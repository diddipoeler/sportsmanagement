<?php
/**
 * Joomla 5/6 regression for scoped roster relations, private errors and birthday filters.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application { interface CMSApplicationInterface {} }
namespace Joomla\CMS\MVC\Model {
    class AdminModel {
        public string $error = '';
        public function setError(string $error): void { $this->error = $error; }
    }
}
namespace Joomla\CMS\Log {
    class Log {
        public const ERROR = 'error';
        public const WARNING = 'warning';
        public static array $messages = [];
        public static function add(string $message, mixed $level, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Date {
    class Date {
        public function __construct(mixed ...$args) {}
        public function toSql(): string { return '2026-10-14 00:00:00'; }
    }
}
namespace Joomla\CMS\Uri {
    class Uri { public static function root(): string { return 'https://jsm.test/'; } }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $data = []) {}
        public function get(string $key, mixed $default = null): mixed {
            return $this->data[$key] ?? $default;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementSiteApplicationResolver {
        public static ?\Joomla\CMS\Application\CMSApplicationInterface $app = null;
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface {
            return self::$app;
        }
    }
    class SportsManagementDatabaseResolver {
        public static int $calls = 0;
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback, int $selector
        ): \Joomla\Database\DatabaseInterface {
            ++self::$calls;
            return $fallback;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel extends \Joomla\CMS\MVC\Model\AdminModel {
        public static mixed $db = null;
        public static array $post = [];
        public static int $parentDeletes = 0;
        protected function getDatabase(): mixed { return self::$db; }
        protected function administratorApplication(): object {
            return new class {
                public function getIdentity(): object { return (object) ['id' => 1]; }
                public function getInput(): object {
                    return new class {
                        public object $post;
                        public function __construct() {
                            $this->post = new class {
                                public function getArray(): array {
                                    return \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::$post;
                                }
                            };
                        }
                    };
                }
            };
        }
        public function delete(&$ids) {
            ++self::$parentDeletes;
            return true;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/TeamplayerModel.php';
    require_once dirname(__DIR__, 2) . '/site/src/Model/EditmatchModel.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';

    $database = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        public int $writes = 0;
        public int $starts = 0;
        public int $rollbacks = 0;
        public int $deletes = 0;
        public bool $throwsOnInsert = true;
        public int $ownerPersonId = 99;
        public function createQuery(): object {
            return new class {
                public array $bindings = [];
                public function select(string|array $fields): self { return $this; }
                public function from(string $table): self { return $this; }
                public function where(string $condition): self { return $this; }
                public function whereIn(string $column, array $ids, mixed $type): self { return $this; }
                public function bind(string $key, mixed $value, mixed $type): self {
                    $this->bindings[$key] = $value;
                    return $this;
                }
                public function delete(string $table): self { return $this; }
            };
        }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(object $query, int $offset = 0, int $limit = 0): self {
            ++$this->queries;
            return $this;
        }
        public function loadResult(): int { return $this->ownerPersonId; }
        public function loadColumn(): array { return []; }
        public function transactionStart(): void { ++$this->starts; }
        public function transactionRollback(): void { ++$this->rollbacks; }
        public function transactionCommit(): void {}
        public function updateObject(string $table, object $row, string $key): void {
            ++$this->writes;
        }
        public function insertObject(string $table, object $record): void {
            ++$this->writes;
            if ($this->throwsOnInsert) {
                throw new \RuntimeException('PRIVATE SQL PASSWORD');
            }
        }
    };
    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public array $messages = [];
        public function getIdentity(): object { return (object) ['id' => 1]; }
        public function enqueueMessage(string $text, string $kind = 'notice'): void {
            $this->messages[] = [$text, $kind];
        }
    };

    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $base::$db = $database;
    $adminName = \Diddipoeler\Component\SportsManagement\Administrator\Model\TeamplayerModel::class;
    $admin = (new \ReflectionClass($adminName))->newInstanceWithoutConstructor();
    $belongs = new \ReflectionMethod($admin, 'assignmentBelongsToPerson');

    if ($belongs->invoke($admin, $database, 12, 7) !== false) {
        throw new \RuntimeException('Forged roster/person association accepted.');
    }
    $database->ownerPersonId = 7;
    if ($belongs->invoke($admin, $database, 12, 7) !== true) {
        throw new \RuntimeException('Matching roster/person relation not recognized.');
    }
    $database->ownerPersonId = 99;
    $queriesBefore = $database->queries;
    if ($belongs->invoke($admin, $database, 0, 7) !== false
        || $database->queries !== $queriesBefore) {
        throw new \RuntimeException('Empty roster assignment reached the database.');
    }

    if ($admin->set_state(['7'], [7 => '12'], 1, 0) !== false
        || $database->writes !== 0 || $database->rollbacks !== 1) {
        throw new \RuntimeException('Roster state changed another person record.');
    }

    $base::$post = ['cid' => [7], 'tpid' => [7 => 12], 'pid' => 0, 'persontype' => 1];
    if ($admin->saveshort() !== false
        || $database->writes !== 0 || $database->rollbacks !== 2) {
        throw new \RuntimeException('Roster short edit changed another person record.');
    }

    $selected = [7];
    if ($admin->delete($selected) !== false
        || $database->deletes !== 0
        || $base::$parentDeletes !== 0
        || $database->rollbacks !== 3) {
        throw new \RuntimeException('Roster delete removed an unrelated assignment.');
    }

    $siteResolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver::class;
    $siteResolver::$app = $app;
    $siteName = \Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel::class;
    $site = (new \ReflectionClass($siteName))->newInstanceWithoutConstructor();
    $failure = new \ReflectionMethod($site, 'reportWriteFailure');
    if ($failure->invoke($site, new \RuntimeException('PRIVATE SQL PASSWORD')) !== false
        || $app->messages[0][0] !== 'JERROR_AN_ERROR_HAS_OCCURRED'
        || !str_contains(end(\Joomla\CMS\Log\Log::$messages), 'PRIVATE SQL PASSWORD')) {
        throw new \RuntimeException('Match database failure leaked private details.');
    }

    $birthdayName = \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper::class;
    $birthday = new $birthdayName();
    $empty = ['persons' => [], 'mode' => 'B', 'pictureServer' => 'https://jsm.test/'];
    $component = new \Joomla\Registry\Registry();
    $resolver = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver::class;
    foreach ([
        ['p' => '7suffix', 'mode' => 'B'],
        ['p' => ['broken'], 'mode' => 'B'],
        ['p' => [7], 'teams' => '12garbage', 'mode' => 'B'],
        ['p' => ['7'], 'use_fav' => 1, 'teams' => [], 'mode' => 'B', 'no_database' => false],
    ] as $settings) {
        $favorite = (bool) ($settings['use_fav'] ?? false);
        $before = $resolver::$calls;
        $result = $birthday->getData(
            new \Joomla\Registry\Registry($settings), $component, $app, $database
        );
        if ($result !== $empty) {
            throw new \RuntimeException('Bad birthday filter returned other projects or teams.');
        }
        if (!$favorite && $before !== $resolver::$calls) {
            throw new \RuntimeException('Invalid birthday filter accessed database.');
        }
    }

    echo "Joomla 5/6 scoped roster, private match errors and birthday filters OK\n";
}
