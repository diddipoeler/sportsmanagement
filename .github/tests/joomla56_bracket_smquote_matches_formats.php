<?php
/**
 * Joomla 5/6 regressions for tournament brackets, quote saves and match formatting.
 *
 * Run: php .github/tests/joomla56_bracket_smquote_matches_formats.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Represent a translated error or score label without a full CMS.
         *
         * @param string $key Language key from component or module.
         * @return string Stable translation fixture.
         */
        public static function _(string $key): string
        {
            return $key;
        }

        /**
         * Record the count supplied to Joomla's plural translation helper.
         *
         * @param string $key Translation key for saved quotes.
         * @param int $count Number of newly inserted records.
         * @return string Translation fixture containing the supplied count.
         */
        public static function plural(string $key, int $count): string
        {
            return $key . ':' . $count;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $messages = [];

        /**
         * Capture diagnostic information that must not reach public messages.
         *
         * @param string $message Internal error context.
         * @param int $priority Joomla logging level.
         * @param string $category Extension logging category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
        {
            self::$messages[] = [$message, $priority, $category];
        }
    }
}

namespace Joomla\Database {
    final class ParameterType
    {
        public const STRING = 'string';
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Supply match formatting settings without a Joomla installation.
         *
         * @param array<string,mixed> $values Module parameters.
         */
        public function __construct(private array $values = [])
        {
        }

        /**
         * Read a Joomla setting or fallback when not configured.
         *
         * @param string $name Parameter name.
         * @param mixed $default Default value.
         * @return mixed Selected or fallback parameter.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public static ?object $db = null;
        public static ?object $app = null;
        public int $parentHooks = 0;

        /**
         * Reuse the fixed Joomla application stub in the administrator model.
         *
         * @return object Stub for administrator notifications.
         */
        protected function administratorApplication(): object
        {
            return self::$app;
        }

        /**
         * Supply the injected fake Joomla database connection.
         *
         * @return object Database with fluent UPDATE query operations.
         */
        protected function getDatabase(): object
        {
            return self::$db;
        }

        /**
         * Record parent model post-save processing after image synchronisation.
         *
         * @param array<string,mixed> $data Saved quote fields.
         * @param int $id Persisted quote primary key.
         * @param bool $isNew Whether Joomla inserted the quote.
         * @return void
         */
        protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
        {
            ++$this->parentHooks;
        }

        /**
         * Preserve prepared fields without requiring Joomla form services.
         *
         * @param array<string,mixed> $data Submitted quote values.
         * @return array<string,mixed> Unchanged test payload.
         */
        protected function prepareSportsManagementData(array $data): array
        {
            return $data;
        }

        /**
         * Supply an inert fallback for unused native Joomla table lookups.
         *
         * @param string $type Requested Joomla table type.
         * @param string $prefix Legacy component table-class prefix.
         * @param array $config Optional table configuration.
         * @return null No table is required by this test.
         */
        public function getTable($type = '', $prefix = '', $config = [])
        {
            return null;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class SmquoteTable
    {
        /**
         * Build an optional fake quote table without database activity.
         *
         * @param mixed $database Joomla database connection.
         */
        public function __construct(mixed $database)
        {
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_matches/connectors/native/FormatTrait.php';

    /**
     * Reject runtime warnings and PHP 8 array conversions inside fixtures.
     *
     * @param int $severity Native PHP diagnostic severity.
     * @param string $message Warning or deprecation message.
     * @return never Always fails this regression.
     */
    function failBracketWarning(int $severity, string $message): never
    {
        throw new \ErrorException($message, 0, $severity);
    }

    /**
     * Assert expected compatibility behaviour with focused diagnostics.
     *
     * @param bool $condition Expected successful comparison.
     * @param string $message Detailed regression failure reason.
     * @return void
     */
    function assertBracketCompat(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * Provide a fluent Joomla UPDATE query for quote image synchronisation.
     */
    final class BracketQuoteQuery
    {
        public array $bindings = [];

        /**
         * Accept the stored quote table for an SQL UPDATE query.
         *
         * @param string $table Target Joomla quote table.
         * @return self Current fake query.
         */
        public function update(string $table): self { return $this; }

        /**
         * Accept the bound image update expression.
         *
         * @param string $assignment Named-parameter SQL assignment.
         * @return self Current fake query.
         */
        public function set(string $assignment): self { return $this; }

        /**
         * Accept the bound author restriction.
         *
         * @param string $condition SQL author match restriction.
         * @return self Current fake query.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Track the scalar author and image values submitted by the model.
         *
         * @param string $name Query placeholder.
         * @param mixed $value Parameter value.
         * @param string $type Joomla query binding type.
         * @return self Current fake query.
         */
        public function bind(string $name, mixed $value, string $type): self
        {
            $this->bindings[$name] = [$value, $type];
            return $this;
        }
    }

    /**
     * Simulate a Joomla database update and a failing optional image sync.
     */
    final class BracketQuoteDb
    {
        public int $queries = 0;
        public int $executions = 0;
        public bool $fail = false;
        public mixed $affected = 3;
        public ?BracketQuoteQuery $query = null;

        /**
         * Produce a fresh fluent UPDATE query.
         *
         * @return BracketQuoteQuery Fake query object.
         */
        public function createQuery(): BracketQuoteQuery
        {
            ++$this->queries;
            return new BracketQuoteQuery();
        }

        /**
         * Simulate Joomla SQL identifier quoting.
         *
         * @param string $value Column or table identifier.
         * @return string Quoted-identifier placeholder.
         */
        public function quoteName(string $value): string { return $value; }

        /**
         * Record a complete query and return the database for execution.
         *
         * @param BracketQuoteQuery $query Fluent image update query.
         * @return self Current database fixture.
         */
        public function setQuery(BracketQuoteQuery $query): self
        {
            $this->query = $query;
            return $this;
        }

        /**
         * Simulate a successful update or privileged database exception.
         *
         * @return void
         * @throws \RuntimeException In failure mode.
         */
        public function execute(): void
        {
            ++$this->executions;

            if ($this->fail) {
                throw new \RuntimeException('PRIVATE_SQL_PASSWORD=secret');
            }
        }

        /**
         * Return the number of rows updated by the fake Joomla driver.
         *
         * @return mixed Affected-row count, including invalid legacy values.
         */
        public function getAffectedRows(): mixed
        {
            return $this->affected;
        }
    }

    /**
     * Record translated administrator notices without a Joomla application.
     */
    final class BracketQuoteApp
    {
        public array $messages = [];

        /**
         * Capture public notice text and severity.
         *
         * @param string $message Translated administrator message.
         * @param string $type Joomla message style.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    }

    /**
     * Exercise the real Matches FormatTrait with a minimal option validator.
     */
    final class BracketMatchesFormatter
    {
        use \Diddipoeler\Module\SportsManagementMatches\Site\Helper\NativeFormatTrait;

        /**
         * Resolve the small integer flags used by the native match formatter.
         *
         * @param mixed $raw Raw module option.
         * @param int $default Fallback when malformed.
         * @param int $maximum Largest supported setting.
         * @return int Validated option.
         */
        private static function boundedMatchesOption(mixed $raw, int $default, int $maximum): int
        {
            if (is_int($raw)) {
                return max(0, min($maximum, $raw));
            }

            return is_string($raw) && preg_match('/^[0-9]+$/D', $raw) === 1
                ? max(0, min($maximum, (int) $raw))
                : $default;
        }
    }

    set_error_handler('failBracketWarning');

    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Model/TournamentbracketResultNormalizer.php';
    require_once $root . '/admin/src/Model/SmquoteModel.php';

    $normaliser = new \Diddipoeler\Component\SportsManagement\Site\Model\TournamentbracketResultNormalizer();

    $draw = $normaliser->normalise((object) [
        'team1_result' => 2,
        'team2_result' => 2,
        'team1_result_so' => 1,
        'team2_result_so' => 0,
    ]);

    assertBracketCompat(
        $draw === ['home' => '3 OT:2 SO:1', 'away' => '2 OT:2 SO:0'],
        'Valid legacy draw and shootout scores changed.'
    );

    $invalid = $normaliser->normalise((object) [
        'team1_result' => [1],
        'team2_result' => 1,
        'team1_result_so' => ['bad'],
        'team2_result_so' => INF,
        'team1_result_ot' => new \stdClass(),
        'team1_result_decision' => ['bad'],
    ]);

    assertBracketCompat(
        $invalid === ['home' => null, 'away' => 1],
        'Malformed tournament scores leaked array conversion warnings or output.'
    );

    $overflow = $normaliser->normalise((object) [
        'team1_result' => 1.0E+308,
        'team2_result' => 1.0E+308,
        'team1_result_so' => 1.0E+308,
    ]);

    assertBracketCompat(
        is_string($overflow['home']) && !str_contains($overflow['home'], 'INF'),
        'Tournament shootout sum overflowed a PHP float.'
    );

    $db = new BracketQuoteDb();
    $app = new BracketQuoteApp();
    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $base::$db = $db;
    $base::$app = $app;

    $quoteClass = \Diddipoeler\Component\SportsManagement\Administrator\Model\SmquoteModel::class;
    $quote = (new \ReflectionClass($quoteClass))->newInstanceWithoutConstructor();
    $after = new \ReflectionMethod($quoteClass, 'afterSportsManagementSave');

    foreach ([
        ['author' => ['nested'], 'picture' => 'images/logo.png'],
        ['author' => 'Example', 'picture' => ['nested']],
        ['author' => new \stdClass(), 'picture' => 'images/logo.png'],
        ['author' => 'Example', 'picture' => true],
        ['author' => "Name\ninjected", 'picture' => 'images/logo.png'],
        ['author' => 'Example', 'picture' => "bad\rimage.png"],
        ['author' => str_repeat('x', 260), 'picture' => 'images/logo.png'],
    ] as $bad) {
        $before = $db->queries;
        $after->invoke($quote, $bad, 7, false);

        assertBracketCompat($db->queries === $before, 'Malformed quote metadata executed SQL.');
    }

    assertBracketCompat($quote->parentHooks === 7, 'Invalid quote metadata bypassed the parent hook.');

    $after->invoke($quote, ['author' => ' A Writer ', 'picture' => 'images/person.png'], 1234, true);
    assertBracketCompat(
        $db->queries === 1 && $db->executions === 1
        && $db->query?->bindings === [
            ':quotePicture' => ['images/person.png', \Joomla\Database\ParameterType::STRING],
            ':quoteAuthor' => ['A Writer', \Joomla\Database\ParameterType::STRING],
        ]
        && $quoteClass::$db_num_rows === 3
        && $app->messages === [['COM_SPORTSMANAGEMENT_N_ITEMS_CREATED:1', 'message']]
        && $quote->parentHooks === 8,
        'Valid quote image sync, created count or Joomla parent hook regressed.'
    );

    $db->affected = ['invalid'];
    $after->invoke($quote, ['author' => ' A Writer ', 'picture' => ''], 1234, false);
    assertBracketCompat($quoteClass::$db_num_rows === 0,
        'Invalid affected-row count triggered PHP 8 conversion warnings.');

    $db->fail = true;
    $after->invoke($quote, ['author' => 'A Writer', 'picture' => 'images/other.png'], 1234, false);
    assertBracketCompat(
        $app->messages[1] === ['JERROR_AN_ERROR_HAS_OCCURRED', 'warning']
        && count(\Joomla\CMS\Log\Log::$messages) === 1
        && !str_contains(\Joomla\CMS\Log\Log::$messages[0][0], 'PRIVATE_SQL_PASSWORD')
        && $quote->parentHooks === 10,
        'Quote image sync failure leaked private SQL or skipped parent hook.'
    );

    $formatter = new BracketMatchesFormatter();
    $format = new \ReflectionMethod($formatter, 'result');
    $params = new \Joomla\Registry\Registry(['part_result' => 1]);
    $row = ['cancel' => false];
    $match = (object) [
        'team1_result' => 0,
        'team2_result' => 2,
        'team1_result_so' => ['bad'],
        'team2_result_so' => INF,
        'team1_result_ot' => new \stdClass(),
        'team1_result_split' => ['nested'],
        'team2_result_split' => ['nested'],
    ];
    $format->invokeArgs($formatter, [&$row, $match, $params]);
    assertBracketCompat($row['result'] === '0:2' && $row['partresults'] === '',
        'Nested module result fields produced invalid or warning-prone output.');

    $row = ['cancel' => false];
    $match = (object) [
        'team1_result' => 2,
        'team2_result' => 1,
        'team1_result_so' => 0,
        'team2_result_so' => 3,
        'team1_result_split' => '1;1',
        'team2_result_split' => '0;1',
    ];
    $format->invokeArgs($formatter, [&$row, $match, $params]);
    assertBracketCompat(
        $row['result'] === 'INP 0:3'
        && $row['partresults'] === '(1:0-1:1)',
        'Valid penalty scores and period results changed.'
    );

    $row = ['cancel' => true];
    $format->invokeArgs($formatter, [&$row, (object) ['cancel_reason' => ['nested']], $params]);
    assertBracketCompat($row === ['cancel' => true, 'result' => '', 'partresults' => ''],
        'Nested cancellation reason produced PHP 8 conversion warnings.');

    $name = new \ReflectionMethod($formatter, 'formatName');
    assertBracketCompat(
        $name->invoke($formatter, (object) [
            'firstname' => ['nested'],
            'lastname' => 'Smith',
            'nickname' => new \stdClass(),
        ], 0) === 'Smith',
        'Nested referee name fields were converted to visible array text.'
    );

    restore_error_handler();
    echo "Joomla 5/6 bracket, admin quote and matches formatting regressions passed.\n";
}
