<?php
/**
 * Person image defaults and URL resolution used by native Joomla 5/6 site views.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

final class PersonImageHelper
{
    /**
     * Resolve the configured default player picture for missing images.
     *
     * Reads the component's ph_player option using Joomla parameters
     * and returns the trimmed placeholder path for site templates.
     * Malformed registry values are ignored instead of being string-cast.
     * Invalidly encoded or unsafe placeholder URLs are not returned to views.
     *
     * @return string Placeholder picture path, possibly empty.
     */
    public static function placeholder(): string
    {
        $picture = ComponentHelper::getParams('com_sportsmanagement')->get('ph_player', '');

        // Misconfigured Joomla registry data can contain arrays or objects.
        // Never render those values as "Array" or trigger a PHP 8 warning.
        if (!is_string($picture) && !is_int($picture)) {
            return '';
        }

        $picture = (string) $picture;

        return strlen($picture) <= 4096
            && preg_match('//u', $picture) === 1
            && preg_match('/[\\x00-\\x1F\\x7F]/', $picture) !== 1
            && self::safeCandidate($picture)
                ? trim($picture)
                : '';
    }

    /**
     * Choose the first nonempty player picture other than the placeholder.
     *
     * Checks candidate paths in priority order so views can prefer
     * personal pictures and fall back to the configured default.
     * Unsafe higher-priority URLs are skipped rather than hiding a valid
     * later candidate or poisoning the placeholder fallback.
     *
     * @param string ...$candidates Possible picture paths in priority order.
     * @return string Selected path or the configured placeholder.
     */
    public static function resolve(string ...$candidates): string
    {
        $placeholder = self::placeholder();

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);

            if ($candidate !== '' && $candidate !== $placeholder
                && self::safeCandidate($candidate)) {
                return $candidate;
            }
        }

        return $placeholder;
    }

    /**
     * Validate a candidate image path without resolving its site URL.
     *
     * Rejects dangerous URL schemes, malformed encoding and control bytes
     * before selecting a legacy image or its configured placeholder. Uses
     * url() only for validation while leaving original relative paths intact.
     *
     * @param string $candidate Picture path or remote URL to inspect.
     * @return bool Whether the candidate can be resolved safely.
     */
    private static function safeCandidate(string $candidate): bool
    {
        return $candidate !== '' && strlen($candidate) <= 4096
            && preg_match('//u', $candidate) === 1
            && self::url($candidate) !== '';
    }

    /**
     * Convert a configured player picture into a browser-accessible image URL.
     *
     * Rejects control characters, unsupported URL schemes, credential-bearing
     * remote URLs and path traversal (including encoded traversal segments).
     * Prefixes accepted relative paths with a validated picture server URL,
     * falling back to Joomla's site root when the configured host is invalid.
     * Ignores non-string picture-server constants without triggering a PHP 8
     * array-to-string conversion warning. Validates raw picture text before
     * trim() can hide control characters.
     *
     * @param string $picture Candidate URL or relative picture path.
     * @return string Validated image URL or an empty string if unsafe.
     */
    public static function url(string $picture): string
    {
        // Reject raw control bytes before trimming can erase trailing NUL,
        // newline or tab characters from imported image paths.
        if (preg_match('/[\\x00-\\x1F\\x7F]/', $picture) === 1) {
            return '';
        }

        $picture = trim($picture);

        if ($picture === ''
            || preg_match('/[\x00-\x1F\x7F]/', $picture)
            || str_contains($picture, chr(92))
            || str_starts_with($picture, '//')) {
            return '';
        }

        if (preg_match('~^https?://~i', $picture)) {
            $parts = parse_url($picture);

            return filter_var($picture, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                    ? $picture
                    : '';
        }

        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $picture)) {
            return '';
        }

        $path = ltrim($picture, '/');
        $decoded = $path;

        // A proxy or web server may decode more than one layer of escaping.
        // Examine every decoded layer, including the final one. A nested
        // %25252e%25252e path must not become ../ only after the last pass.
        // Reject still-encoded input after the bounded loop to avoid relying
        // on the web server's number of URL-decoding passes.
        for ($depth = 0; $depth < 8; $depth++) {
            if (preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decoded)
                || preg_match('/[\x00-\x1F\x7F]/', $decoded)
                || str_contains($decoded, chr(92))
                || str_contains($decoded, '?')
                || str_contains($decoded, '#')) {
                return '';
            }

            $next = rawurldecode($decoded);

            if ($next === $decoded) {
                break;
            }

            $decoded = $next;
        }

        // Nested percent escapes remaining after eight rounds could be
        // interpreted by a proxy or server in a subsequent decoding pass.
        if (preg_match('/%[0-9a-f]{2}/i', $decoded) === 1
            || preg_match('~(?:^|/)\\.{1,2}(?:/|$)~', $decoded)
            || preg_match('/[\\x00-\\x1F\\x7F]/', $decoded)
            || str_contains($decoded, chr(92))
            || str_contains($decoded, '?') || str_contains($decoded, '#')) {
            return '';
        }

        $configuredBase = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
            ? COM_SPORTSMANAGEMENT_PICTURE_SERVER
            : null;
        // A malformed constant may contain nested data on old installations.
        // Never cast arrays or objects into an image host in PHP 8.
        $base = is_string($configuredBase) ? trim($configuredBase) : Uri::root();

        // An invalid configured picture host must not become an image link.
        $parts = parse_url($base);

        if (!is_array($parts)
            || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment'])) {
            $base = Uri::root();
        }

        return rtrim($base, '/') . '/' . $path;
    }
}
