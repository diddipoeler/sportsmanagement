<?php
/**
 * Joomla 5/6 round writer ACL, token and MVC model regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController {
        protected object $app;
        protected object $input;
        public array $permission = [];
        public bool $validToken = true;
        public mixed $model = null;
        public int $modelCalls = 0;
        public array $lastConfig = [];
        public ?array $redirect = null;

        public function __construct()
        {
            $input = new class {
                public object $post;
                public function __construct()
                {
                    $this->post = new class {
                        public function getArray(): array { return []; }
                        public function get(string $key, mixed $fallback = null, string $filter = 'raw'): mixed
                        {
                            return $fallback;
                        }
                    };
                }
                public function get(string $key, mixed $fallback = null, string $filter = 'raw'): mixed
                {
                    return $fallback;
                }
            };
            $this->input = $input;
            $this->app = new class($input) {
                public function __construct(private object $input) {}
                public function getInput(): object { return $this->input; }
                public function getUserState(string $key, mixed $default = null): mixed { return $default; }
                public function setUserState(string $key, mixed $value): void {}
            };
        }

        public function getModel($name = '', $prefix = '', $config = [])
        {
            ++$this->modelCalls;
            $this->lastConfig = $config;
            return $this->model;
        }

        protected function checkToken(): void
        {
            if (!$this->validToken) { throw new \RuntimeException('Invalid token', 403); }
        }

        protected function assertWritePermission(string $action = 'core.edit'): void
        {
            if (!in_array($action, $this->permission, true)) {
                throw new \RuntimeException('Not authorised', 403);
            }
        }

        protected function setRedirect(string $url, mixed $message = '', string $type = 'message'): void
        {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}

namespace Joomla\CMS\Router {
    final class Route {
        public static function _(string $url, bool $xhtml = true): string { return $url; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/RoundsController.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\RoundsController();
    $controller->getModel('Round', 'Administrator', ['ignore_request' => false, 'sentinel' => true]);

    if ($controller->lastConfig !== ['ignore_request' => true, 'sentinel' => true]) {
        throw new \RuntimeException('Rounds controller discarded MVC model configuration.');
    }

    foreach (['massadd' => 'core.create', 'deleteRoundMatches' => 'core.delete',
        'saveshort' => 'core.edit'] as $method => $permission) {
        $controller->permission = [];
        $controller->validToken = true;
        $before = $controller->modelCalls;

        try {
            $controller->{$method}();
            throw new \RuntimeException('Unauthorized round write succeeded: ' . $method);
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }

        if ($controller->modelCalls !== $before) {
            throw new \RuntimeException('Unauthorized round action touched the persistence model.');
        }

        $controller->permission = [$permission];
        $controller->validToken = false;

        try {
            $controller->{$method}();
            throw new \RuntimeException('Invalid CSRF token was accepted: ' . $method);
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }

        if ($controller->modelCalls !== $before) {
            throw new \RuntimeException('Invalid round request accessed model.');
        }

        $controller->validToken = true;

        foreach ([false, null] as $missing) {
            $controller->model = $missing;

            try {
                $controller->{$method}();
                throw new \RuntimeException('Unavailable round model was accepted.');
            } catch (\RuntimeException $error) {
                if ($error->getCode() !== 500
                    || !str_contains($error->getMessage(), 'round model could not be loaded')) {
                    throw $error;
                }
            }
        }
    }

    echo "Administrator Rounds write and missing-model guards OK\n";
}
