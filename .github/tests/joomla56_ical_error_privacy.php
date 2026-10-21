<?php
/**
 * Joomla 5/6 iCalendar privacy and graceful database errors.
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

namespace Joomla\CMS\Language {
    final class Text {
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public static array $entries = [];
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 1; }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel {
        protected int $projectId = 0;
        protected int $divisionId = 0;
        public object $testApp;

        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null)
        {
            $this->testApp = new class {
                public array $messages = [];
                public function getInput(): object
                {
                    return new class {
                        public function getInt(string $key, int $default = 0): int { return $default; }
                    };
                }
                public function enqueueMessage(string $message, string $type): void
                {
                    $this->messages[] = [$message, $type];
                }
            };
        }

        protected function siteApplication(): object { return $this->testApp; }

        protected function getDatabase(): object
        {
            return new class {
                public function createQuery(): object
                {
                    throw new \RuntimeException('SQLSTATE password=private-file /etc/database.conf');
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/IcalModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\IcalModel();

    if ($model->getResultsPlan(0) !== []) {
        throw new \RuntimeException('Empty project should not query iCalendar database.');
    }

    if ($model->getResultsPlan(5) !== false) {
        throw new \RuntimeException('iCalendar database failures must retain the legacy false result.');
    }

    $messages = $model->testApp->messages;
    $logs = \Joomla\CMS\Log\Log::$entries;

    if ($messages !== [['JERROR_AN_ERROR_HAS_OCCURRED', 'error']]
        || count($logs) !== 1
        || !str_contains($logs[0][0], 'password=private-file')
        || $logs[0][1] !== \Joomla\CMS\Log\Log::ERROR
        || $logs[0][2] !== 'jsmerror') {
        throw new \RuntimeException('Calendar SQL details were lost from logs or exposed in the UI.');
    }

    echo "iCalendar public error privacy regressions OK\n";
}
