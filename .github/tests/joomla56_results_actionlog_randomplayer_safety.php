<?php
/**
 * Regression checks for Joomla 5/6 results compatibility, action logs and random player CSS.
 *
 * Usage: php .github/tests/joomla56_results_actionlog_randomplayer_safety.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language {
    /** Minimal Joomla language translation fixture for standalone layout tests. */
    final class Text
    {
        /**
         * Return stable language identifiers for deterministic output checks.
         *
         * @param string $key Translation key requested by the layout.
         * @return string Safe untranslated key for isolated execution.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\Component\Actionlogs\Administrator\Model {
    /** Capture admin action-log writes for regression assertions. */
    final class ActionlogModel
    {
        public static array $events = [];

        /**
         * Store action-log messages without Joomla's database or application.
         *
         * @param array $messages Action-log rows.
         * @param string $languageKey Translated log action label.
         * @param string $context Component context identifier.
         * @param int $userId User responsible for the action.
         * @return void
         */
        public function addLog(array $messages, string $languageKey, string $context, int $userId): void
        {
            self::$events[] = $messages;
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    $root = \dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/ResultsLegacyProjectCompatibility.php';
    require_once $root . '/admin/src/Helper/ActionLogHelper.php';

    $facade = \Diddipoeler\Component\SportsManagement\Site\Helper\ResultsLegacyProjectCompatibility::class;
    $facade::register();

    if (!\class_exists('sportsmanagementModelProject', false)) {
        throw new \RuntimeException('Legacy results project class alias was not registered.');
    }

    $events = [(object) ['type' => 'goal'], (object) ['type' => 'yellow']];
    $substitutions = [(object) ['player' => 7]];
    $facade::sync(
        (object) ['id' => 12, 'season_id' => 3, 'slug' => '12:sample', 'round_slug' => '5:round'],
        5,
        1,
        [70 => $events],
        [70 => $substitutions],
        [2, 4]
    );

    if ($facade::$projectid !== 12
        || $facade::$cfg_which_database !== 1
        || \array_map(static fn (object $row): string => $row->type, $facade::getMatchEvents(70, 0, 1))
            !== ['yellow', 'goal']
        || $facade::getMatchSubstitutions(70) !== $substitutions
        || $facade::getMatchEvents(999) !== []) {
        throw new \RuntimeException('Legacy results facade no longer exposes native view data correctly.');
    }

    $action = \Diddipoeler\Component\SportsManagement\Administrator\Helper\ActionLogHelper::class;

    $action::record(
        (object) ['id' => 10, 'username' => 'admin'],
        ['id' => 6, 'name' => ['invalid nested value']],
        false,
        'club'
    );
    $action::record(
        (object) ['id' => 10, 'username' => 'admin'],
        ['id' => 7, 'firstname' => ['invalid'], 'lastname' => (object) ['bad' => true]],
        true,
        'player'
    );
    $logs = \Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel::$events;

    if (\count($logs) !== 2
        || $logs[0][0]['type'] !== 'JLIB_INSTALLER_UPDATE'
        || $logs[1][0]['type'] !== 'JTOOLBAR_NEW') {
        throw new \RuntimeException('Malformed legacy labels must not break administrator action logs.');
    }

    // A standalone Joomla native.php include supplies the layout contract.
    $params = new class {
        /**
         * Return configured module parameters, including hostile CSS input.
         *
         * @param string $key Joomla module parameter name.
         * @param mixed $default Fallback when not configured.
         * @return mixed Test setting value or original default.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return match ($key) {
                'mode' => 'S',
                'border_color' => 'red;position:fixed',
                'background_color' => 'blue;background-image:url(https://example.invalid)',
                'text_color' => '#123456',
                default => $default,
            };
        }
    };

    $list = ['player' => (object) [
        'display_name' => 'Player <test>',
        'player_url' => '/player',
        'team_url' => '/team',
        'picture_url' => '',
        'team_picture_url' => '',
        'flag_url' => '',
        'project_name' => 'League',
        'team_name' => 'Team',
        'position_name' => 'Forward',
        'country' => 'DEU',
    ]];

    \ob_start();
    require $root . '/modules/mod_sportsmanagement_randomplayer/tmpl/native.php';
    $html = (string) \ob_get_clean();

    if (\str_contains($html, 'position:fixed')
        || \str_contains($html, 'background-image:')
        || !\str_contains($html, 'border:2px solid rebeccapurple')
        || !\str_contains($html, 'color:#123456')
        || !\str_contains($html, 'Player &lt;test&gt;')) {
        throw new \RuntimeException('RandomPlayer CSS or escaped player names regressed.');
    }

    echo "Joomla 5/6 results compatibility, action-log labels and RandomPlayer CSS: OK\n";
}
