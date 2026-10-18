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
    public static function placeholder(): string
    {
        return trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_player', ''));
    }

    public static function resolve(string ...$candidates): string
    {
        $placeholder = self::placeholder();

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);

            if ($candidate !== '' && $candidate !== $placeholder) {
                return $candidate;
            }
        }

        return $placeholder;
    }

    /**
     * Return a safe browser image URL without allowing malformed legacy paths
     * to escape the site's public image directory.
     */
    public static function url(string $picture): string
    {
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
        for ($depth = 0; $depth < 3; $depth++) {
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

        $base = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
            ? trim((string) COM_SPORTSMANAGEMENT_PICTURE_SERVER)
            : Uri::root();

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
