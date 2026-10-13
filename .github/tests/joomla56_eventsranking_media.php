<?php
/**
 * Standalone Joomla 5/6 event-ranking media and icon regression checks.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static function getParams(string $component): \Joomla\Registry\Registry
        {
            return new \Joomla\Registry\Registry(['ph_player' => 'images/fallback.png']);
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    $root = sys_get_temp_dir() . '/jsm-event-ranking-' . bin2hex(random_bytes(8));

    if (!mkdir($root . '/images', 0700, true)) {
        throw new \RuntimeException('Cannot create image test fixtures');
    }

    define('JPATH_ROOT', $root);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_eventsranking/src/Helper/EventsRankingHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper\EventsRankingHelper();
    $media = new \ReflectionMethod($helper, 'mediaUrl');
    $picture = new \ReflectionMethod($helper, 'picture');

    try {
        file_put_contents($root . '/images/player.png', 'player');
        file_put_contents($root . '/images/fallback.png', 'fallback');

        foreach ([
            ['images/player.png', 'https://example.test/images/player.png'],
            ['/images/player.png', 'https://example.test/images/player.png'],
            ['https://cdn.example.org/logo.png', 'https://cdn.example.org/logo.png'],
            ['http://cdn.example.org/logo.png', 'http://cdn.example.org/logo.png'],
            ['javascript:alert(1)', ''],
            ['data:image/svg+xml,<svg>', ''],
            ['file:///etc/passwd', ''],
            ['//evil.example.org/logo.png', ''],
            ['../private.png', ''],
            ['images/../private.png', ''],
            ['https://user:password@cdn.example.org/logo.png', ''],
            ['images\wrong.png', ''],
            ["https://cdn.example.org/\ninvalid.png", ''],
        ] as [$path, $expected]) {
            $actual = $media->invoke($helper, $path);
            if ($actual !== $expected) {
                throw new \RuntimeException('Invalid media URL for ' . json_encode($path));
            }
        }

        foreach ([
            ['images/player.png', 'https://example.test/images/player.png'],
            ['missing.png', 'https://example.test/images/fallback.png'],
            ['../private.png', 'https://example.test/images/fallback.png'],
            ['https://cdn.example.org/player.png', 'https://cdn.example.org/player.png'],
            ['javascript:alert(1)', 'https://example.test/images/fallback.png'],
        ] as [$path, $expected]) {
            if ($picture->invoke($helper, $path) !== $expected) {
                throw new \RuntimeException('Unexpected player image for ' . $path);
            }
        }

        $outside = tempnam(sys_get_temp_dir(), 'jsm-outside-image-');
        if ($outside === false) {
            throw new \RuntimeException('Cannot create external image fixture');
        }

        try {
            if (function_exists('symlink') && @symlink($outside, $root . '/images/outside.png')) {
                if ($picture->invoke($helper, 'images/outside.png')
                    !== 'https://example.test/images/fallback.png'
                ) {
                    throw new \RuntimeException('External symbolic link was accepted');
                }
            }
        } finally {
            @unlink($root . '/images/outside.png');
            @unlink($outside);
        }

        $params = new \Joomla\Registry\Registry([
            'show_picture' => 0,
            'show_team' => 0,
            'show_player_link' => 0,
        ]);

        $render = static function (string $icon) use ($helper, $media, $params): string {
            $rankingData = [
                'project' => null,
                'eventtypes' => [(object) [
                    'id' => 1,
                    'name' => 'Goals',
                    'icon' => $icon,
                    'icon_url' => $media->invoke($helper, $icon),
                ]],
                'rankings' => [1 => [(object) [
                    'rank' => 1,
                    'p' => 5,
                    'display_name' => 'Player',
                ]]],
            ];

            ob_start();
            try {
                require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_eventsranking/tmpl/default.php';
                return (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }
        };

        $unsafeHtml = $render('javascript:alert(1)');
        if (str_contains($unsafeHtml, '<img') || !str_contains($unsafeHtml, 'Goals')) {
            throw new \RuntimeException('Unsafe event icon rendered into HTML');
        }

        $safeHtml = $render('images/player.png');
        if (!str_contains($safeHtml, 'src="https://example.test/images/player.png"')) {
            throw new \RuntimeException('Safe event icon missing from template');
        }

        echo "Events Ranking media URL and icon regressions OK\n";
    } finally {
        @unlink($root . '/images/outside.png');
        @unlink($root . '/images/player.png');
        @unlink($root . '/images/fallback.png');
        @rmdir($root . '/images');
        @rmdir($root);
    }
}
