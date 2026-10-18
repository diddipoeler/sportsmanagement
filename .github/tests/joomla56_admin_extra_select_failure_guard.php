<?php
/**
 * Joomla 5/6 administrator select extra-field query failure regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    trait SportsManagementDatabaseTrait
    {
        protected function getSportsManagementDatabase(): object
        {
            return \TestAdminExtraFieldDatabase::$connection;
        }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        public static function _(string $task, string $value, string $label): object
        {
            if ($task !== 'select.option') {
                throw new \RuntimeException('Unexpected HTML option task.');
            }

            return (object) ['value' => $value, 'text' => $label];
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $errors = [];
        public static function add(string $message, int $priority, string $category): void
        {
            self::$errors[] = [$message, $priority, $category];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/ExtraSelectOptionsHelper.php';

    final class TestAdminExtraFieldQuery
    {
        public function select(mixed $v): self { return $this; }
        public function from(mixed $v): self { return $this; }
        public function where(mixed $v): self { return $this; }
    }

    final class TestAdminExtraFieldDatabase
    {
        public static object $connection;
        public int $queries = 0;
        public bool $failSetup = false;
        public bool $failRead = false;
        public ?object $result = null;

        public function createQuery(): TestAdminExtraFieldQuery
        {
            if ($this->failSetup) {
                throw new \RuntimeException('SQLSTATE fake-database-password');
            }

            return new TestAdminExtraFieldQuery();
        }

        public function quoteName(string $name): string { return $name; }
        public function quote(string $value): string { return "'" . $value . "'"; }
        public function setQuery(object $query): void { ++$this->queries; }

        public function loadObject(): ?object
        {
            if ($this->failRead) {
                throw new \RuntimeException('Private connection details');
            }

            return $this->result;
        }
    }

    $helper = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraSelectOptionsHelper();
    $db = new TestAdminExtraFieldDatabase();
    TestAdminExtraFieldDatabase::$connection = $db;

    if ($helper->getOptions('', 'location') !== []
        || $helper->getOptions('club', '') !== []
        || $db->queries !== 0) {
        throw new \RuntimeException('Empty extra field selection queried the database.');
    }

    $db->result = (object) [
        'select_columns' => 'yes,no',
        'select_values' => 'Ja,Nein',
    ];

    $values = $helper->getOptions('club', 'field');

    if (count($values) !== 2
        || $values[0]->value !== 'yes' || $values[0]->text !== 'Ja'
        || $values[1]->value !== 'no' || $values[1]->text !== 'Nein') {
        throw new \RuntimeException('Valid administrator extra-field select options were altered.');
    }

    $db->failSetup = true;

    if ($helper->getOptions('club', 'field') !== []) {
        throw new \RuntimeException('Extra-select query construction failure was not caught.');
    }

    $db->failSetup = false;
    $db->failRead = true;

    if ($helper->getOptions('club', 'field') !== []) {
        throw new \RuntimeException('Extra-select database read failure was not caught.');
    }

    $errors = \Joomla\CMS\Log\Log::$errors;

    if (count($errors) !== 2
        || $errors[0][1] !== \Joomla\CMS\Log\Log::ERROR
        || $errors[0][2] !== 'com_sportsmanagement'
        || !str_contains($errors[0][0], 'SQLSTATE fake-database-password')
        || !str_contains($errors[1][0], 'Private connection details')) {
        throw new \RuntimeException('Administrator extra-select failures were not logged.');
    }

    echo "Administrator extra-select query failure handling passed.\n";
}
