<?php
/**
 * Standalone regression for Joomla 5/6 results and quick-add SQL error privacy.
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
        /**
         * Return a predictable translated error without exposing exception data.
         *
         * @param string $key Joomla translation key.
         * @return string Public error text.
         */
        public static function _(string $key): string
        {
            return $key === 'JERROR_AN_ERROR_HAS_OCCURRED' ? 'An error occurred.' : $key;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $messages = [];

        /**
         * Capture diagnostic messages that should remain server-side.
         *
         * @param string $message Private diagnostic text.
         * @param int $level Log severity.
         * @param string $category Log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$messages[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        public object $application;

        /**
         * Accept the optional MVC dependencies expected by ResultsDataModel.
         *
         * @param array $config Model configuration.
         * @param mixed $factory Optional Joomla MVC factory.
         */
        public function __construct($config = [], $factory = null)
        {
        }

        /**
         * Resolve the test site application used to capture public messages.
         *
         * @return object Application with enqueueMessage support.
         */
        protected function siteApplication(): object
        {
            return $this->application;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel
    {
        public ?\Joomla\Database\DatabaseInterface $database = null;
        public array $errors = [];

        /**
         * Supply the failing database service to the quick-add model.
         *
         * @return \Joomla\Database\DatabaseInterface Database double.
         */
        public function getDatabase(): \Joomla\Database\DatabaseInterface
        {
            return $this->database;
        }

        /**
         * Record the Joomla model error visible to the administrator.
         *
         * @param string $message Public error text.
         * @return void
         */
        public function setError(string $message): void
        {
            $this->errors[] = $message;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    require_once __DIR__ . '/../../site/src/Model/ResultsDataModel.php';
    require_once __DIR__ . '/../../admin/src/Model/QuickaddModel.php';

    $secret = 'SQL connection password=private-test-token';
    $siteApplication = new class {
        public array $messages = [];

        /**
         * Capture what would be displayed to a frontend visitor.
         *
         * @param string $message Public Joomla message.
         * @param string $type Message category.
         * @return void
         */
        public function enqueueMessage(string $message, string $type): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    $siteModel = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel();
    $siteModel->application = $siteApplication;
    $siteReporter = new \ReflectionMethod($siteModel, 'reportDatabaseError');
    $siteReporter->invoke($siteModel, new \RuntimeException($secret));

    if ($siteApplication->messages !== [['An error occurred.', 'error']]) {
        throw new \RuntimeException('Site results displayed a non-generic database error.');
    }

    $database = new class($secret) implements \Joomla\Database\DatabaseInterface {
        /**
         * Initialise the failing database double with a private diagnostic.
         *
         * @param string $message SQL error message to keep private.
         */
        public function __construct(private string $message)
        {
        }

        /**
         * Simulate the database failing before the quick-add page is loaded.
         *
         * @param mixed $query Query under test.
         * @return void
         */
        public function setQuery($query): void
        {
            throw new \RuntimeException($this->message);
        }

        /**
         * Simulate a failing quick-add person insert.
         *
         * @param string $table Target table.
         * @param object $record Record being inserted.
         * @return void
         */
        public function insertObject(string $table, object $record): void
        {
            throw new \RuntimeException($this->message);
        }
    };

    $query = new class {
        /**
         * Support query cloning/count conversion in the quick-add reader.
         *
         * @param string $part Query clause to clear.
         * @return self This query double.
         */
        public function clear(string $part): self
        {
            return $this;
        }

        /**
         * Support selecting COUNT(*) for the quick-add total.
         *
         * @param string $expression Query select expression.
         * @return self This query double.
         */
        public function select(string $expression): self
        {
            return $this;
        }
    };

    $quickAddModel = new \Diddipoeler\Component\SportsManagement\Administrator\Model\QuickaddModel();
    $quickAddModel->database = $database;
    $quickReader = new \ReflectionMethod($quickAddModel, 'loadQuickRows');
    $rows = $quickReader->invoke($quickAddModel, $database, $query, false);

    if ($rows !== [] || $quickAddModel->getTotal() !== 0 || $quickAddModel->errors !== ['An error occurred.']) {
        throw new \RuntimeException('Quick-add search disclosed details or returned stale pagination state.');
    }

    $created = $quickAddModel->addPlayer(0, 0, 'Test Person');

    if ($created !== false || $quickAddModel->errors !== ['An error occurred.', 'An error occurred.']) {
        throw new \RuntimeException('Quick-add person insert disclosed SQL exception details.');
    }

    if (\count(\Joomla\CMS\Log\Log::$messages) !== 3) {
        throw new \RuntimeException('Expected three private database error log entries.');
    }

    foreach (\Joomla\CMS\Log\Log::$messages as [$message, $level, $category]) {
        if (!str_contains($message, $secret)
            || $level !== \Joomla\CMS\Log\Log::ERROR
            || $category !== 'com_sportsmanagement'
        ) {
            throw new \RuntimeException('Private SQL diagnostics were not logged correctly.');
        }
    }

    echo "Joomla 5/6 results and quick-add error privacy: OK\n";
}
