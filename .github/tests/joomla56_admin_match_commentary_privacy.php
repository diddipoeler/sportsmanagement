<?php
/**
 * Test Joomla 5/6 match commentary read-only query and private error handling.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public static ?object $connection = null;
        public array $errors = [];

        /**
         * Supply the configured Joomla database stand-in to the model.
         *
         * @return object Fake database used by this regression.
         */
        protected function getDatabase(): object
        {
            return self::$connection;
        }

        /**
         * Record errors that the administrator MVC view could display.
         *
         * @param string $error Public-facing error message.
         * @return void
         */
        public function setError(string $error): void
        {
            $this->errors[] = $error;
        }
    }
}

namespace Joomla\Database {
    class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Return a generic Joomla translation to distinguish it from SQL.
         *
         * @param string $key Translation key.
         * @return string Localised generic message stand-in.
         */
        public static function _(string $key): string
        {
            return $key === 'JERROR_AN_ERROR_HAS_OCCURRED'
                ? 'A generic database error occurred.'
                : $key;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Record protected error details through the Joomla log stub.
         *
         * @param string $message Internal exception details.
         * @param int $level Log severity.
         * @param string $category Joomla logging category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/MatchcommentaryModel.php';

    class CommentaryQuery
    {
        public array $bound = [];

        /**
         * Supply a fluent SELECT expression for the mock database builder.
         *
         * @param string $columns Selected SQL columns.
         * @return self Current query instance.
         */
        public function select(string $columns): self { return $this; }

        /**
         * Record a query table without requiring a live Joomla database.
         *
         * @param string $table Table and alias identifier.
         * @return self Current query instance.
         */
        public function from(string $table): self { return $this; }

        /**
         * Accept a generated WHERE expression for the scoped read query.
         *
         * @param string $condition SQL condition.
         * @return self Current query instance.
         */
        public function where(string $condition): self { return $this; }

        /**
         * Record the bound match identifier instead of interpolating input.
         *
         * @param string $name Placeholder name.
         * @param mixed $value Selected value.
         * @param mixed $type Joomla SQL parameter type.
         * @return self Current query instance.
         */
        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->bound[$name] = $value;
            return $this;
        }

        /**
         * Accept the match commentary chronological ordering expression.
         *
         * @param string $expression Sort expression.
         * @return self Current query instance.
         */
        public function order(string $expression): self { return $this; }
    }

    class CommentaryDatabase
    {
        public int $created = 0;
        public bool $fail = false;
        public array $rows = [];
        public ?CommentaryQuery $query = null;

        /**
         * Create and count database query objects for scope assertions.
         *
         * @return CommentaryQuery Mutable query stub.
         */
        public function createQuery(): CommentaryQuery
        {
            ++$this->created;
            return new CommentaryQuery();
        }

        /**
         * Retain Joomla SQL identifier usage in the fake query.
         *
         * @param string $name Database identifier.
         * @param string $alias Optional query alias.
         * @return string Identifier text for the fake query builder.
         */
        public function quoteName(string $name, string $alias = ''): string
        {
            return $name;
        }

        /**
         * Verify the prepared query and simulate optional database failure.
         *
         * @param CommentaryQuery $query Query produced by the real model.
         * @return void
         * @throws \RuntimeException When failure mode is enabled.
         */
        public function setQuery(CommentaryQuery $query): void
        {
            $this->query = $query;

            if ($this->fail) {
                throw new \RuntimeException('SQLSTATE password=private connection=/srv/secret');
            }
        }

        /**
         * Return preconfigured commentary records for the successful path.
         *
         * @return array<int, object> Fake chronological commentary entries.
         */
        public function loadObjectList(): array
        {
            return $this->rows;
        }
    }

    $db = new CommentaryDatabase();
    \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::$connection = $db;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\MatchcommentaryModel();

    if ($model->getMatchCommentary(0) !== [] || $db->created !== 0) {
        throw new \RuntimeException('Invalid match ID performed a commentary database query.');
    }

    $record = (object) ['id' => 13, 'match_id' => 8];
    $db->rows = [$record];
    if ($model->getMatchCommentary(8) !== [$record]
        || $db->query?->bound[':matchId'] !== 8) {
        throw new \RuntimeException('Match commentary read did not bind the selected ID.');
    }

    $db->fail = true;
    if ($model->getMatchCommentary(8) !== []
        || $model->errors !== ['A generic database error occurred.']) {
        throw new \RuntimeException('Private database error was exposed by the administrator model.');
    }

    $logs = \Joomla\CMS\Log\Log::$entries;
    if (count($logs) !== 1
        || $logs[0][1] !== \Joomla\CMS\Log\Log::ERROR
        || $logs[0][2] !== 'com_sportsmanagement'
        || !str_contains($logs[0][0], 'password=private')) {
        throw new \RuntimeException('Database failure was not logged privately.');
    }

    echo "Joomla 5/6 administrator commentary read and private errors passed.\n";
}
