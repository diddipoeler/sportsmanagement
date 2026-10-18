<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/** Write the optional club KML file without loading the legacy map/geocoder helper. */
final class ClubKmlHelper
{
    public static function write(
        int $clubId,
        string $address,
        string $name,
        string $picture,
        ?float $latitude,
        ?float $longitude
    ): bool {
        if (
            $clubId <= 0
            || $latitude === null
            || $longitude === null
            || !is_finite($latitude)
            || !is_finite($longitude)
            || $latitude < -90.0
            || $latitude > 90.0
            || $longitude < -180.0
            || $longitude > 180.0
        ) {
            return false;
        }

        $escape = static fn (string $value): string => htmlspecialchars(
            $value,
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );
        $pictureUrl = self::pictureUrl($picture);
        $id = (string) $clubId;
        $style = '';

        if ($pictureUrl !== '') {
            $style = '  <Style id="' . $id . 'Style"><IconStyle><Icon><href>'
                . $escape($pictureUrl)
                . '</href></Icon></IconStyle></Style>' . "\n";
        }

        $kml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n"
            . ' <Document>' . "\n"
            . $style
            . '  <Placemark id="placemark' . $id . '">' . "\n"
            . '   <name>' . $escape($name) . '</name>' . "\n"
            . '   <description>' . $escape($address) . '</description>' . "\n"
            . '   <address>' . $escape($address) . '</address>' . "\n"
            . ($pictureUrl !== '' ? '   <styleUrl>#' . $id . 'Style</styleUrl>' . "\n" : '')
            . '   <Point><coordinates>' . $longitude . ',' . $latitude . '</coordinates></Point>' . "\n"
            . '  </Placemark>' . "\n"
            . ' </Document>' . "\n"
            . '</kml>';

        $directory = JPATH_SITE . '/tmp';

        if (!is_dir($directory) && !Folder::create($directory)) {
            return false;
        }

        try {
            return File::write($directory . '/' . $clubId . '-club.kml', $kml);
        } catch (\Throwable) {
            return false;
        }
    }
    /**
     * Use only safe HTTP(S) URLs or paths beneath the site's public root.
     * Malformed optional image paths must not invalidate the club KML itself.
     */
    private static function pictureUrl(string $picture): string
    {
        $picture = trim($picture);

        if ($picture === ''
            || preg_match('/[\\x00-\\x1F\\x7F]/', $picture)
            || str_contains($picture, '\\\\')) {
            return '';
        }

        if (preg_match('~^https?://~i', $picture)) {
            $parts = parse_url($picture);

            return filter_var($picture, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !isset($parts['user'], $parts['pass'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                    ? $picture
                    : '';
        }

        if (str_starts_with($picture, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $picture)) {
            return '';
        }

        $path = ltrim($picture, '/');
        $decodedPath = rawurldecode($path);

        if (preg_match('~(?:^|/)\\.{1,2}(?:/|$)~', $decodedPath)
            || str_contains($decodedPath, '\\\\')
            || str_contains($decodedPath, '?')
            || str_contains($decodedPath, '#')) {
            return '';
        }

        return rtrim(Uri::root(), '/') . '/' . $path;
    }

}
