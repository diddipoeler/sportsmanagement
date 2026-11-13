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
    /**
     * Generate a KML placemark describing a club and its location.
     *
     * Validates the club identifier and geographic coordinate range,
     * safely escapes XML content and creates the KML file within Joomla's
     * temporary folder, optionally including a validated picture URL.
     * Rejects invalid XML controls and refuses pre-existing symlink output
     * files to avoid overwriting an unrelated target through the tmp folder.
     *
     * @param int $clubId Club identifier used in the output filename.
     * @param string $address Club location and description.
     * @param string $name Club display name.
     * @param string $picture Optional club image URL or site path.
     * @param float|null $latitude Latitude in degrees.
     * @param float|null $longitude Longitude in degrees.
     * @return bool True when a KML file was written successfully.
     */
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
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $name . $address) === 1
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

        $filename = $directory . '/' . $clubId . '-club.kml';

        // Reject symlinks even if their target does not currently exist.
        if (is_link($filename)) {
            return false;
        }

        try {
            return File::write($filename, $kml);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Resolve an optional picture to a safe KML icon URL.
     *
     * Rejects executable URI schemes, control characters, encoded path
     * traversal and credential-bearing remote URLs, preserving valid
     * HTTP(S) and site-relative images. Repeatedly decodes local paths
     * to reject multi-encoded traversal and URL scheme bypass attempts.
     *
     * @param string $picture Candidate image URL or site path.
     * @return string Valid KML image URL or an empty string.
     */
    private static function pictureUrl(string $picture): string
    {
        $picture = trim($picture);

        if ($picture === ''
            || preg_match('/[\\x00-\\x1F\\x7F]/', $picture)
            || str_contains($picture, chr(92))) {
            return '';
        }

        if (preg_match('~^https?://~i', $picture)) {
            $parts = parse_url($picture);

            return filter_var($picture, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
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
        $decodedPath = $path;

        for ($depth = 0; $depth < 8; $depth++) {
            if (preg_match('/[\x00-\x1F\x7F]/', $decodedPath)
                || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decodedPath)
                || preg_match('~^[a-z][a-z0-9+.-]*:~i', $decodedPath)
                || str_starts_with($decodedPath, '//')
                || str_contains($decodedPath, chr(92))
                || str_contains($decodedPath, '?')
                || str_contains($decodedPath, '#')) {
                return '';
            }

            $next = rawurldecode($decodedPath);

            if ($next === $decodedPath) {
                break;
            }

            $decodedPath = $next;
        }

        // Remaining percent-escapes may hide another decoding layer.
        if (preg_match('/%[0-9a-f]{2}/i', $decodedPath)
            || preg_match('/[\x00-\x1F\x7F]/', $decodedPath)
            || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decodedPath)
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $decodedPath)
            || str_starts_with($decodedPath, '//')
            || str_contains($decodedPath, chr(92))
            || str_contains($decodedPath, '?')
            || str_contains($decodedPath, '#')) {
            return '';
        }

        return rtrim(Uri::root(), '/') . '/' . $path;
    }
}
