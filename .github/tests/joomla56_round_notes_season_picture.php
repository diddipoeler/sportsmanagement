<?php
/**
 * Regression checks for round notes, season pictures and the 4.24.03 schema.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS\Filter {
    final class OutputFilter
    {
        /** @return string Sanitised test alias. */
        public static function stringURLSafe(string $value): string
        {
            return strtolower($value);
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class SportsManagementDateHelper
    {
        /** @return string|null Normalised test date. */
        public static function toSqlDate(string $value): ?string
        {
            return $value === '' ? null : $value;
        }

        /** @return int Deterministic timestamp for the test. */
        public static function getTimestamp(string $date): int
        {
            return 1234;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class SportsManagementTable
    {
        public array $bound = [];
        public array $errors = [];

        /** @return void Initialise the lightweight table stub. */
        public function __construct(string $table, string $key, \Joomla\Database\DatabaseInterface $db)
        {
        }

        /** @return bool Record values given to the inherited binder. */
        public function bind($src, $ignore = [])
        {
            $this->bound = is_array($src) ? $src : get_object_vars($src);

            return true;
        }

        /** @return void Record validation failures. */
        public function setError(string $message): void
        {
            $this->errors[] = $message;
        }

        /** @return bool Simulate the successful Joomla base-table check. */
        public function check()
        {
            return true;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/admin/src/Table/RoundTable.php';
    require_once $root . '/admin/src/Table/SeasonTable.php';

    $database = new class implements \Joomla\Database\DatabaseInterface {};
    $round = new \Diddipoeler\Component\SportsManagement\Administrator\Table\RoundTable($database);
    $season = new \Diddipoeler\Component\SportsManagement\Administrator\Table\SeasonTable($database);

    $expect = static function (bool $condition, string $message): void {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    };

    $expect($round->bind(['notes' => 'A short note']) && $round->bound['notes'] === 'A short note',
        'Round notes must be persisted unchanged.');
    $expect($round->bind(['notes' => '']) && $round->bound['notes'] === null,
        'Empty round notes must be stored as NULL.');
    $expect(!$round->bind(['notes' => ['not', 'text']]),
        'Structured round editor input must be rejected.');
    $expect($round->bind(['notes' => str_repeat('x', 65535)]),
        'SQL TEXT maximum-size round notes must be accepted.');
    $expect(!$round->bind(['notes' => str_repeat('x', 65536)]),
        'Oversized round notes must be rejected.');
    $expect(!$round->bind(['round_date_first' => ['invalid']]),
        'Malformed round calendar values must be rejected.');
    $expect($round->bind(['round_date_first' => '']) && $round->bound['round_date_first'] === null,
        'Empty round dates must remain nullable.');

    $expect($season->bind(['picture' => 'images/season.jpg'])
        && $season->bound['picture'] === 'images/season.jpg',
        'Valid season pictures must be persisted.');
    $expect($season->bind(['picture' => '']) && $season->bound['picture'] === null,
        'Empty season pictures must be stored as NULL.');
    $expect(!$season->bind(['picture' => []]),
        'Structured season picture values must be rejected.');
    $expect($season->bind(['picture' => str_repeat('x', 250)]),
        'Picture paths at the VARCHAR(250) limit must be accepted.');
    $expect(!$season->bind(['picture' => str_repeat('x', 251)]),
        'Oversized season picture paths must be rejected.');

    $sql = file_get_contents($root . '/admin/sql/updates/mysql/4.24.03.sql');
    $expect(is_string($sql)
        && str_contains($sql, 'ADD COLUMN `notes` TEXT NULL DEFAULT NULL')
        && str_contains($sql, 'ADD COLUMN `picture_blob` MEDIUMBLOB NULL DEFAULT NULL')
        && str_contains($sql, 'ADD COLUMN `picture` VARCHAR(250) NULL DEFAULT NULL'),
        'The upgrade must contain all three nullable columns.');

    foreach (['admin/forms', 'admin/models/forms'] as $formFolder) {
        $roundXml = file_get_contents($root . '/' . $formFolder . '/round.xml');
        $seasonXml = file_get_contents($root . '/' . $formFolder . '/season.xml');
        $expect(is_string($roundXml)
            && str_contains($roundXml, 'name="notes" type="editor"')
            && str_contains($roundXml, 'filter="safehtml"'),
            'The round notes editor must exist in ' . $formFolder);
        $expect(is_string($seasonXml)
            && str_contains($seasonXml, 'name="picture" type="media"')
            && str_contains($seasonXml, 'maxlength="250"'),
            'The season picture media field must exist in ' . $formFolder);
    }

    echo "Round notes and season picture schema/binding checks OK\n";
}
