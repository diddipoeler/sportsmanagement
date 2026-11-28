<?php
/**
 * Exercise the real legacy model table resolver with minimal Joomla fixtures.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}
namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {}
    class ListModel extends BaseDatabaseModel {}
    class AdminModel extends BaseDatabaseModel {
        /** Return the default table name for the model fixture. */
        public function getName(): string { return 'player'; }
    }
}
namespace Joomla\CMS\Table {
    class Table {
        public static array $fallbacks = [];

        /** Capture the actual storage table, primary key and injected driver. */
        public function __construct(public string $table, public string $key, public object $database) {}

        /** Record historical factory calls without requiring a Joomla installation. */
        public static function getInstance($type, $prefix, $config): object {
            self::$fallbacks[] = [$type, $prefix, $config];
            return (object) ['legacy' => true];
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    class SportsManagementDatabaseResolver {
        public static ?\Joomla\Database\DatabaseInterface $replacement = null;
        public static int $calls = 0;

        /** Simulate a resolver that may return a different connection. */
        public function resolve($selector, $db): object {
            self::$calls++;
            return self::$replacement ?? $db;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $prefix = 'Diddipoeler\\Component\\SportsManagement\\Administrator\\Table\\';

    spl_autoload_register(static function (string $class) use ($root, $prefix): void {
        if (str_starts_with($class, $prefix)) {
            $file = $root . '/admin/src/Table/' . substr($class, strlen($prefix)) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        }
    });

    require_once $root . '/admin/libraries/sportsmanagement/model.php';
    $model = (new \ReflectionClass(\JSMModelAdmin::class))->newInstanceWithoutConstructor();
    $db = new class implements \Joomla\Database\DatabaseInterface {};
    $model->jsmdb = $db;

    // Simulate a changed database selection after model initialisation.
    $resolver = \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver::class;
    $resolver::$replacement = new class implements \Joomla\Database\DatabaseInterface {};
    $initialCalls = $resolver::$calls;

    foreach ([
        ['', 'PersonTable', '#__sportsmanagement_person'],
        ['player', 'PersonTable', '#__sportsmanagement_person'],
        ['PLAYER', 'PersonTable', '#__sportsmanagement_person'],
        ['jsmgcalendarimport', 'JsmgcalendarTable', '#__sportsmanagement_gcalendar'],
        ['matchsingle', 'MatchSingleTable', '#__sportsmanagement_match_single'],
        ['teamtrainingdata', 'TeamTrainingDataTable', '#__sportsmanagement_team_trainingdata'],
        ['teamstaff', 'TeamstaffTable', '#__sportsmanagement_team_staff'],
        ['jlextcountry', 'JlextcountryTable', '#__sportsmanagement_countries'],
        ['smquote', 'SmquoteTable', '#__sportsmanagement_rquote'],
    ] as [$type, $expectedClass, $expectedTable]) {
        $table = $model->getTable($type);
        if (get_class($table) !== $prefix . $expectedClass || $table->database !== $db
            || $table->table !== $expectedTable || $table->key !== 'id'
            || $resolver::$calls !== $initialCalls) {
            throw new \RuntimeException('Incorrect native table resolution: ' . $type);
        }
    }

    foreach (['../player', 'foo/bar', 'foo\\bar', '1person', str_repeat('a', 129), [], null] as $invalid) {
        try {
            $model->getTable($invalid);
            throw new \RuntimeException('Invalid table name accepted.');
        } catch (\InvalidArgumentException $expected) {
            // Expected: reject before invoking either factory.
        }
    }

    if (\Joomla\CMS\Table\Table::$fallbacks !== []) {
        throw new \RuntimeException('Native or invalid table request invoked legacy factory.');
    }
    $model->getTable('smquotestxt');
    $model->getTable('player', 'ExternalTable', ['custom' => 1]);

    $calls = \Joomla\CMS\Table\Table::$fallbacks;
    if (count($calls) !== 2 || $calls[0] !== ['smquotestxt', 'sportsmanagementTable', ['dbo' => $db]]
        || $calls[1] !== ['player', 'ExternalTable', ['custom' => 1, 'dbo' => $db]]) {
        throw new \RuntimeException('Legacy fallback did not preserve configuration.');
    }

    // An already resolved connection must survive table construction unchanged.
    foreach ([
        'ProjectteamTable', 'MatchTable', 'PositioneventtypeTable', 'PositionstatisticTable',
        'TeamTable', 'TeamTrainingDataTable', 'PersonTable', 'ClubTable', 'LeagueTable',
        'ProjectTable', 'SeasonTable', 'RoundTable', 'DivisionTable', 'EventtypeTable', 'StatisticTable',
        'ProjectrefereeTable', 'TeamplayerTable', 'PlaygroundTable', 'SportstypeTable', 'AgegroupTable',
        'MatcheventTable', 'MatchplayerTable', 'MatchrefereeTable', 'MatchstaffTable',
        'MatchstaffstatisticTable', 'MatchstatisticTable', 'MatchcommentaryTable',
        'PredictionadminTable', 'PredictionentryTable', 'PredictiongameTable',
        'PredictiongroupTable', 'PredictionmemberTable', 'PredictionprojectTable',
        'PredictionresultTable', 'PredictionroundTable', 'PredictiontemplateTable',
        'PositionTable', 'ProjectpositionTable', 'RosterpositionTable', 'TreetomatchTable',
        'JlextassociationTable', 'JlextfederationTable',
        'TeamstaffTable', 'JsmgcalendarTable', 'JlextcountryTable', 'MatchSingleTable',
        'SmquoteTable',
    ] as $name) {
        $class = $prefix . $name;
        $before = $resolver::$calls;
        $resolved = new $class($db, true);
        if ($resolved->database !== $db || $resolver::$calls !== $before) {
            throw new \RuntimeException('Resolved connection was replaced: ' . $name);
        }
        $legacy = new $class($db);
        if ($legacy->database !== $resolver::$replacement || $resolver::$calls !== $before + 1) {
            throw new \RuntimeException('Default resolver contract changed: ' . $name);
        }
    }

    echo "Legacy administrator table resolution: OK\n";
}
