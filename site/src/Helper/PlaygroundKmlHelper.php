<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/** Write the optional playground KML file without loading the legacy map helper. */
final class PlaygroundKmlHelper
{
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
