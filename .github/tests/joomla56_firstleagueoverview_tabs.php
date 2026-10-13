<?php
/**
 * Standalone regression checks for first league overview federation tabs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string
        {
            return match ($key) {
                'JNONE' => 'Unassigned',
                'JUNKNOWN' => 'Unknown',
                default => $key,
            };
        }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        public static array $tabLabels = [];

        public static function _(string $method, mixed ...$args): string
        {
            if ($method === 'uitab.addTab') {
                self::$tabLabels[] = (string) ($args[2] ?? '');
            }

            return '';
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function get(string $key, mixed $default = null): mixed
        {
            return $default;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_firstleagueoverview/src/Helper/FirstLeagueOverviewHelper.php';

    $helper = \Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper::class;

    $catalogue = [
        1 => (object) ['id' => 1, 'name' => 'Unused'],
        2 => (object) ['id' => 2, 'name' => 'Europe', 'picture_url' => ''],
        3 => (object) ['id' => 3, 'name' => 'Also unused'],
    ];
    $projects = [
        (object) ['federation' => 2],
        (object) ['federation' => null],
        (object) ['federation' => 99],
        (object) ['federation' => 2],
    ];

    $visible = $helper::visibleFederations($projects, $catalogue);

    if (array_keys($visible) !== [2, 0, 99]
        || $visible[2] !== $catalogue[2]
        || $visible[0]->name !== 'Unassigned'
        || $visible[99]->name !== 'Unknown (99)'
    ) {
        throw new \RuntimeException('League projects disappeared or unrelated federations appeared');
    }

    if ($helper::visibleFederations([], $catalogue) !== []) {
        throw new \RuntimeException('Empty league overview should have no federation tabs');
    }

    $render = static function (array $tabs, array $rows): string {
        $federations = $tabs;
        $firstleagueoverview = $rows;
        $module = (object) ['id' => 7, 'module' => 'mod_sportsmanagement_firstleagueoverview'];
        $params = new \Joomla\Registry\Registry();

        ob_start();

        try {
            require dirname(__DIR__, 2)
                . '/modules/mod_sportsmanagement_firstleagueoverview/tmpl/default.php';

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    };

    $html = $render([], []);

    if (!str_contains($html, 'JGLOBAL_NO_MATCHING_RESULTS')) {
        throw new \RuntimeException('Empty league overview has no visible empty-state message');
    }

    \Joomla\CMS\HTML\HTMLHelper::$tabLabels = [];
    $malicious = '<img src=x onerror=alert(1)>';
    $html = $render(
        [2 => (object) ['name' => $malicious, 'picture_url' => '']],
        [(object) [
            'federation' => 2,
            'name' => 'Team One',
            'ranking_link' => 'index.php?option=com_sportsmanagement',
            'flag_html' => '',
        ]]
    );
    $label = \Joomla\CMS\HTML\HTMLHelper::$tabLabels[0] ?? '';

    if (str_contains($label, '<img src=x')
        || !str_contains($label, '&lt;img src=x')
        || !str_contains($html, 'Team One')
    ) {
        throw new \RuntimeException('Federation tab name was not escaped or league is hidden');
    }

    echo "First league overview tab regressions OK\n";
}
