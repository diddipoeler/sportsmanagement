<?php
/**
 * Joomla 5/6 regression tests for prediction scoring, quote filters and league tabs.
 *
 * Usage: php .github/tests/joomla56_prediction_quotes_firstleague_guards.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a stable untranslated label for independent Joomla fixtures.
         *
         * @param string $key Joomla translation key.
         * @return string Test language marker.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Provide a predictable Joomla subdirectory root for flag media.
         *
         * @return string Test website root URL.
         */
        public static function root(): string
        {
            return 'https://sports.example/joomla/';
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        /**
         * Provide component flag settings without bootstrapping Joomla.
         *
         * @param string $component Joomla component identifier.
         * @return \Joomla\Registry\Registry Component settings fixture.
         */
        public static function getParams(string $component): \Joomla\Registry\Registry
        {
            return new \Joomla\Registry\Registry(['cfg_flags_css' => 0]);
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $messages = [];

        /**
         * Capture protected server-side database error messages.
         *
         * @param string $message Internal component diagnostic.
         * @param int $priority Joomla severity level.
         * @param string $category Component log category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
        {
            self::$messages[] = [$message, $priority, $category];
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Hold Joomla component or module options for the fixture.
         *
         * @param array<string,mixed> $values Settings for testing.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Get a configured Joomla parameter.
         *
         * @param string $name Requested option.
         * @param mixed $default Fallback for missing options.
         * @return mixed Stored option value or fallback.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        public static array $lastQuery = [];

        /**
         * Capture the ranking route parameters that the module constructs.
         *
         * @param string $view SportsManagement frontend view.
         * @param array<string,mixed> $query Joomla route parameters.
         * @return string Mock frontend route.
         */
        public static function view(string $view, array $query): string
        {
            self::$lastQuery = $query;
            return 'index.php?view=' . $view;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        /**
         * Preserve the supplied Joomla database for the isolated module.
         *
         * @param \Joomla\Database\DatabaseInterface $database Test database.
         * @param int $selector Validated database selection.
         * @return \Joomla\Database\DatabaseInterface Selected test connection.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $database;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class PredictionresultsModel
    {
        public bool $admin = true;
        public mixed $project = null;
        public mixed $rows = [];
        public int $rowFetches = 0;
        public array $scoreModes = [];
        public object $database;

        /**
         * Report whether the fixture allows administrator scoring writes.
         *
         * @return bool Simulated Joomla permission check.
         */
        public function isAllowedAdmin(): bool
        {
            return $this->admin;
        }

        /**
         * Supply the selected prediction game project.
         *
         * @return object|null Current project fixture.
         */
        public function getResultsProject(): ?object
        {
            return $this->project;
        }

        /**
         * Return the selected round from the native prediction controller.
         *
         * @param array<string,mixed> $config Scoring configuration.
         * @return int Fixture round ID.
         */
        public function getSelectedRoundId(array $config): int
        {
            return 3;
        }

        /**
         * Fetch submitted prediction results for the selected scope.
         *
         * @param int $projectId Validated project identifier.
         * @param int $firstRound First selected project round.
         * @param int $lastRound Last selected project round.
         * @return mixed Stored rows including invalid old-driver values.
         */
        public function getPredictionMembersResultsList(int $projectId, int $firstRound, int $lastRound): mixed
        {
            ++$this->rowFetches;
            return $this->rows;
        }

        /**
         * Provide a fake database for the score persistence operation.
         *
         * @return object Database with updateObject() fixture.
         */
        public function getDatabase(): object
        {
            return $this->database;
        }

        /**
         * Mark all supported fixture rows as having completed match results.
         *
         * @param object $row Stored prediction result.
         * @return bool True for fixture scoring rows.
         */
        public function hasPlayedResult(object $row): bool
        {
            return true;
        }

        /**
         * Observe the scoring-mode selector and preserve the prediction row.
         *
         * @param object $row Stored prediction.
         * @param int $mode Validated scoring mode.
         * @return object Current scoring fixture.
         */
        public function normaliseResultForScoring(object $row, int $mode): object
        {
            $this->scoreModes[] = $mode;
            return $row;
        }

        /**
         * Return one deterministic nonzero score for each valid row.
         *
         * @param object $project Prediction project.
         * @param object $row Normalised prediction.
         * @return int Test score.
         */
        public function calculateScore(object $project, object $row): int
        {
            return 5;
        }

        /**
         * Calculate an unchanged classification for the valid fixture.
         *
         * @param object $project Selected game project.
         * @param object $row Normalised prediction row.
         * @return array<string,int|string> Predicted score classification.
         */
        public function classifyPrediction(object $project, object $row): array
        {
            return ['tipp' => 'X', 'top' => 1, 'diff' => 2, 'tend' => 3];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel
    {
        public array $state = [];
        public object $database;

        /**
         * Receive native MVC model configuration without initialising Joomla.
         *
         * @param array<string,mixed> $config Joomla model configuration.
         * @param mixed $factory Optional Joomla factory.
         */
        public function __construct($config = [], $factory = null)
        {
        }

        /**
         * Provide a no-op Joomla parent list-state initialiser.
         *
         * @param string $ordering Default ordering.
         * @param string $direction Default direction.
         * @return void
         */
        protected function populateState($ordering = '', $direction = 'ASC')
        {
        }

        /**
         * Read one Joomla list-model state field.
         *
         * @param string $name State key.
         * @param mixed $default Fallback for missing state.
         * @return mixed State filter.
         */
        public function getState(string $name, mixed $default = null): mixed
        {
            return $this->state[$name] ?? $default;
        }

        /**
         * Supply the SQL fixture to the administrator list query builder.
         *
         * @return object Database with fluent query support.
         */
        protected function getDatabase(): object
        {
            return $this->database;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    /**
     * Turn PHP 8 warnings and deprecations into failed migration tests.
     *
     * @param int $severity PHP warning level.
     * @param string $message Warning text.
     * @return never Always fails the unsafe operation.
     */
    function failPredictionQuoteWarning(int $severity, string $message): never
    {
        throw new \ErrorException($message, 0, $severity);
    }

    /**
     * Verify one Joomla 5/6 runtime migration contract.
     *
     * @param bool $condition Whether the expected result occurred.
     * @param string $message Diagnostic when the contract was violated.
     * @return void
     */
    function assertPredictionQuoteCompat(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * Simulate the safe Joomla updateObject persistence interface.
     */
    final class PredictionWriteDatabase
    {
        public array $writes = [];

        /**
         * Record one scoped prediction result update.
         *
         * @param string $table Joomla prediction results table name.
         * @param object $row Updated scoring fields.
         * @param string $key Database record primary key.
         * @return bool Whether the fixture wrote the score.
         */
        public function updateObject(string $table, object $row, string $key): bool
        {
            $this->writes[] = [$table, clone $row, $key];
            return true;
        }
    }

    /**
     * Capture SQL parameters and order terms without a Joomla installation.
     */
    final class QuoteListQuery
    {
        public array $bindings = [];
        public array $where = [];
        public string $orderBy = '';

        /**
         * Accept the Joomla quote-list columns.
         *
         * @param array<int,string> $columns Quoted SELECT expressions.
         * @return self Fluent query fixture.
         */
        public function select(array $columns): self { return $this; }

        /**
         * Select the published quote table.
         *
         * @param string $name Native Joomla table reference.
         * @return self Fluent query fixture.
         */
        public function from(string $name): self { return $this; }

        /**
         * Accept a Joomla user/category lookup join.
         *
         * @param string $kind Join type.
         * @param string $statement SQL join expression.
         * @return self Fluent query fixture.
         */
        public function join(string $kind, string $statement): self { return $this; }

        /**
         * Record an administrator filter WHERE condition.
         *
         * @param string $condition SQL expression.
         * @return self Fluent query fixture.
         */
        public function where(string $condition): self
        {
            $this->where[] = $condition;
            return $this;
        }

        /**
         * Capture a bound search, category or state parameter.
         *
         * @param string $name Named SQL parameter.
         * @param mixed $value Validated value.
         * @param string $type Joomla parameter type.
         * @return self Fluent query fixture.
         */
        public function bind(string $name, mixed $value, string $type): self
        {
            $this->bindings[$name] = [$value, $type];
            return $this;
        }

        /**
         * Capture the selected administrator sort expression.
         *
         * @param string $expression ORDER BY SQL fragment.
         * @return self Fluent query fixture.
         */
        public function order(string $expression): self
        {
            $this->orderBy = $expression;
            return $this;
        }
    }

    /**
     * Simulate Joomla's quote list query and escape service.
     */
    final class QuoteListDatabase
    {
        /**
         * Create a fresh parameter-capturing Joomla SQL query.
         *
         * @return QuoteListQuery Query fixture.
         */
        public function createQuery(): QuoteListQuery
        {
            return new QuoteListQuery();
        }

        /**
         * Return a quoted SQL field with an optional alias.
         *
         * @param string $name Database column name.
         * @param string|null $alias Optional output alias.
         * @return string SQL fixture identifier.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name . ($alias !== null ? ' AS ' . $alias : '');
        }

        /**
         * Escape wildcard-sensitive search terms for Joomla SQL.
         *
         * @param string $text Quote search fragment.
         * @param bool $extra Whether to escape wildcards.
         * @return string Escaped search fragment.
         */
        public function escape(string $text, bool $extra = false): string
        {
            return $text;
        }
    }

    /**
     * Provide the chainable Joomla SQL builder for first league queries.
     */
    final class LeagueQuery
    {
        /**
         * Accept league/project or federation SELECT columns.
         *
         * @param array<int,string> $fields Queried columns.
         * @return self Fluent Joomla query.
         */
        public function select(array $fields): self { return $this; }

        /**
         * Accept a table selected through Joomla quoteName().
         *
         * @param string $name SQL table identifier.
         * @return self Fluent Joomla query.
         */
        public function from(string $name): self { return $this; }

        /**
         * Accept published league and country joins.
         *
         * @param string $type Join kind.
         * @param string $expression Joined table conditions.
         * @return self Fluent Joomla query.
         */
        public function join(string $type, string $expression): self { return $this; }

        /**
         * Apply a league restriction to the statement.
         *
         * @param string $expression Condition expression.
         * @return self Fluent Joomla query.
         */
        public function where(string $expression): self { return $this; }

        /**
         * Bind the league champions-complete selector.
         *
         * @param string $key Named SQL placeholder.
         * @param mixed $value Bound scalar.
         * @param string $type Joomla SQL binding type.
         * @return self Fluent Joomla query.
         */
        public function bind(string $key, mixed $value, string $type): self { return $this; }

        /**
         * Accept bounded numeric league levels in the query.
         *
         * @param string $column League field.
         * @param array<int,int> $values League levels.
         * @param string $type Joomla parameter type.
         * @return self Fluent Joomla query.
         */
        public function whereIn(string $column, array $values, string $type): self { return $this; }

        /**
         * Configure league and federation sort order.
         *
         * @param string|array $fields Order expressions.
         * @return self Fluent Joomla query.
         */
        public function order(string|array $fields): self { return $this; }
    }

    /**
     * Return mixed legacy league rows and federation information.
     */
    final class LeagueDatabase implements \Joomla\Database\DatabaseInterface
    {
        public mixed $projects = [];
        public mixed $federations = [];
        public int $queries = 0;

        /**
         * Construct a league/federation Joomla query.
         *
         * @return LeagueQuery Fluent query fixture.
         */
        public function createQuery(): LeagueQuery
        {
            return new LeagueQuery();
        }

        /**
         * Quote database field or table names including optional aliases.
         *
         * @param string $column Raw database name.
         * @param string|null $alias Optional alias.
         * @return string Stable SQL field identifier.
         */
        public function quoteName(string $column, ?string $alias = null): string
        {
            return $column;
        }

        /**
         * Record the selected league/federation query.
         *
         * @param LeagueQuery $query Prepared Joomla query.
         * @return void
         */
        public function setQuery(LeagueQuery $query): void
        {
        }

        /**
         * Return result rows for the selected league or federation table.
         *
         * @return mixed Legacy driver row result.
         */
        public function loadObjectList(): mixed
        {
            ++$this->queries;
            return $this->queries % 2 === 1 ? $this->projects : $this->federations;
        }
    }

    set_error_handler('failPredictionQuoteWarning');
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/PredictionpointsModel.php';
    require_once $root . '/admin/src/Model/SmquotesModel.php';
    require_once $root . '/modules/mod_sportsmanagement_firstleagueoverview/src/Helper/FirstLeagueOverviewHelper.php';

    $db = new PredictionWriteDatabase();
    $scoring = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictionpointsModel();
    $scoring->database = $db;
    $scoring->project = (object) ['project_id' => '0007'];
    $scoring->rows = [
        null,
        ['id' => 4],
        (object) ['prID' => '5garbage'],
        (object) ['prID' => ['5']],
        (object) [
            'prID' => '0005',
            'prHomeTipp' => 1, 'prAwayTipp' => 1, 'prJoker' => 0,
            'prTipp' => null, 'prPoints' => ['bad'],
        ],
        (object) [
            'prID' => '0005',
            'prHomeTipp' => '1', 'prAwayTipp' => '1', 'prJoker' => 0,
            'prTipp' => null, 'prPoints' => '0', 'prTop' => '0',
            'prDiff' => 0, 'prTend' => 0,
        ],
        (object) [
            'prID' => 6,
            'prHomeTipp' => 1, 'prAwayTipp' => 1, 'prJoker' => 0,
            'prTipp' => 'X', 'prPoints' => '5', 'prTop' => 1,
            'prDiff' => '2', 'prTend' => 3,
        ],
    ];

    assertPredictionQuoteCompat(
        $scoring->recalculatePoints(['use_match_result' => ['invalid']]) === 1
        && count($db->writes) === 1
        && $db->writes[0][1]->id === 5
        && $scoring->scoreModes === [0, 0],
        'Prediction scoring converted malformed input or updated wrong rows.'
    );

    $scoring->project = (object) ['project_id' => '7junk'];
    assertPredictionQuoteCompat(
        $scoring->recalculatePoints([]) === 0 && $scoring->rowFetches === 1,
        'Invalid prediction project ID queried or wrote results.'
    );

    $scoring->admin = false;

    try {
        $scoring->recalculatePoints([]);
        throw new \RuntimeException('Prediction scoring bypassed ACL.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) {
            throw $error;
        }
    }

    $quote = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotesModel();
    $quote->database = new QuoteListDatabase();
    $listQuery = new \ReflectionMethod($quote, 'getListQuery');
    $quote->state = [
        'filter.search' => ['nested'],
        'filter.state' => '1e3',
        'filter.catid' => '99junk',
        'list.ordering' => ['obj.id'],
        'list.direction' => ['DESC'],
    ];

    $query = $listQuery->invoke($quote);
    assertPredictionQuoteCompat(
        $query->bindings === [] && $query->where === []
        && $query->orderBy === 'obj.quote ASC',
        'Malformed administrator quote filters reached SQL or changed sorting.'
    );

    $quote->state = [
        'filter.search' => '  John  ',
        'filter.state' => '-2',
        'filter.catid' => '0007',
        'list.ordering' => 'obj.author',
        'list.direction' => 'DESC',
    ];
    $query = $listQuery->invoke($quote);
    assertPredictionQuoteCompat(
        $query->bindings === [
            ':quoteSearch' => ['%John%', \Joomla\Database\ParameterType::STRING],
            ':quotePublished' => [-2, \Joomla\Database\ParameterType::INTEGER],
            ':quoteCategoryId' => [7, \Joomla\Database\ParameterType::INTEGER],
        ]
        && $query->orderBy === 'obj.author DESC',
        'Valid published/category/search quote filters changed.'
    );

    $quote->state = [
        'filter.state' => '999999999999999999999999999',
        'filter.catid' => '-1',
        'list.ordering' => 'obj.id;DELETE',
        'list.direction' => 'desc',
    ];
    $query = $listQuery->invoke($quote);
    assertPredictionQuoteCompat(
        $query->bindings === [] && $query->orderBy === 'obj.quote DESC',
        'Overflowing or SQL-like quote filters were not rejected.'
    );

    $helper = new \Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper();
    $helperClass = \Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Helper\FirstLeagueOverviewHelper::class;
    $catalogue = [
        2 => (object) ['id' => 2, 'name' => 'Europe'],
        'invalid' => (object) ['id' => 0, 'name' => 'Never visible'],
    ];
    $visible = $helperClass::visibleFederations([
        (object) ['federation' => '0002'],
        (object) ['federation' => null],
        (object) ['federation' => '99'],
        null,
        ['federation' => 'invalid'],
    ], $catalogue);

    assertPredictionQuoteCompat(
        array_keys($visible) === [2, 0, 99]
        && $visible[2] === $catalogue[2]
        && $visible[0]->id === 0
        && $visible[99]->id === 99,
        'Invalid league/federation rows produced unsafe or missing tabs.'
    );

    $ranking = new \ReflectionMethod($helperClass, 'rankingLink');
    $ranking->invoke($helper, (object) [
        'id' => '00042', 'season_id' => ['nested'],
        'project_slug' => ['nested'],
    ], new \Joomla\Registry\Registry());

    assertPredictionQuoteCompat(
        \Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper::$lastQuery['p'] === '42'
        && \Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper::$lastQuery['s'] === 0,
        'Malformed project route or season was cast into ranking parameters.'
    );

    $flags = new \ReflectionMethod($helperClass, 'flagHtml');
    $flag = $flags->invoke($helper, (object) [
        'country' => ['nested'],
        'alpha2' => ['nested'],
        'country_label' => ['unsafe'],
        'country_picture' => ['nested'],
    ], new \Joomla\Registry\Registry(['cfg_flags_css' => ['bad']]));

    assertPredictionQuoteCompat($flag === '', 'Malformed country flag fields reached HTML markup.');

    $leagueDb = new LeagueDatabase();
    $leagueDb->projects = [
        (object) [
            'id' => '00042', 'league_id' => '0007', 'season_id' => 6,
            'alias' => 'autumn', 'country_name' => ['nested'],
            'country' => 'DEU', 'alpha2' => 'de',
            'country_picture' => 'images/de.png', 'federation' => 2,
        ],
    ];
    $leagueDb->federations = [(object) ['id' => 2, 'name' => 'Europe', 'picture' => 'images/europe.png']];

    $result = $helper->getData(new \Joomla\Registry\Registry(), $leagueDb);

    assertPredictionQuoteCompat(
        count($result['projects']) === 1
        && $result['projects'][0]->country_label === 'DEU'
        && str_contains($result['projects'][0]->flag_html, '/flags/de.png')
        && array_keys($result['federations']) === [2],
        'League overview failed to recover valid country labels or federation tabs.'
    );

    $leagueDb->projects = false;
    $result = $helper->getData(new \Joomla\Registry\Registry(), $leagueDb);
    assertPredictionQuoteCompat(
        $result['projects'] === [] && $result['federations'] === [],
        'Malformed league database results triggered a PHP 8 warning.'
    );

    restore_error_handler();
    echo "Joomla 5/6 prediction, administrator quotes and first-league guards passed.\n";
}
