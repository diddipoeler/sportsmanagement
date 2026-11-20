<?php
/**
 * Joomla 5/6 Sports Type Statistics project-scope and icon regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }

    final class ParameterType
    {
        public const INTEGER = 'integer';
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

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://example.test/subdir/';
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

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static array $selections = [];

        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            self::$selections[] = $selector;

            return $fallback;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_sports_type_statistics/src/Helper/SportsTypeStatisticsHelper.php';

    final class SportsStatsQuery
    {
        public array $selects = [];
        public array $wheres = [];
        public array $bindings = [];
        public string $table = '';

        public function select(string|array $columns): self
        {
            foreach ((array) $columns as $column) {
                $this->selects[] = $column;
            }

            return $this;
        }

        public function from(string $table): self
        {
            $this->table = $table;
            return $this;
        }

        public function where(string $predicate): self
        {
            $this->wheres[] = $predicate;
            return $this;
        }

        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bindings[$name] = [$value, $type];
            return $this;
        }
    }

    final class SportsStatsDatabase implements \Joomla\Database\DatabaseInterface
    {
        public array $queries = [];
        public mixed $sportRecordId = 7;
        private ?SportsStatsQuery $current = null;

        public function __construct(private int $projectCount = 4)
        {
        }

        public function createQuery(): SportsStatsQuery
        {
            return new SportsStatsQuery();
        }

        public function quoteName(string $column, ?string $alias = null): string
        {
            return $column;
        }

        public function setQuery(SportsStatsQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->queries[] = $query;
            $this->current = $query;
        }

        public function loadObject(): object
        {
            return (object) [
                'id' => $this->sportRecordId,
                'name' => 'Football & <img>',
                'icon' => 'https://cdn.example.test/football.png',
            ];
        }

        public function loadResult(): int
        {
            $sql = implode(', ', $this->current?->selects ?? []);

            return match ($sql) {
                'COUNT(DISTINCT p.league_id)' => 2,
                'COUNT(DISTINCT p.season_id)' => 3,
                'COUNT(*)' => $this->projectCount,
                default => throw new \RuntimeException('Unexpected statistics query: ' . $sql),
            };
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper\SportsTypeStatisticsHelper();
    $db = new SportsStatsDatabase();
    $disabled = array_fill_keys([
        'show_playgrounds', 'show_clubs', 'show_teams', 'show_players',
        'show_divisions', 'show_rounds', 'show_matches',
        'show_player_events', 'show_player_stats',
    ], 0);
    $params = new \Joomla\Registry\Registry($disabled + [
        'sportstypes' => 7,
        'show_leagues' => 1,
        'show_seasons' => 1,
        'sportstypes_picture_width' => 999999,
        'show_icon' => 0,
    ]);

    $data = $helper->getData($params, $db);
    if ($data['counts'] !== ['projects' => 4, 'leagues' => 2, 'seasons' => 3]
        || ($data['sportstype']->icon_url ?? '') !== 'https://cdn.example.test/football.png'
        || count($db->queries) !== 4
    ) {
        throw new \RuntimeException('Sport statistics counters or icon source are invalid');
    }

    $expectedColumns = ['COUNT(DISTINCT p.league_id)', 'COUNT(DISTINCT p.season_id)'];
    foreach ($db->queries as $query) {
        $column = $query->selects[0] ?? '';
        if (!in_array($column, $expectedColumns, true)) {
            continue;
        }

        $field = str_contains($column, 'league_id') ? 'league_id' : 'season_id';
        if ($query->table !== '#__sportsmanagement_project'
            || !in_array('p.sports_type_id = :sportTypeId', $query->wheres, true)
            || !in_array('p.' . $field . ' > 0', $query->wheres, true)
            || $query->bindings[':sportTypeId'] !== [7, \Joomla\Database\ParameterType::INTEGER]
        ) {
            throw new \RuntimeException('Distinct sport dimension was not filtered by sport type');
        }

        $expectedColumns = array_values(array_diff($expectedColumns, [$column]));
    }

    if ($expectedColumns !== []) {
        throw new \RuntimeException('Both league and season queries must use DISTINCT');
    }

    $dimension = new \ReflectionMethod($helper, 'countProjectDimension');
    $otherDb = new SportsStatsDatabase();
    if ($dimension->invoke($helper, $otherDb, 'league_id', 0) !== 0
        || $dimension->invoke($helper, $otherDb, 'invalid', 7) !== 0
        || $otherDb->queries !== []
    ) {
        throw new \RuntimeException('Invalid project dimension must not reach the database');
    }

    $iconCases = [
        ['images/sport logo.png', 'https://example.test/subdir/images/sport%20logo.png'],
        ['/images/sport.png', 'https://example.test/subdir/images/sport.png'],
        ['https://cdn.example.test/sport.png', 'https://cdn.example.test/sport.png'],
        ['http://cdn.example.test/sport.png', 'http://cdn.example.test/sport.png'],
        ['javascript:alert(1)', ''],
        ['data:image/svg+xml,test', ''],
        ['//evil.example.test/icon.png', ''],
        ['images/../outside.png', ''],
        ['images/./icon.png', ''],
        ['images\..\evil.png', ''],
        ['images/%2e%2e/file.png', ''],
        ['https://user:secret@cdn.example.test/icon.png', ''],
        ["https://cdn.example.test/\nicon.png", ''],
    ];

    foreach ($iconCases as [$icon, $expected]) {
        if ($helper::imageUrl($icon) !== $expected) {
            throw new \RuntimeException('Invalid sports icon URL for ' . json_encode($icon));
        }
    }

    $statistics = $data;
    ob_start();

    try {
        require dirname(__DIR__, 2)
            . '/modules/mod_sportsmanagement_sports_type_statistics/tmpl/default.php';
        $html = (string) ob_get_contents();
    } finally {
        ob_end_clean();
    }

    if (!str_contains($html, 'src="https://cdn.example.test/football.png"')
        || !str_contains($html, 'width="400"')
        || !str_contains($html, 'Football &amp; &lt;img&gt;')
        || str_contains($html, 'https://example.test/subdir/https://')
    ) {
        throw new \RuntimeException('Sport type icon or title rendered incorrectly');
    }

    // No projects means the layout displays an empty state. Do not execute
    // expensive queries against unrelated global tables in that case.
    $emptyDb = new SportsStatsDatabase(0);
    $emptyData = $helper->getData($params, $emptyDb);
    if ($emptyData['counts'] !== ['projects' => 0]
        || count($emptyDb->queries) !== 2
    ) {
        throw new \RuntimeException('Empty sports must not count unrelated records');
    }

    $unconfiguredDb = new SportsStatsDatabase();
    $unconfigured = $helper->getData(
        new \Joomla\Registry\Registry(['sportstypes' => 0]),
        $unconfiguredDb
    );
    if ($unconfigured['counts'] !== ['projects' => 0]
        || $unconfiguredDb->queries !== []
        || \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::$selections !== [0, 0]
    ) {
        throw new \RuntimeException('Unconfigured sport must not trigger statistics queries');
    }

    // An unconfigured sport may have an external DB selected. Do not open it
    // when the widget cannot produce a sport-scoped result.
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;
    $before = count($resolver::$selections);
    $noSport = $helper->getData(
        new \Joomla\Registry\Registry(['sportstypes' => '0000', 'cfg_which_database' => 1]),
        $unconfiguredDb
    );
    if ($noSport !== ['sportstype' => null, 'counts' => ['projects' => 0]]
        || count($resolver::$selections) !== $before) {
        throw new \RuntimeException('Empty sport opened the external database connection.');
    }

    // Malformed Joomla module settings must not issue any database queries.
    $parseSelector = new \ReflectionMethod($helper, 'nonNegativeSelector');

    foreach ([
        [0, 0], [7, 7], ['000', 0], ['0007', 7],
        [-1, null], ['-7', null], ['7junk', null],
        ['7.5', null], [true, null], [['7'], null],
        ['999999999999999999999999999', null],
    ] as [$raw, $expected]) {
        if ($parseSelector->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Invalid sport statistics selector was accepted.');
        }
    }

    foreach ([
        ['sportstypes' => '7junk'],
        ['sportstypes' => ['7']],
        ['sportstypes' => '999999999999999999999999999'],
        ['sportstypes' => 7, 'cfg_which_database' => '1bad'],
        ['sportstypes' => 7, 'cfg_which_database' => true],
        ['sportstypes' => 7, 'cfg_which_database' => -1],
    ] as $settings) {
        $guardDb = new SportsStatsDatabase();
        $guardData = $helper->getData(new \Joomla\Registry\Registry($settings), $guardDb);

        if ($guardDb->queries !== []
            || $guardData !== ['sportstype' => null, 'counts' => ['projects' => 0]]) {
            throw new \RuntimeException('Invalid sport statistics configuration performed SQL.');
        }
    }

    // Legacy template overrides should use the validated record ID, not
    // cast arbitrary raw module settings into an array index.
    if (!defined('JPATH_SITE')) {
        define('JPATH_SITE', dirname(__DIR__, 2));
    }
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_sports_type_statistics/helper.php';
    // Invalid sport selectors must return before obtaining the Joomla DB
    // from the container, which is intentionally unavailable in this test.
    $legacySelector = new \ReflectionMethod('modJSMSportsHelper', 'selectedSportId');

    foreach ([
        [7, 7], ['0007', 7], [0, 0], ['000', 0],
        [false, 0], [true, 0], [['7'], 0],
        ['7junk', 0], ['7.5', 0], ['-7', 0],
        ['99999999999999999999999999', 0],
    ] as [$raw, $expected]) {
        if ($legacySelector->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Legacy statistics sport ID validation failed.');
        }
    }

    foreach ([0, '0000', '7junk', ['7'], true] as $invalid) {
        $params = new \Joomla\Registry\Registry(['sportstypes' => $invalid]);

        if (\modJSMSportsHelper::getData($params) !== ['sportstype' => [], 'projectscount' => 0]) {
            throw new \RuntimeException('Invalid legacy sport selection tried to open the database.');
        }
    }

    $legacyParams = new \Joomla\Registry\Registry($disabled + [
        'sportstypes' => '0007',
        'show_leagues' => 1,
        'show_seasons' => 1,
    ]);
    $bridgeDb = new SportsStatsDatabase();
    $legacy = \modJSMSportsHelper::getData($legacyParams, $bridgeDb);

    if (!isset($legacy['sportstype'][7])
        || $legacy['projectscount'] !== 4
        || $legacy['leaguescount'] !== 2) {
        throw new \RuntimeException('Legacy statistics bridge dropped a valid sport.');
    }

    $invalidRecordDb = new SportsStatsDatabase();
    $invalidRecordDb->sportRecordId = '7junk';
    $invalidRecord = \modJSMSportsHelper::getData($legacyParams, $invalidRecordDb);

    if ($invalidRecord['sportstype'] !== []) {
        throw new \RuntimeException('Legacy statistics bridge accepted malformed sport ID.');
    }

    echo "Sports Type Statistics project dimensions and icon URLs OK\n";
}
