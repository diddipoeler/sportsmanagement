<?php
/**
 * Joomla 5/6 project referee actions require ACL and preserve MVC options.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class ProjectrefereeModel {
        public int $writes = 0;
        public function saveshort(array $ids, array $post): bool { ++$this->writes; return true; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController {
        protected object $app;
        public array $granted = [];
        public bool $validToken = true;
        public array $config = [];
        public int $modelCalls = 0;
        public mixed $model = null;
        public function __construct() {
            $this->app = new class {
                public function getInput(): object {
                    return new class {
                        public object $post;
                        public function __construct() {
                            $this->post = new class {
                                public function get(string $key, mixed $default = null, string $filter = 'raw'): mixed { return $default; }
                                public function getArray(): array { return []; }
                            };
                        }
                    };
                }
            };
        }
        protected function checkToken(): void {
            if (!$this->validToken) { throw new \RuntimeException('Invalid token', 403); }
        }
        protected function assertWritePermission(string $action = 'core.edit'): void {
            if (!in_array($action, $this->granted, true)) { throw new \RuntimeException('Denied', 403); }
        }
        public function getModel($name = '', $prefix = '', $config = []) {
            ++$this->modelCalls;
            $this->config = $config;
            return $this->model;
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {}
        public function publish(): void {}
    }
}

namespace Joomla\CMS\Router {
    class Route {
        public static function _(string $route, bool $xhtml = true): string { return $route; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/ProjectrefereesController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectrefereesController();
    $controller->getModel('Projectreferee', 'Administrator', ['keep' => 'yes']);

    if ($controller->config !== ['keep' => 'yes', 'ignore_request' => true]) {
        throw new \RuntimeException('Project referee MVC config was lost.');
    }

    foreach (['saveshort' => 'core.edit', 'publish' => 'core.edit.state'] as $method => $action) {
        $before = $controller->modelCalls;
        try {
            $controller->{$method}();
            throw new \RuntimeException('Unauthorized project referee write accepted.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }
        if ($controller->modelCalls !== $before) {
            throw new \RuntimeException('Denied project referee write accessed model.');
        }
        $controller->granted = [$action];
        $controller->validToken = false;
        try {
            $controller->{$method}();
            throw new \RuntimeException('Invalid CSRF token accepted.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }
        $controller->validToken = true;
        $controller->granted = [];
    }

    $controller->granted = ['core.edit'];
    try {
        $controller->saveshort();
        throw new \RuntimeException('Absent referee model accepted.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 500) { throw $error; }
    }

    $controller->model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectrefereeModel();
    $controller->saveshort();
    if ($controller->model->writes !== 1) {
        throw new \RuntimeException('Authorized referee short save was not executed.');
    }

    echo "Project referee write ACL and MVC model guards OK\n";
}
