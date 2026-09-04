<?php
/**
 * Joomla 5/6 remote image download helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Http\HttpFactory;

/**
 * Fetch remote images while rejecting local/private targets and unsafe redirects.
 */
final class RemoteImageDownloadHelper
{
    private const MAX_REDIRECTS = 3;
    private const REQUEST_TIMEOUT = 15;

    public function download(string $url, string $absolutePath, int $maxBytes): bool
    {
        $maxBytes = max(1, $maxBytes);

        if (!$this->isAllowedTargetPath($absolutePath) || $this->validateRemoteUrl($url) === null) {
            return false;
        }

        $body = $this->fetch($url, $maxBytes);

        if ($body === null || !$this->isAllowedImageData($body)) {
            return false;
        }

        $directory = dirname($absolutePath);

        try {
            if (!is_dir($directory) && !Folder::create($directory)) {
                return false;
            }

            return (bool) File::write($absolutePath, $body);
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');

            return false;
        }
    }

    private function fetch(string $url, int $maxBytes): ?string
    {
        $currentUrl = $url;

        try {
            // Only use transports where follow_location=false is honoured so every redirect can be validated.
            $http = (new HttpFactory())->getHttp(['follow_location' => false], ['Curl', 'Stream']);

            for ($redirects = 0; $redirects <= self::MAX_REDIRECTS; $redirects++) {
                if ($this->validateRemoteUrl($currentUrl) === null) {
                    return null;
                }

                $response = $http->get(
                    $currentUrl,
                    ['Accept' => 'image/*,*/*;q=0.1'],
                    self::REQUEST_TIMEOUT
                );
                $status = $response->getStatusCode();

                if ($status >= 300 && $status < 400) {
                    if ($redirects === self::MAX_REDIRECTS) {
                        return null;
                    }

                    $currentUrl = $this->resolveRedirect(
                        $currentUrl,
                        trim($response->getHeaderLine('Location'))
                    );

                    if ($currentUrl === null) {
                        return null;
                    }

                    continue;
                }

                if ($status < 200 || $status >= 300) {
                    return null;
                }

                $contentLength = trim($response->getHeaderLine('Content-Length'));

                if ($contentLength !== ''
                    && ctype_digit($contentLength)
                    && (int) $contentLength > $maxBytes) {
                    return null;
                }

                $body = (string) $response->getBody();

                if ($body === '' || strlen($body) > $maxBytes) {
                    return null;
                }

                return $body;
            }
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
        }

        return null;
    }

    private function validateRemoteUrl(string $url): ?array
    {
        if ($url === ''
            || strlen($url) > 2048
            || str_contains($url, '\\')
            || preg_match('/[\x00-\x20\x7F]/', $url)) {
            return null;
        }

        $parts = parse_url($url);

        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = trim((string) ($parts['host'] ?? ''), '[]');

        if (!in_array($scheme, ['http', 'https'], true)
            || $host === ''
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return null;
        }

        $expectedPort = $scheme === 'https' ? 443 : 80;

        if (isset($parts['port']) && (int) $parts['port'] !== $expectedPort) {
            return null;
        }

        if (!$this->isPublicHost($host)) {
            return null;
        }

        $parts['scheme'] = $scheme;
        $parts['host'] = $host;

        return $parts;
    }

    private function isPublicHost(string $host): bool
    {
        $host = strtolower(rtrim(trim($host), '.'));

        if ($host === '' || strlen($host) > 253 || str_contains($host, "\0")) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $this->isPublicIp($host);
        }

        foreach (['localhost', 'localdomain', 'local', 'internal', 'lan', 'home', 'home.arpa'] as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.' . $suffix)) {
                return false;
            }
        }

        if (!str_contains($host, '.') || !preg_match('/^[a-z0-9.-]+$/i', $host)) {
            return false;
        }

        foreach (explode('.', $host) as $label) {
            if ($label === ''
                || strlen($label) > 63
                || str_starts_with($label, '-')
                || str_ends_with($label, '-')) {
                return false;
            }
        }

        $addresses = [];
        $ipv4 = @gethostbynamel($host);

        if (is_array($ipv4)) {
            $addresses = array_merge($addresses, $ipv4);
        }

        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);

            if (is_array($records)) {
                foreach ($records as $record) {
                    if (!empty($record['ip'])) {
                        $addresses[] = (string) $record['ip'];
                    }

                    if (!empty($record['ipv6'])) {
                        $addresses[] = (string) $record['ipv6'];
                    }
                }
            }
        }

        $addresses = array_values(array_unique($addresses));

        if ($addresses === []) {
            return false;
        }

        foreach ($addresses as $address) {
            if (!$this->isPublicIp($address)) {
                return false;
            }
        }

        return true;
    }

    private function isPublicIp(string $address): bool
    {
        if (filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false) {
            return false;
        }

        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $octets = array_map('intval', explode('.', $address));

            if (($octets[0] === 100 && $octets[1] >= 64 && $octets[1] <= 127)
                || ($octets[0] === 198 && in_array($octets[1], [18, 19], true))) {
                return false;
            }
        }

        return true;
    }

    private function resolveRedirect(string $baseUrl, string $location): ?string
    {
        $location = trim($location);

        if ($location === ''
            || str_contains($location, '\\')
            || preg_match('/[\x00-\x20\x7F]/', $location)) {
            return null;
        }

        $fragmentPosition = strpos($location, '#');

        if ($fragmentPosition !== false) {
            $location = substr($location, 0, $fragmentPosition);
        }

        if ($location === '') {
            return null;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $location)) {
            return $this->validateRemoteUrl($location) !== null ? $location : null;
        }

        $base = $this->validateRemoteUrl($baseUrl);

        if ($base === null) {
            return null;
        }

        $scheme = (string) $base['scheme'];
        $host = (string) $base['host'];
        $hostForUrl = str_contains($host, ':') ? '[' . $host . ']' : $host;
        $port = isset($base['port']) ? ':' . (int) $base['port'] : '';
        $origin = $scheme . '://' . $hostForUrl . $port;

        if (str_starts_with($location, '//')) {
            $resolved = $scheme . ':' . $location;
        } else {
            $basePath = (string) ($base['path'] ?? '/');
            $basePath = $basePath !== '' ? $basePath : '/';

            if (str_starts_with($location, '?')) {
                $resolved = $origin . $basePath . $location;
            } elseif (str_starts_with($location, '/')) {
                $resolved = $origin . $location;
            } else {
                $slashPosition = strrpos($basePath, '/');
                $directory = $slashPosition === false ? '/' : substr($basePath, 0, $slashPosition + 1);
                $resolved = $origin . $directory . $location;
            }
        }

        return $this->validateRemoteUrl($resolved) !== null ? $resolved : null;
    }

    private function isAllowedImageData(string $body): bool
    {
        $info = @getimagesizefromstring($body);

        return $info !== false
            && in_array(
                strtolower((string) ($info['mime'] ?? '')),
                ['image/gif', 'image/jpeg', 'image/png', 'image/webp'],
                true
            );
    }

    private function isAllowedTargetPath(string $absolutePath): bool
    {
        $normalised = str_replace('\\', '/', $absolutePath);
        $root = rtrim(str_replace('\\', '/', JPATH_ROOT), '/')
            . '/images/com_sportsmanagement/database/';

        if ($normalised === ''
            || str_contains($normalised, "\0")
            || !str_starts_with($normalised, $root)) {
            return false;
        }

        foreach (explode('/', substr($normalised, strlen($root))) as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                return false;
            }
        }

        $extension = strtolower((string) pathinfo($normalised, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }
}
