<?php
/**
 * Joomla 5/6 direct frontend match-write permission regression test.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Date {
    class Date
    {
        public function __construct(string $date, \DateTimeZone $zone) {}
        public function toSql(): string { return '2026-10-11 12:00:00'; }
    }
}
namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 400;
        public static function add(string $message, int $level, string $category): void {}
    }
}
namespace Joomla\CMS\Uri {
    class Uri
    {
        public static function getInstance(string $url = ''): self { return new self(); }
        public function getHost(): string { return 'localhost'; }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
}
namespace Joomla\CMS\Factory {
    class Factory
    {
        public static function getContainer(): object
        {
            return new class {
                public function get(string $type): \Joomla\Database\DatabaseInterface
                {
                    return new \Diddipoeler\Component\SportsManagement\Site\Controller\TestDatabase();
                }
            };
        }
    }
}
namespace Joomla\Registry {
    class Registry
    {
        public function loadArray(array $arr): void {}
        public function __toString(): string { return '{}'; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class FormController
    {
        protected mixed $app = null;
        protected mixed $input = null;
        public array $redirect = [];
        public int $modelCalls = 0;
        public ?object $editModel = null;
        public bool $validToken = true;
        public function setEnvironment(object $app, object $input): void
        {
            $this->app = $app;
            $this->input = $input;
        }
        public function checkToken(): void
        {
            if (!$this->validToken) {
                throw new \RuntimeException('JINVALID_TOKEN', 403);
            }
        }
        public function getModel($name = ''): mixed
        {
            $this->modelCalls++;
            return $this->editModel;
        }
        public function setRedirect(string $url, string $message = '', string $type = 'message'): void
        {
            $this->redirect = compact('url', 'message', 'type');
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver
    {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector
        ): \Joomla\Database\DatabaseInterface {
            return $fallback;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class IndividualMatchWriteService
    {
        public static int $calls = 0;
        public function __construct(\Joomla\Database\DatabaseInterface $database) {}
        public function saveShort(array $post, array $ids, int $by, string $timestamp): bool
        {
            self::$calls++;
            return true;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class ResultsAccessModel
    {
        public static bool $allowed = false;
        public static array $requests = [];
        public function setDatabaseSelector(int $value): void { self::$requests['database'] = $value; }
        public function setProjectId(int $value): void { self::$requests['project'] = $value; }
        public function canEditMatches(array $ids): bool
        {
            self::$requests['matches'] = $ids;
            return self::$allowed;
        }
    }
    class EditmatchModel
    {
        public int $writes = 0;
        public function savestats(array $post): bool { $this->writes++; return true; }
        public function updateReferees(array $post): bool { $this->writes++; return true; }
        public function updateRosterBillard(array $post): bool { $this->writes++; return true; }
        public function updateRoster(array $post): bool { $this->writes++; return true; }
        public function updateStaff(array $post): bool { $this->writes++; return true; }
        public function updItem(array $post): bool { $this->writes++; return true; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Controller {
    class TestDatabase implements \Joomla\Database\DatabaseInterface {}
}
namespace {
    \define('_JEXEC', 1);
    unset($_SERVER['HTTP_REFERER']);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Controller/EditmatchController.php';

    class InputStub
    {
        public array $fields = [];
        public object $post;
        public function __construct()
        {
            $this->post = new class($this) {
                public function __construct(private InputStub $input) {}
                public function getArray(): array { return $this->input->fields; }
                public function get(string $key, mixed $default = null, string $format = ''): mixed
                {
                    return $this->input->fields[$key] ?? $default;
                }
            };
        }
        public function getInt(string $key, int $default = 0): int
        {
            return (int) ($this->fields[$key] ?? $default);
        }
    }
    class AppStub
    {
        public function __construct(private InputStub $input) {}
        public function getUserState(string $key, int $fallback = 0): int { return $fallback; }
        public function getIdentity(): object { return (object) ['id' => 7]; }
    }

    $input = new InputStub();
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel();
    $app = new AppStub($input);
    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\EditmatchController();
    $controller->setEnvironment($app, $input);
    $controller->editModel = $model;
    $access = \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel::class;
    $write = \Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchWriteService::class;
    $actions = ['savestats', 'saveReferees', 'saverosterbillard', 'saveroster',
        'saveshort', 'applyshortsinglematch', 'saveshortsinglematch'];

    $input->fields = [
        'id' => 81, 'match_id' => 81, 'matchid' => 81,
        'p' => 17, 'project_id' => 17, 'cfg_which_database' => 1,
        'cid' => [81],
    ];

    foreach ($actions as $action) {
        $controller->validToken = false;
        try {
            $controller->{$action}();
            throw new \RuntimeException($action . ' accepted invalid CSRF token');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) {
                throw $e;
            }
        }
        $controller->validToken = true;
    }

    $access::$allowed = false;
    foreach ($actions as $action) {
        try {
            $controller->{$action}();
            throw new \RuntimeException($action . ' bypassed match ACL');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) {
                throw $e;
            }
        }
        if ($access::$requests['project'] !== 17
            || $access::$requests['matches'] !== [81]
            || $access::$requests['database'] !== 1) {
            throw new \RuntimeException($action . ' ACL did not scope project, match and DB');
        }
    }
    if ($model->writes !== 0 || $write::$calls !== 0 || $controller->modelCalls !== 0) {
        throw new \RuntimeException('Unauthorized match writes reached the model or service.');
    }

    $access::$allowed = true;
    foreach ($actions as $action) {
        $input->fields['id'] = 0;
        $input->fields['match_id'] = 0;
        $input->fields['matchid'] = 0;
        try {
            $controller->{$action}();
            throw new \RuntimeException($action . ' accepted a missing match ID');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 400) {
                throw $e;
            }
        }
    }

    $input->fields['id'] = $input->fields['match_id'] = $input->fields['matchid'] = 81;
    $input->fields['p'] = $input->fields['project_id'] = 0;
    foreach ($actions as $action) {
        try {
            $controller->{$action}();
            throw new \RuntimeException($action . ' accepted a missing project');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 400) {
                throw $e;
            }
        }
    }

    $input->fields['p'] = $input->fields['project_id'] = 17;
    foreach ($actions as $action) {
        $controller->{$action}();
    }
    if ($model->writes !== 6 || $write::$calls !== 2) {
        throw new \RuntimeException('Authorized match editors did not reach expected writers.');
    }

    echo "Joomla 5/6 match editing POST, project ACL and valid writes OK\n";
}
