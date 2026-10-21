<?php
/**
 * UEFA ranking site model database error privacy regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Joomla\Database {
    final class ParameterType { public const STRING = 'string'; }
}

namespace Joomla\CMS\Language {
    final class Text {
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public static array $messages = [];
        public static function add(string $message, int $level, string $category): void
        {
            self::$messages[] = [$message, $level, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel {
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        protected function siteApplication(): object { return $GLOBALS['uefaTestApp']; }
        protected function getDatabase(): object { return $GLOBALS['uefaTestDatabase']; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/UefawertungModel.php';

    final class UefaTestQuery {
        public function select(mixed $value): self { return $this; }
        public function from(string $value): self { return $this; }
        public function where(string $value): self { return $this; }
        public function whereIn(string $col, array $ids, mixed $type): self { return $this; }
        public function group(string $value): self { return $this; }
        public function order(string $value): self { return $this; }
        public function bind(string $key, mixed $value, mixed $type): self { return $this; }
    }

    $GLOBALS['uefaTestDatabase'] = new class {
        public function createQuery(): UefaTestQuery { return new UefaTestQuery(); }
        public function quoteName(string $name, ?string $alias = null): string { return $name; }
        public function setQuery(object $query, int $start = 0, int $limit = 0): void
        {
            throw new \RuntimeException('SQLSTATE secret-db-password /private/configuration.php');
        }
    };

    $GLOBALS['uefaTestApp'] = new class {
        public array $messages = [];
        public function getInput(): object
        {
            return new class {
                public object $post;
                public function __construct()
                {
                    $this->post = new class {
                        public function getString(string $key, string $fallback = ''): string
                        {
                            return $fallback;
                        }
                    };
                }
                public function getString(string $key, string $fallback = ''): string
                {
                    return $fallback;
                }
            };
        }
        public function enqueueMessage(string $message, string $type): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\UefawertungModel();

    foreach ([
        [$model->getcoefficientyears(), []],
        [$model->getSeasonNames('2025'), []],
        [$model->getcoefficientyearspoints('2025'), []],
    ] as [$actual, $expected]) {
        if ($actual !== $expected) {
            throw new \RuntimeException('UEFA query failed without returning empty data.');
        }
    }

    $messages = $GLOBALS['uefaTestApp']->messages;
    $logs = \Joomla\CMS\Log\Log::$messages;

    if (count($messages) !== 3 || count($logs) !== 3) {
        throw new \RuntimeException('UEFA database exception logging or display regressed.');
    }

    foreach ($messages as $item) {
        if ($item !== ['JERROR_AN_ERROR_HAS_OCCURRED', 'error']) {
            throw new \RuntimeException('UEFA SQL details were exposed to site visitors.');
        }
    }

    foreach ($logs as [$message, $level, $category]) {
        if (!str_contains($message, 'secret-db-password')
            || $level !== \Joomla\CMS\Log\Log::ERROR || $category !== 'jsmerror') {
            throw new \RuntimeException('UEFA internal exceptions were not safely logged.');
        }
    }

    echo "UEFA coefficient query error privacy OK\n";
}
