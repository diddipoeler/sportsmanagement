<?php
/**
 * Native Joomla 5/6 HTTP client for Inline Hockey API and trusted ISHD media access.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Http\HttpFactory;

/** Joomla HTTP bridge for the Inline Hockey APIs and trusted club media. */
final class InlineHockeyApiClient
{
    private const MAX_IMAGE_BYTES = 5242880;
    private const MAX_API_PAGES = 100;
    private const API_HOSTS = ['ishd.de', 'www.ishd.de', 'api.ishd.de'];
    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    public function fetchJson(string $url, string $username = '', string $password = ''): object
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (!in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
            throw new \RuntimeException('Invalid Inline-Hockey API URL.');
        }

        // Do not send Basic authentication over HTTP or accept credentials in URLs.
        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new \RuntimeException('Inline-Hockey API URLs must not contain credentials.');
        }

        if ($scheme !== 'https' && ($username !== '' || $password !== '')) {
            throw new \RuntimeException('Inline-Hockey API authentication requires HTTPS.');
        }

        // The project match URL and API-provided club links are not trusted.
        // Only the official ISHD API hosts may receive an HTTP request,
        // especially when the administrator configured Basic credentials.
        if (strlen($url) > 2048
            || preg_match('/[\x00-\x20\x7F\\\\]/', $url)
            || $scheme !== 'https'
            || !in_array(strtolower((string) $parts['host']), self::API_HOSTS, true)
            || (isset($parts['port']) && (int) $parts['port'] !== 443)) {
            throw new \RuntimeException('Inline-Hockey API URL must use trusted ISHD HTTPS.');
        }

        // Do not forward Basic credentials or an image download to redirect
        // targets that have not passed this client's URL validation.
        $http = (new HttpFactory())->getHttp(['follow_location' => false], ['Curl', 'Stream']);
        $response = $http->get(
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

        // Do not forward Basic credentials or an image download to redirect
        // targets that have not passed this client's URL validation.
        $http = (new HttpFactory())->getHttp(['follow_location' => false], ['Curl', 'Stream']);
        $response = $http->get(
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

        // The external endpoint controls the bytes, not just the filename. Do
        // not save HTML, SVG or a differently encoded image under a web-accessible
        // raster-image extension.
        $imageTypes = [
            'png' => IMAGETYPE_PNG,
            'jpg' => IMAGETYPE_JPEG,
            'jpeg' => IMAGETYPE_JPEG,
            'gif' => IMAGETYPE_GIF,
            'webp' => IMAGETYPE_WEBP,
        ];
        $image = @getimagesizefromstring($body);

        if ($image === false || (int) ($image[2] ?? 0) !== $imageTypes[$extension]) {
            throw new \RuntimeException('Inline-Hockey logo content does not match its image extension.');
        }

        return $body;
    }

    /** Keep remote pagination finite, even for corrupt or enormous API counts. */
    /**
     * Accept only complete, positive external decimal identifiers. PHP casts
     * arrays, booleans and strings such as "12junk" into unrelated IDs.
     */
    public function positiveId(mixed $value): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : 0;
        }

        if (!is_string($value) || !preg_match('/^[1-9][0-9]*$/D', $value)) {
            return 0;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    public function pageCount(mixed $rawPages): int
    {
        if (!is_int($rawPages)
            && (!is_string($rawPages) || !preg_match('/^[0-9]{1,5}$/D', $rawPages))) {
            return 1;
        }

        return min(self::MAX_API_PAGES, max(1, (int) $rawPages));
    }

    public function pageUrl(string $url, int $page): string
    {
        $page = max(1, $page);

        // Page parameters belong in the query string, not after a fragment.
        $fragmentPosition = strpos($url, '#');
        $fragment = $fragmentPosition === false ? '' : substr($url, $fragmentPosition);
        $baseUrl = $fragmentPosition === false ? $url : substr($url, 0, $fragmentPosition);

        // Duplicate "page" parameters are interpreted inconsistently by API
        // servers. Keep the first parameter in place and remove later copies.
        $seenPage = false;
        $baseUrl = (string) preg_replace_callback(
            '/([?&])page=[^&#]*/i',
            static function (array $matches) use (&$seenPage, $page): string {
                if ($seenPage) {
                    return '';
                }

                $seenPage = true;

                return $matches[1] . 'page=' . $page;
            },
            $baseUrl
        );

        if (!$seenPage) {
            $baseUrl .= (str_contains($baseUrl, '?') ? '&' : '?') . 'page=' . $page;
        }

        return $baseUrl . $fragment;
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

        if ($url === '' || strlen($url) > 2048
            || preg_match('/[\\x00-\\x20\\x7F\\\\]/', $url)) {
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
