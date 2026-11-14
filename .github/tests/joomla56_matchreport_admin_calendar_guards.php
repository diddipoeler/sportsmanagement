<?php
/**
 * Regression checks for Joomla 5/6 match-report error privacy, administrator
 * person-season query parameter binding and signed calendar timestamps.
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
            return $key;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 400;
        public static array $entries = [];

        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Date {
    class Date extends \DateTime
    {
        public function __construct(string $value = 'now', string $timezone = 'UTC')
        {
            parent::__construct($value, new \DateTimeZone($timezone));
        }
    }
}

namespace Joomla\Database {
    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        public array $publicMessages = [];

        protected function siteApplication(): object
        {
            return new class($this) {
                public function __construct(private SportsManagementProjectModel $model)
                {
                }

                public function enqueueMessage(string $message, string $type): void
                {
                    $this->model->publicMessages[] = [$message, $type];
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', '/nonexistent-joomla-admin');
    $root = dirname(__DIR__, 2);
    $assert = static function (bool $ok, string $message): void {
        if (!$ok) {
            throw new \RuntimeException($message);
        }
    };

    class JSMModelAdmin
    {
        public object $jsmdb;
    }

    require_once $root . '/site/src/Model/MatchreportMatchDataModel.php';

    $class = new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Site\Model\MatchreportMatchDataModel::class
    );
    $report = $class->newInstanceWithoutConstructor();
    $method = $class->getMethod('reportDatabaseError');
    $secret = 'sql-private-connection-string';
    $method->invoke($report, new \RuntimeException($secret));

    $assert(
        $report->publicMessages === [['JERROR_AN_ERROR_HAS_OCCURRED', 'error']],
        'The public match-report error must be generic.'
    );
    $assert(
        count(\Joomla\CMS\Log\Log::$entries) === 1
        && str_contains(\Joomla\CMS\Log\Log::$entries[0][0], $secret)
        && \Joomla\CMS\Log\Log::$entries[0][2] === 'com_sportsmanagement',
        'Detailed match-report diagnostics must be logged privately.'
    );

    require_once $root . '/admin/models/match.php';

    $query = new class {
        public array $conditions = [];
        public array $bindings = [];

        public function select(string $columns): self { return $this; }
        public function from(string $table): self { return $this; }

        public function where(string $condition): self
        {
            $this->conditions[] = $condition;
            return $this;
        }

        public function bind(string $key, &$value, string $type): self
        {
            $this->bindings[$key] = [$value, $type];
            return $this;
        }
    };
    $db = new class($query) {
        public int $queries = 0;
        public int $limit = 0;

        public function __construct(private object $query) {}
        public function getQuery(bool $new = true): object { return $this->query; }
        public function quoteName(string $identifier): string { return '"' . $identifier . '"'; }

        public function setQuery(object $query, int $offset = 0, int $limit = 0): void
        {
            ++$this->queries;
            $this->limit = $limit;
        }

        public function loadObject(): object { return (object) ['id' => 42]; }
    };
    $adminModel = (new \ReflectionClass(\sportsmanagementModelMatch::class))->newInstanceWithoutConstructor();
    $adminModel->jsmdb = $db;

    $assert(
        $adminModel->getSeasonPersonAssignment('1 OR 1=1', 7, 0) === null && $db->queries === 0,
        'Malformed season-person identifiers must be rejected before SQL execution.'
    );
    $assert(
        ($adminModel->getSeasonPersonAssignment(12, 7, 0)->id ?? null) === 42 && $db->queries === 1,
        'Valid season-person relationship requests must still work.'
    );
    $assert(
        $query->bindings === [
            ':assignmentPersonId' => [12, \Joomla\Database\ParameterType::INTEGER],
            ':assignmentSeasonId' => [7, \Joomla\Database\ParameterType::INTEGER],
            ':assignmentPersonType' => [0, \Joomla\Database\ParameterType::INTEGER],
        ] && $db->limit === 1 && count($query->conditions) === 3
          && !str_contains(implode(' ', $query->conditions), '12'),
        'All three season-person selectors must use typed SQL bindings.'
    );

    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarDate.php';

    $calendarClass = \Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarDate::class;

    foreach ([-1, '-1', -60, '-60', 0, '0', 1, '0001'] as $timestamp) {
        $date = $calendarClass::fromValue($timestamp, 'Europe/Berlin');
        $assert(
            $date instanceof \Joomla\CMS\Date\Date && $date->getTimestamp() === (int) $timestamp,
            'Calendar timestamps must preserve the signed Unix instant.'
        );
    }

    foreach ([[], true, '-99999999999999999999999999', '99999999999999999999999'] as $badDate) {
        $assert(
            $calendarClass::fromValue($badDate, 'UTC') === null,
            'The calendar must reject invalid or overflowing timestamp input.'
        );
    }

    echo "Joomla 5/6 match-report privacy, admin SQL bindings and calendar dates: OK\n";
}
