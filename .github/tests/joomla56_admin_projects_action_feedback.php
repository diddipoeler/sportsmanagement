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

    $app->identity->authorized = true;
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

    echo "Administrator Projects permissions and action feedback OK\n";
}
