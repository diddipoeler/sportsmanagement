<?php
/**
 * Joomla 5/6 iCalendar query filter, project scope and portable slugs test.
 *
 * Exercises the real native calendar model with in-memory Joomla doubles so
 * ID validation, join constraints and PHP-side presentation can be verified.
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
namespace Joomla\CMS\Language {
    final class Text {
        /** @return string Untranslated Joomla error key. */
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public static array $entries = [];
        /**
         * Store sanitized calendar error events for privacy assertions.
         *
         * @param string $message Error message.
         * @param int $level Joomla log severity.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType {
        public const INTEGER = 1;
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel {
        protected int $projectId = 0;
        protected int $divisionId = 0;
        public static object $database;
        public object $application;

        /**
         * Configure a fake site application for the calendar constructor.
         *
         * @param array $config Test configuration.
         * @param \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null $factory Unused MVC factory.
         * @return void
         */
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
        {
            $this->application = new class {
                /**
                 * Supply bounded Joomla request parameters.
                 *
                 * @return object Fake Joomla input.
                 */
                public function getInput(): object
                {
                    return new class {
                        /**
                         * Return a defaulted Joomla integer request value.
                         *
                         * @param string $name Requested input name.
                         * @param int $default Default value.
                         * @return int Default fixture value.
                         */
                        public function getInt(string $name, int $default = 0): int { return $default; }
                    };
                }

                /**
                 * Convert query failures into captured frontend notifications.
                 *
                 * @param string $message Translated message.
                 * @param string $type Severity.
                 * @return void
                 */
                public function enqueueMessage(string $message, string $type): void {}
            };
        }

        /** @return object Active fake calendar database. */
        protected function getDatabase(): object { return self::$database; }

        /** @return object Fake Joomla site application. */
        protected function siteApplication(): object { return $this->application; }
    }
}
namespace {
    \define('_JEXEC', 1);
    require_once \dirname(__DIR__, 2) . '/site/src/Model/IcalModel.php';

    $database = new class implements \Joomla\Database\DatabaseInterface {
        public ?object $lastQuery = null;
        public int $queries = 0;
        public array $fixtures = [];

        /**
         * Return a fluent fake Joomla query collecting clauses and bindings.
         *
         * @return object Query fixture.
         */
        public function createQuery(): object
        {
            return new class {
                public array $selections = [];
                public array $joins = [];
                public array $conditions = [];
                public array $orders = [];
                public array $bindings = [];

                /** @return self Query with selected expression(s). */
                public function select(array|string $fields): self {
                    $this->selections = array_merge($this->selections, (array) $fields);
                    return $this;
                }
                /** @return self Query with source table. */
                public function from(string $table): self { return $this; }
                /**
                 * Record the join's SQL expression for project isolation.
                 *
                 * @param string $kind Join type.
                 * @param string $sql Join predicate.
                 * @return self Fluent query.
                 */
                public function join(string $kind, string $sql): self {
                    $this->joins[] = $sql;
                    return $this;
                }
                /** @return self Query with bound condition. */
                public function where(string $sql): self {
                    $this->conditions[] = $sql;
                    return $this;
                }
                /** @return self Query with sort order. */
                public function order(string $sql): self {
                    $this->orders[] = $sql;
                    return $this;
                }
                /**
                 * Record named bindings, supporting Joomla multi-bind arrays.
                 *
                 * @param string|array $name Name or names of placeholders.
                 * @param mixed $value Bound numeric value.
                 * @param mixed $type ParameterType constant.
                 * @return self Fluent query.
                 */
                public function bind(string|array $name, mixed $value, mixed $type): self {
                    foreach ((array) $name as $key) {
                        $this->bindings[$key] = [$value, $type];
                    }
                    return $this;
                }
            };
        }

        /**
         * Return a stable SQL identifier for structural query checks.
         *
         * @param string $name Database identifier.
         * @param string|null $alias Optional selected column alias.
         * @return string Identifier with optional alias.
         */
        public function quoteName(string $name, ?string $alias = null): string
        {
            return $name . ($alias === null ? '' : ' AS ' . $alias);
        }

        /**
         * Capture the actual query submitted by the tested calendar model.
         *
         * @param object $query Fluent query fixture.
         * @return void
         */
        public function setQuery(object $query): void {
            $this->lastQuery = $query;
            ++$this->queries;
        }

        /**
         * Return synthetic published matches keyed by their original ID.
         *
         * @param string $key Row ID column.
         * @return array Calendar matches.
         */
        public function loadObjectList(string $key): array { return $this->fixtures; }
    };

    \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel::$database = $database;
    $calendar = new \Diddipoeler\Component\SportsManagement\Site\Model\IcalModel();
    $database->fixtures = [5 => (object) [
        'id' => 5, 'project_id' => 8, 'project_alias' => 'cup',
        'round_id' => 3, 'round_alias' => 'final',
        'playground_id' => 9, 'playground_alias' => 'stadium',
        'home_team_alias' => 'hosts', 'away_team_alias' => 'visitors',
        'time_present' => '18:35:00',
    ]];

    $matches = $calendar->getResultsPlan('008:cup', '15:team', '04:division', '009:stadium', 'DESC');
    if (($matches[5]->slug ?? null) !== '5:hosts_visitors'
        || ($matches[5]->project_slug ?? null) !== '8:cup'
        || ($matches[5]->round_slug ?? null) !== '3:final'
        || ($matches[5]->playground_slug ?? null) !== '9:stadium'
        || ($matches[5]->time_present ?? null) !== '18:35') {
        throw new \RuntimeException('iCalendar PHP presentation lost legacy slugs or time formatting.');
    }

    $query = $database->lastQuery;
    $expected = [
        ':projectId' => 8, ':homeTeamId' => 15, ':awayTeamId' => 15,
        ':calendarDivisionId' => 4, ':calendarPlaygroundId' => 9,
    ];
    foreach ($expected as $key => $value) {
        if (($query->bindings[$key] ?? null) !== [$value, \Joomla\Database\ParameterType::INTEGER]) {
            throw new \RuntimeException('iCalendar filter was not bound: ' . $key);
        }
    }
    $joinText = implode("\n", $query->joins);
    foreach ([
        'pt1.project_id = p.id', 'pt2.project_id = p.id',
        'st1.season_id = p.season_id', 'st2.season_id = p.season_id',
    ] as $required) {
        if (!str_contains($joinText, $required)) {
            throw new \RuntimeException('iCalendar lost its project-season join constraint: ' . $required);
        }
    }
    if (str_contains(implode(' ', $query->selections), 'CONCAT_WS')
        || str_contains(implode(' ', $query->selections), 'DATE_FORMAT')
        || $query->orders !== ['m.match_date DESC', 'm.match_number DESC']) {
        throw new \RuntimeException('iCalendar still relies on MySQL formatting or lost DESC ordering.');
    }

    $before = $database->queries;
    foreach ([
        ['8', 0, '4bad', 0, 0],
        ['8', 0, 0, '-1', 0],
        ['8', 0, 0, 0, 2],
        ['8', 0, 0, 0, '1garbage'],
    ] as [$project, $team, $division, $venue, $selector]) {
        if ($calendar->getResultsPlan($project, $team, $division, $venue, 'ASC', $selector) !== []) {
            throw new \RuntimeException('Malformed calendar filter was not rejected.');
        }
    }
    if ($database->queries !== $before) {
        throw new \RuntimeException('Invalid calendar filter executed an unintended query.');
    }

    echo "Joomla 5/6 iCalendar scoped portable query contract OK\n";
}
