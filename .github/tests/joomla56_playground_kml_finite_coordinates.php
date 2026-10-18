<?php
/**
 * Joomla 5/6 playground KML coordinate regression tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Filesystem {
    final class File
    {
        public static ?string $written = null;

        public static function write(string $path, string $contents): bool
        {
            self::$written = $contents;

            return true;
        }
    }

    final class Folder
    {
        public static function create(string $path): bool
        {
            return true;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_SITE', sys_get_temp_dir());
    require_once dirname(__DIR__, 2) . '/site/src/Helper/PlaygroundKmlHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\PlaygroundKmlHelper::class;
    $writer = \Joomla\Filesystem\File::class;

    foreach ([
        [NAN, 12.0], [10.0, NAN],
        [INF, 12.0], [10.0, -INF],
        [-90.001, 0.0], [90.001, 0.0],
        [0.0, -180.001], [0.0, 180.001],
    ] as [$latitude, $longitude]) {
        $writer::$written = null;

        if ($helper::write(5, 'address', 'name', $latitude, $longitude)
            || $writer::$written !== null) {
            throw new \RuntimeException('Invalid playground coordinates reached the KML writer.');
        }
    }

    $writer::$written = null;

    if (!$helper::write(7, 'A & B', 'Club <7>', 90.0, -180.0)
        || !str_contains((string) $writer::$written, '<coordinates>-180,90</coordinates>')
        || !str_contains((string) $writer::$written, 'A &amp; B')
        || !str_contains((string) $writer::$written, 'Club &lt;7&gt;')) {
        throw new \RuntimeException('Valid playground coordinate KML output regressed.');
    }

    echo "Playground KML coordinate regression passed.\n";
}
