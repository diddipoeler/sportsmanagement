<?php
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
        $picture = trim($picture);
        $pictureUrl = $picture === ''
            ? ''
            : (preg_match('#^https?://#i', $picture)
                ? $picture
                : rtrim(Uri::root(), '/') . '/' . ltrim($picture, '/'));
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
}
