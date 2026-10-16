<?php
/**
 * Joomla 5/6 JoomLeague importer POST authorization regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $message): string { return $message; }
    }
}
namespace Joomla\CMS\Router {
    class Route {
        public static function _(string $url, bool $route = true): string { return $url; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {
        protected object $app;
        public bool $validToken = true;
        public int $modelCalls = 0;
        public ?array $redirect = null;
        public object $model;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function checkToken(): void {
            if (!$this->validToken) { throw new \RuntimeException('Bad CSRF token', 403); }
        }
        public function getModel($name = '', $prefix = '', $config = []) {
            ++$this->modelCalls;
            return $this->model;
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}
namespace Joomla\CMS\Component {
    class ComponentHelper {}
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class DatabaseFactory {}
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Controller/JoomleagueimportsController.php';

    $app = new class {
        public object $identity;
        public object $input;
        public int $stateChanges = 0;
        public function __construct() {
            $this->identity = new class {
                public bool $admin = false;
                public function authorise(string $action, string $asset): bool {
                    return $this->admin
                        && $action === 'core.admin'
                        && $asset === 'com_sportsmanagement';
                }
            };
            $this->input = new class {
                public string $step = 'ENDE';
                public function getString(string $key, string $default = ''): string {
                    return $key === 'jl_table_import_step' ? $this->step : $default;
                }
                public function getInt(string $key, int $default = 0): int { return $default; }
            };
        }
        public function getIdentity(): object { return $this->identity; }
        public function getInput(): object { return $this->input; }
        public function getDocument(): object {
            throw new \RuntimeException('Document must not be used for ENDE step.');
        }
        public function setUserState(string $key, mixed $value): void { ++$this->stateChanges; }
    };
    $model = new class {
        public int $calls = 0;
        public function joomleaguesetagegroup(): void { ++$this->calls; }
    };
    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JoomleagueimportsController();
    $controller->setTestApp($app);
    $controller->model = $model;

    foreach (['joomleaguesetagegroup', 'importjoomleaguenew', 'importjoomleagueagegroup'] as $method) {
        try {
            $controller->{$method}();
            throw new \RuntimeException($method . ' accepted a non-administrator.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    if ($controller->modelCalls !== 0 || $controller->redirect !== null
        || $model->calls !== 0 || $app->stateChanges !== 0) {
        throw new \RuntimeException('Unauthorized JoomLeague task mutated importer state.');
    }

    $app->identity->admin = true;
    $controller->validToken = false;
    foreach (['joomleaguesetagegroup', 'importjoomleaguenew', 'importjoomleagueagegroup'] as $method) {
        try {
            $controller->{$method}();
            throw new \RuntimeException($method . ' accepted missing CSRF token.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    $controller->validToken = true;
    if ($controller->joomleaguesetagegroup() !== true || $model->calls !== 1
        || $controller->modelCalls !== 1) {
        throw new \RuntimeException('Authorized JoomLeague age-group mapping broke.');
    }
    if ($controller->importjoomleaguenew() !== true
        || !str_contains((string) $controller->redirect['url'], 'jl_table_import_step=0')) {
        throw new \RuntimeException('Authorized JoomLeague completion no longer redirects.');
    }
    if ($controller->importjoomleagueagegroup() !== true
        || !str_contains((string) $controller->redirect['url'], 'layout=infofield')) {
        throw new \RuntimeException('Authorized JoomLeague age-group navigation broke.');
    }

    echo "Joomla 5/6 JoomLeague import admin ACL and CSRF OK\n";
}
