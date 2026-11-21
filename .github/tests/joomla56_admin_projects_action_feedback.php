<?php
/**
 * Joomla 5/6 administrator Project action permissions and feedback.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Session {
    final class Session {
        public static bool $valid = true;
        public static function checkToken(string $type = 'post'): bool
        {
            return $type === 'post' && self::$valid;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text {
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Router {
    final class Route {
        public static function _(string $route, bool $xhtml = true): string { return $route; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class ProjectModel {
        public mixed $result = true;
        public string $error = 'a stale previous error';
        public int $writes = 0;
        public function saveshort(): mixed { ++$this->writes; return $this->result; }
        public function setleaguechampion(): mixed { ++$this->writes; return $this->result; }
        public function copy(): mixed { ++$this->writes; return $this->result; }
        public function getError(): string { return $this->error; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController {
        protected object $app;
        public ?array $redirect = null;
        public array $lastConfig = [];
        public int $modelRequests = 0;
        public mixed $model = null;

        public function setTestApplication(object $app): void { $this->app = $app; }
        public function getModel($name = '', $prefix = '', $config = []) {
            ++$this->modelRequests;
            $this->lastConfig = $config;
            return $this->model;
        }
        protected function setRedirect(string $url, mixed $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/ProjectsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectsController();
    $app = new class {
        public ?object $identity = null;
        public function getIdentity(): ?object { return $this->identity; }
    };
    $controller->setTestApplication($app);
    $project = new \Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel();
    $controller->model = $project;

    $controller->getModel('Project', 'Administrator', ['custom' => 1, 'ignore_request' => true]);
    if ($controller->lastConfig !== ['ignore_request' => true, 'custom' => 1]) {
        throw new \RuntimeException('Projects controller dropped MVC caller options.');
    }

    $before = $controller->modelRequests;
    foreach (['saveshort', 'setleaguechampion', 'copy'] as $task) {
        try {
            $controller->{$task}();
            throw new \RuntimeException('Missing user identity accepted for ' . $task);
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }
    }
    if ($controller->modelRequests !== $before || $project->writes !== 0) {
        throw new \RuntimeException('Anonymous project actions opened a persistence model.');
    }

    $app->identity = new class {
        public int $id = 42;
        public bool $authorized = false;
        public function authorise(string $action, string $asset): bool {
            return $this->authorized && $asset === 'com_sportsmanagement'
                && in_array($action, ['core.create', 'core.edit'], true);
        }
    };

    try {
        $controller->copy();
        throw new \RuntimeException('Project creation allowed without authorization.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) { throw $error; }
    }

    // Even internal dispatch calls must not invoke arbitrary model methods
    // or pair a write operation with a weaker Joomla ACL permission.
    $action = new \ReflectionMethod($controller, 'runProjectAction');
    foreach ([
        ['getError', 'core.edit'],
        ['copy', 'core.edit'],
        ['saveshort', 'core.create'],
    ] as [$method, $permission]) {
        $writesBefore = $project->writes;
        $requestsBefore = $controller->modelRequests;

        try {
            $action->invoke($controller, $permission, $method);
            throw new \RuntimeException('Unsupported project action accepted: ' . $method);
        } catch (\InvalidArgumentException $error) {
            if ($error->getCode() !== 400) {
                throw $error;
            }
        }

        if ($project->writes !== $writesBefore || $controller->modelRequests !== $requestsBefore) {
            throw new \RuntimeException('Invalid project action reached the persistence model.');
        }
    }

    // Reject guests, malformed legacy user IDs and identity implementations
    // without authorise(), even when a fake ACL would allow all writes.
    $identityId = new \ReflectionMethod($controller, 'authenticatedUserId');
    foreach ([
        [42, 42], ['00042', 42], [0, 0], ['0', 0],
        [true, 0], [false, 0], [['42'], 0],
        ['42junk', 0], ['42.5', 0], [' 42', 0],
        ['999999999999999999999999', 0],
    ] as [$raw, $expected]) {
        if ($identityId->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Malformed project administrator user ID was accepted.');
        }
    }

    foreach ([0, '42junk', true, ['42'], '999999999999999999999999'] as $invalidId) {
        $app->identity = new class($invalidId) {
            public function __construct(public mixed $id) {}

            public function authorise(string $action, string $asset): bool
            {
                return true;
            }
        };

        $writesBefore = $project->writes;
        $requestsBefore = $controller->modelRequests;

        try {
            $controller->copy();
            throw new \RuntimeException('Malformed project identity was accepted.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) {
                throw $error;
            }
        }

        if ($project->writes !== $writesBefore || $controller->modelRequests !== $requestsBefore) {
            throw new \RuntimeException('Invalid administrator identity reached project write model.');
        }
    }

    $app->identity = new class {
        public int $id = 42;
    };
    try {
        $controller->saveshort();
        throw new \RuntimeException('Identity without an ACL method was accepted.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) {
            throw $error;
        }
    }

    $app->identity = new class {
        public int $id = 42;
        public bool $authorized = true;
        public function authorise(string $action, string $asset): bool {
            return $this->authorized && $asset === 'com_sportsmanagement'
                && in_array($action, ['core.create', 'core.edit'], true);
        }
    };
    \Joomla\CMS\Session\Session::$valid = false;
    try {
        $controller->copy();
        throw new \RuntimeException('Project copy accepted invalid CSRF token.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) { throw $error; }
    }
    \Joomla\CMS\Session\Session::$valid = true;

    $project->result = true;
    $controller->saveshort();
    if ($controller->redirect['message'] !== 'JLIB_APPLICATION_SAVE_SUCCESS'
        || $controller->redirect['type'] !== 'message') {
        throw new \RuntimeException('Successful Project write showed a stale error.');
    }

    $project->result = 'Copy finished';
    $controller->copy();
    if ($controller->redirect['message'] !== 'Copy finished'
        || $controller->redirect['type'] !== 'message') {
        throw new \RuntimeException('Project action custom success message was lost.');
    }

    $project->result = false;
    $project->error = 'Save rejected';
    $controller->setleaguechampion();
    if ($controller->redirect['message'] !== 'Save rejected'
        || $controller->redirect['type'] !== 'warning') {
        throw new \RuntimeException('Project save failure lost its error message.');
    }

    $project->result = null;
    $project->error = '';
    $controller->copy();
    if ($controller->redirect['message'] !== 'JERROR_AN_ERROR_HAS_OCCURRED'
        || $controller->redirect['type'] !== 'warning') {
        throw new \RuntimeException('Null project save result incorrectly reported success.');
    }

    $project->result = false;
    $project->error = '';
    $controller->saveshort();
    if ($controller->redirect['message'] !== 'JERROR_AN_ERROR_HAS_OCCURRED'
        || $controller->redirect['type'] !== 'warning') {
        throw new \RuntimeException('Empty project model error lost the fallback message.');
    }

    echo "Administrator Projects permissions and action feedback OK\n";
}
