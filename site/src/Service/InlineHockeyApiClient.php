<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Http\HttpFactory;

/** Joomla HTTP bridge for the Inline Hockey JSON APIs. */
final class InlineHockeyApiClient
{
    public function fetchJson(string $url, string $username = '', string $password = ''): object
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (!in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
            throw new \RuntimeException('Invalid Inline-Hockey API URL.');
        }

        $headers = ['Accept' => 'application/json'];

        if ($username !== '' || $password !== '') {
            $headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
        }

        $response = HttpFactory::getHttp()->get($url, $headers, 30);
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

    public function pageUrl(string $url, int $page): string
    {
        $page = max(1, $page);

        if (preg_match('/([?&])page=\d+/i', $url)) {
            return (string) preg_replace('/([?&])page=\d+/i', '$1page=' . $page, $url, 1);
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . 'page=' . $page;
    }
}
