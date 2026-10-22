<?php
/**
 * Joomla 5/6 project-position save ACL and missing-model regression tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController {
        protected object $app;
        public array $allowed = [];
        public bool $tokenValid = true;
        public mixed $model = null;
        public int $modelCalls = 0;
        public array $modelConfig = [];
        public ?array $redirect = null;

        public function __construct() {
            $this->app = new class {
                public function getInput(): object {
                    return new class {
                        public object $post;
                        public function __construct() {
                            $this->post = new class {
                                public function getArray(): array { return ['title' => 'test']; }
                            };
                        }
                    };
                }
            };
        }

        public function getModel($name = '', $prefix = '', $config = []) {
            ++$this->modelCalls;
            $this->modelConfig = $config;
            return $this->model;
        }
        protected function checkToken(): void {
            if (!$this->tokenValid) { throw new \RuntimeException('Invalid token', 403); }
        }
        protected function assertWritePermission(string $permission = 'core.edit'): void {
            if (!in_array($permission, $this->allowed, true)) {
                throw new \RuntimeException('Permission denied', 403);
            }
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = [$url, $message, $type];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/ProjectpositionsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectpositionsController();
    $controller->getModel('Projectposition', 'Administrator', ['ignore_request' => false, 'keep' => 123]);
    if ($controller->modelConfig !== ['ignore_request' => true, 'keep' => 123]) {
        throw new \RuntimeException('Project-position MVC configuration was discarded.');
    }
    $before = $controller->modelCalls;
    try {
        $controller->store();
        throw new \RuntimeException('Project-position save allowed without ACL.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    if ($controller->modelCalls !== $before) {
        throw new \RuntimeException('Unauthorized project-position save loaded the model.');
    }

    $controller->allowed = ['core.edit'];
    $controller->tokenValid = false;
    try {
        $controller->store();
        throw new \RuntimeException('Project-position save accepted an invalid token.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    $controller->tokenValid = true;
    foreach ([false, null] as $missing) {
        $controller->model = $missing;
        try {
            $controller->store();
            throw new \RuntimeException('Missing project-position model was allowed.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 500) { throw $e; }
        }
    }

    $controller->model = new class {
        public array $saved = [];
        public function store(array $data): bool {
            $this->saved = $data;
            return true;
        }
    };
    $controller->store();
    if ($controller->model->saved !== ['title' => 'test']
        || $controller->redirect[2] !== 'message') {
        throw new \RuntimeException('Authorized project-position write failed.');
    }

    echo "Project-position controller ACL and MVC regressions passed\n";
}
