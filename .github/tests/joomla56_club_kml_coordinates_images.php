<?php
/**
 * Joomla 5/6 club KML coordinate and image URI regression coverage.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://sports.example/joomla/';
        }
    }
}

namespace Joomla\Filesystem {
    final class File
    {
        public static string $written = '';

        public static function write(string $path, string $data): bool
        {
            self::$written = $data;

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

    require_once dirname(__DIR__, 2) . '/site/src/Helper/ClubKmlHelper.php';

    $class = \Diddipoeler\Component\SportsManagement\Site\Helper\ClubKmlHelper::class;
    $written = \Joomla\Filesystem\File::class;

    foreach ([[NAN, 12.0], [10.0, NAN], [INF, 12.0], [10.0, -INF], [91.0, 0.0], [0.0, 181.0]] as $coordinates) {
        $written::$written = '';
        if ($class::write(5, 'Street', 'Club', '', $coordinates[0], $coordinates[1])
            || $written::$written !== '') {
            throw new \RuntimeException('Invalid club KML coordinates reached the writer.');
        }
    }

    foreach ([
        'images/clubs/team.png' => 'https://sports.example/joomla/images/clubs/team.png',
        '/images/clubs/team.png' => 'https://sports.example/joomla/images/clubs/team.png',
        'https://cdn.example/team.png' => 'https://cdn.example/team.png',
        'javascript:alert(1)' => '',
        '//evil.example/icon.png' => '',
        '../private/logo.png' => '',
        'images/%2e%2e/private/logo.png' => '',
        'images/logo%0A.png' => '',
        'images\\private\\logo.png' => '',
        'https://user:secret@cdn.example/image.png' => '',
    ] as $picture => $expected) {
        $written::$written = '';
        if (!$class::write(5, 'Club & street', 'Club <A>', $picture, 51.3, 7.2)) {
            throw new \RuntimeException('Valid coordinates should allow the KML document.');
        }

        $xml = $written::$written;
        $hasHref = preg_match('~<href>(.*?)</href>~', $xml, $matches) === 1;
        $url = $hasHref ? html_entity_decode($matches[1], ENT_QUOTES | ENT_XML1, 'UTF-8') : '';

        if ($url !== $expected || !str_contains($xml, '7.2,51.3')
            || !str_contains($xml, 'Club &lt;A&gt;')) {
            throw new \RuntimeException('Unexpected club KML image URL or escaped content for ' . $picture);
        }
    }

    echo "Joomla 5/6 club KML coordinate and image checks passed.\n";
}
