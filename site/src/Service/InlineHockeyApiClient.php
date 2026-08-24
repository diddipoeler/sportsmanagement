<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Http\HttpFactory;

/** Joomla HTTP bridge for the Inline Hockey APIs and trusted club media. */
final class InlineHockeyApiClient
{
    private const MAX_IMAGE_BYTES = 5242880;
    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    public function fetchJson(string $url, string $username = '', string $password = ''): object
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (!in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
            throw new \RuntimeException('Invalid Inline-Hockey API URL.');
        }

        $response = HttpFactory::getHttp()->get(
            $url,
            $this->headers('application/json', $username, $password),
            30
        );
        $status = $response->getStatusCode();
        $body = trim((string) $response->getBody());

        if ($status < 200 || $status >= 300 || $body === '') {
            throw new \RuntimeException('Inline-Hockey API request failed with HTTP status ' . $status . '.');
        }

        try {
            $payload = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Inline-Hockey API response is invalid JSON.', 0, $exception);
        }

        if (!is_object($payload)) {
            throw new \RuntimeException('Inline-Hockey API response has an invalid structure.');
        }

        return $payload;
    }

    public function fetchIshdImage(string $urlOrPath, string $username = '', string $password = ''): string
    {
        $url = $this->normaliseIshdImageUrl($urlOrPath);
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if (!in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            throw new \RuntimeException('Inline-Hockey logo has an unsupported image extension.');
        }

        $response = HttpFactory::getHttp()->get(
            $url,
            $this->headers('image/png, image/jpeg, image/gif, image/webp', $username, $password),
            30
        );
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300 || $body === '') {
            throw new \RuntimeException('Inline-Hockey logo request failed with HTTP status ' . $status . '.');
        }

        if (strlen($body) > self::MAX_IMAGE_BYTES) {
            throw new \RuntimeException('Inline-Hockey logo exceeds the 5 MiB size limit.');
        }

        return $body;
    }

    public function pageUrl(string $url, int $page): string
    {
        $page = max(1, $page);

        if (preg_match('/([?&])page=\d+/i', $url)) {
            return (string) preg_replace('/([?&])page=\d+/i', '$1page=' . $page, $url, 1);
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . 'page=' . $page;
    }

    /** @return array<string,string> */
    private function headers(string $accept, string $username, string $password): array
    {
        $headers = ['Accept' => $accept];

        if ($username !== '' || $password !== '') {
            $headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
        }

        return $headers;
    }

    private function normaliseIshdImageUrl(string $urlOrPath): string
    {
        $url = trim($urlOrPath);

        if ($url === '') {
            throw new \RuntimeException('Inline-Hockey logo URL is empty.');
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        } elseif (!preg_match('#^https?://#i', $url)) {
            $url = 'https://www.ishd.de/' . ltrim($url, '/');
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $port = isset($parts['port']) ? (int) $parts['port'] : 443;

        if (
            $scheme !== 'https'
            || !in_array($host, ['ishd.de', 'www.ishd.de'], true)
            || $port !== 443
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            throw new \RuntimeException('Inline-Hockey logo URL must use trusted ISHD HTTPS.');
        }

        return $url;
    }
}
