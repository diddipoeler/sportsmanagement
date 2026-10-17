<?php
/**
 * Joomla 5/6 regression for explicit Google API provisioning and OAuth ACL.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text { public static function _(string $s): string { return $s; } }
}
namespace Joomla\Database {
    class ParameterType { public const INTEGER = 'integer'; }
}
namespace Joomla\CMS\Log {
    class Log {
        public const NOTICE = 200;
        public static array $messages = [];
        public static function add(string $message, int $priority, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Joomla\Filesystem {
    class File {
        public static int $checks = 0;
        public static function exists(string $path): bool {
            ++self::$checks;
            return true;
        }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {
        protected object $app;
        public bool $tokenValid = true;
        public int $loads = 0;
        public ?array $redirect = null;
        public mixed $model = null;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function checkToken(): void {
            if (!$this->tokenValid) {
                throw new \RuntimeException('CSRF token required', 403);
            }
        }
        public function getModel($name = '', $prefix = '', $config = []) {
            ++$this->loads;
            return $this->model;
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = [$url, $message, $type];
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController extends \Joomla\CMS\MVC\Controller\BaseController {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {
        protected function administratorApplication(): object {
            return $GLOBALS['calendarGuardApp'];
        }
    }
}
namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', '/fake-joomla');

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/JsmgcalendarsModel.php';
    require_once $root . '/admin/src/Model/JsmgcalendarimportModel.php';
    require_once $root . '/admin/src/Controller/JsmgcalendarsController.php';
    require_once $root . '/admin/src/Controller/JsmgcalendarimportController.php';

    class CalendarGuardApp {
        public object $identity;
        public object $input;
        public array $params = [];
        public function __construct() {
            $this->identity = new class {
                public bool $admin = false;
                public function authorise(string $permission, string $asset): bool {
                    return $this->admin && $permission === 'core.admin'
                        && $asset === 'com_sportsmanagement';
                }
            };
            $this->input = new class($this) {
                public function __construct(private CalendarGuardApp $app) {}
                public function get(string $key, mixed $default = null, string $filter = ''): mixed {
                    return $this->app->params[$key] ?? $default;
                }
                public function getString(string $key, string $default = ''): string {
                    return is_string($this->app->params[$key] ?? null)
                        ? $this->app->params[$key] : $default;
                }
            };
        }
        public function getInput(): object { return $this->input; }
        public function getIdentity(): object { return $this->identity; }
    }
    $GLOBALS['calendarGuardApp'] = new CalendarGuardApp();
    $app = $GLOBALS['calendarGuardApp'];

    $provisionModel = new \Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarsModel();
    $importModel = new \Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarimportModel();
    $list = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JsmgcalendarsController();
    $import = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JsmgcalendarimportController();
    $list->setTestApp($app);
    $import->setTestApp($app);
    $list->model = $provisionModel;
    $import->model = new class {
        public int $imports = 0;
        public function import(): bool { ++$this->imports; return true; }
    };

    // The calendar list must no longer provision code through a GET display.
    $view = file_get_contents($root . '/admin/src/View/Jsmgcalendars/HtmlView.php');
    if (!is_string($view)
        || str_contains($view, '$model->check_google_api()')
        || !str_contains($view, 'jsmgcalendars.installgoogleapi')) {
        throw new \RuntimeException('Calendar GET view still provisions third-party PHP libraries.');
    }

    // Neither direct model entry point may run before a component admin ACL.
    foreach ([
        static fn() => $list->installgoogleapi(),
        static fn() => $provisionModel->check_google_api(),
        static fn() => $import->import(),
        static fn() => $importModel->import(),
    ] as $entry) {
        try {
            $entry();
            throw new \RuntimeException('Non-administrator reached calendar import or package install.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    if ($list->loads || $import->loads || \Joomla\Filesystem\File::$checks !== 0
        || $import->model->imports !== 0) {
        throw new \RuntimeException('Unauthorized calendar code reached models or disk.');
    }

    $app->identity->admin = true;
    $list->tokenValid = false;
    $import->tokenValid = false;
    foreach ([
        static fn() => $list->installgoogleapi(),
        static fn() => $import->import(),
    ] as $entry) {
        try {
            $entry();
            throw new \RuntimeException('Calendar initiation accepted an invalid POST token.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    if ($list->loads !== 0 || $import->loads !== 0) {
        throw new \RuntimeException('Missing POST token reached the calendar service.');
    }

    $list->tokenValid = true;
    $list->installgoogleapi();
    if ($list->loads !== 1 || \Joomla\Filesystem\File::$checks !== 1
        || $list->redirect[2] !== 'message') {
        throw new \RuntimeException('Explicit Google API provisioning broke.');
    }

    $import->tokenValid = true;
    $import->import();
    if ($import->loads !== 1 || $import->model->imports !== 1) {
        throw new \RuntimeException('Valid OAuth import initiation broke.');
    }

    // OAuth callbacks cannot send the initiating form token, but they must
    // still pass component ACL, while initiation must always have POST CSRF.
    $app->params = ['code' => 'oauth-code', 'state' => 'session-state'];
    $import->tokenValid = false;
    $import->import();
    if ($import->model->imports !== 2) {
        throw new \RuntimeException('Real OAuth callback requires a POST token.');
    }
    $app->identity->admin = false;
    try {
        $import->import();
        throw new \RuntimeException('OAuth callback bypassed administrator permission.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }

    $app->identity->admin = true;
    $app->params = ['code' => ['invalid']];
    try {
        $import->import();
        throw new \RuntimeException('Array OAuth code bypassed initiation token.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }

    echo "Joomla 5/6 Google calendar provisioning and OAuth admin ACL OK\n";
}
