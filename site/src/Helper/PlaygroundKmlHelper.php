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

use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/** Write the optional playground KML file without loading the legacy map helper. */
final class PlaygroundKmlHelper
{
    /**
     * Write a KML placemark file for a playground with valid coordinates.
     *
     * Rejects missing or nonfinite latitude/longitude values and IDs
     * outside the expected range, escapes the playground name and address
     * for XML output and writes the KML file in Joomla's site tmp folder.
     * Returns false when directory creation or file writing fails.
     *
     * @param int $playgroundId Positive playground record ID.
     * @param string $address Location address for KML metadata.
     * @param string $name Playground display name.
     * @param float|null $latitude Latitude in the range -90 to 90.
     * @param float|null $longitude Longitude in the range -180 to 180.
     * @return bool True when the KML file was written successfully.
     */
    public static function write(
        int $playgroundId,
        string $address,
        string $name,
        ?float $latitude,
        ?float $longitude
    ): bool {
        if (
            $playgroundId <= 0
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
        $id = (string) $playgroundId;
        $kml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n"
            . ' <Document>' . "\n"
            . '  <Style id="' . $id . 'Style">' . "\n"
            . '   <IconStyle><Icon><href>https://maps.google.com/mapfiles/kml/pal2/icon49.png</href></Icon></IconStyle>' . "\n"
            . '  </Style>' . "\n"
            . '  <Placemark id="placemark' . $id . '">' . "\n"
            . '   <name>' . $escape($name) . '</name>' . "\n"
            . '   <description>' . $escape($address) . '</description>' . "\n"
            . '   <address>' . $escape($address) . '</address>' . "\n"
            . '   <styleUrl>#' . $id . 'Style</styleUrl>' . "\n"
            . '   <Point><coordinates>' . $longitude . ',' . $latitude . '</coordinates></Point>' . "\n"
            . '  </Placemark>' . "\n"
            . ' </Document>' . "\n"
            . '</kml>';

        $directory = JPATH_SITE . '/tmp';

        if (!is_dir($directory) && !Folder::create($directory)) {
            return false;
        }

        try {
            return File::write($directory . '/' . $playgroundId . '-playground.kml', $kml);
        } catch (\Throwable) {
            return false;
        }
    }
}
