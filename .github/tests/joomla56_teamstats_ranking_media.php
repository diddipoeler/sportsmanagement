<?php
/**
 * Joomla 5/6 Team Stats Ranking safe icon and club logo regression tests.
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
            return 'https://site.example.test/subdir/';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $value): string
        {
            return $value;
        }
    }
}

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

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_teamstats_ranking/src/Helper/TeamStatsRankingHelper.php';

    $helper = \Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Helper\TeamStatsRankingHelper::class;

    foreach ([
        ['media/club logo.png', 'https://site.example.test/subdir/media/club%20logo.png'],
        ['/images/club.png', 'https://site.example.test/subdir/images/club.png'],
        ['https://cdn.example.test/icon.png', 'https://cdn.example.test/icon.png'],
        ['http://cdn.example.test/icon.png', 'http://cdn.example.test/icon.png'],
        ['', ''],
        ['javascript:alert(1)', ''],
        ['data:image/svg+xml,<svg>', ''],
        ['//cdn.example.test/club.png', ''],
        ['file:///etc/passwd', ''],
        ['images/../../secret.png', ''],
        ['images/./icon.png', ''],
        ['images/%2e%2e/private.png', ''],
        ['images\outside.png', ''],
        ['https://user:secret@cdn.example.test/icon.png', ''],
        ["https://cdn.example.test/\nicon.png", ''],
        ['images/icon.png?redirect=evil', ''],
    ] as [$input, $expected]) {
        $result = $helper::mediaUrl($input);

        if ($result !== $expected) {
            throw new \RuntimeException('Incorrect ranking image URL for ' . json_encode($input));
        }
    }

    $render = static function (string $statIcon, string $clubLogo) use ($helper): string {
        $project = (object) ['name' => 'Autumn League', 'id' => 1, 'slug' => '1:autumn', 'season_slug' => '2:autumn'];
        $stat = (object) [
            'id' => 7,
            'name' => 'Goals <All>',
            'icon' => $statIcon,
            'icon_url' => $helper::mediaUrl($statIcon),
        ];
        $ranking = [['team_id' => 17, 'rank' => 1, 'total' => '3.0']];
        $teams = [17 => (object) [
            'id' => 17,
            'name' => 'United & City',
            'short_name' => 'United & City',
            'alias' => 'united',
            'club_id' => 1,
            'logo_big' => $clubLogo,
            'logo_url' => $helper::mediaUrl($clubLogo),
        ]];
        $databaseSelector = 0;
        $params = new \Joomla\Registry\Registry([
            'show_logo' => 1,
            'show_event_icon' => 1,
            'teamlink' => '',
        ]);

        ob_start();

        try {
            require dirname(__DIR__, 2)
                . '/modules/mod_sportsmanagement_teamstats_ranking/tmpl/default.php';

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    };

    $invalid = $render('javascript:alert(1)', '../secret.png');
    if (str_contains($invalid, '<img')
        || str_contains($invalid, 'javascript:')
        || !str_contains($invalid, 'Goals &lt;All&gt;')
        || !str_contains($invalid, 'United &amp; City')
    ) {
        throw new \RuntimeException('Unsafe ranking image reached rendered markup');
    }

    $valid = $render('https://cdn.example.test/icon.png', 'images/club logo.png');
    if (substr_count($valid, '<img ') !== 2
        || !str_contains($valid, 'src="https://cdn.example.test/icon.png"')
        || !str_contains($valid, 'src="https://site.example.test/subdir/images/club%20logo.png"')
    ) {
        throw new \RuntimeException('Valid ranking images disappeared or were incorrectly prefixed');
    }

    echo "Team Stats Ranking icon and logo URL regressions OK\n";
}
