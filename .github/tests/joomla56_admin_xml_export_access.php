<?php
/**
 * Joomla 5/6 administrator XML export ACL and strict project target regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const INTEGER = 'integer'; }
}
namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        public function setDatabase(\Joomla\Database\DatabaseInterface $database): void {}
    }
}
namespace Joomla\CMS\Filter {
    class OutputFilter {
        public static function stringURLSafe(string $name): string {
            return preg_replace('/[^a-z0-9-]+/', '-', strtolower($name));
        }
    }
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $message): string { return $message; }
    }
}
namespace Joomla\CMS\Router {
    class Route {
        public static function _(string $url, bool $enabled = true): string { return $url; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {
        protected object $app;
        public ?array $redirect = null;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class SportsManagementAdministratorApplicationResolver {
        public static object $app;
        public static function resolve(): object { return self::$app; }
    }
}
namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', sys_get_temp_dir());
    class sportsmanagementModelJLXMLExports extends \Joomla\CMS\MVC\Model\BaseDatabaseModel {
        public int $exports = 0;
        public function exportData() {
            ++$this->exports;
            return true;
        }
    }
    class ExportDb implements \Joomla\Database\DatabaseInterface {
        public function createQuery(): object { return new \stdClass(); }
    }
    class sportsmanagementHelper {
        public static ?ExportDb $db = null;
        public static function getDBConnection(): \Joomla\Database\DatabaseInterface {
            return self::$db ??= new ExportDb();
        }
    }
    class ExportApp {
        public object $identity;
        public object $input;
        public int $modelLoads = 0;
        public ?object $model = null;
        public function __construct() {
            $this->identity = new class {
                public bool $manage = false;
                public function authorise(string $action, string $asset): bool {
                    return $this->manage && $action === 'core.manage'
                        && $asset === 'com_sportsmanagement';
                }
            };
            $this->input = new class {
                public mixed $pid = 7;
                public function get(string $key, mixed $fallback = null, string $filter = ''): mixed {
                    return $key === 'pid' ? $this->pid : $fallback;
                }
                public function getInt(string $key, int $fallback = 0): int {
                    return $key === 'pid' ? (int) $this->pid : $fallback;
                }
                public function getCmd(string $key, string $fallback = ''): string { return $fallback; }
            };
        }
        public function getIdentity(): object { return $this->identity; }
        public function getInput(): object { return $this->input; }
        public function bootComponent(string $component): object {
            return new class($this) {
                public function __construct(private ExportApp $app) {}
                public function getMVCFactory(): object {
                    return new class($this->app) {
                        public function __construct(private ExportApp $app) {}
                        public function createModel(string $name, string $section, array $options): object {
                            ++$this->app->modelLoads;
                            return $this->app->model;
                        }
                    };
                }
            };
        }
    }

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/JlxmlexportsModel.php';
    require_once $root . '/admin/src/Controller/JlxmlexportsController.php';

    $app = new ExportApp();
    \Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver::$app = $app;
    $app->model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\JlxmlexportsModel();
    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlexportsController();
    $controller->setTestApp($app);

    foreach ([
        static fn() => $controller->export(),
        static fn() => $app->model->exportData(),
    ] as $action) {
        try {
            $action();
            throw new \RuntimeException('XML export was allowed without component management rights.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) { throw $error; }
        }
    }

    if ($app->modelLoads !== 0 || $app->model->exports !== 0
        || $controller->redirect !== null) {
        throw new \RuntimeException('Unauthorized XML export accessed project data.');
    }

    $app->identity->manage = true;
    foreach ([
        0, -1, '7junk', '07', '999999999999999999999', [7], true, '',
    ] as $bad) {
        $app->input->pid = $bad;
        try {
            $controller->export();
            throw new \RuntimeException('Malformed export project identifier was accepted.');
        } catch (\UnexpectedValueException $error) {
            if ($error->getCode() !== 400) { throw $error; }
        }
        if ($app->modelLoads !== 0 || $app->model->exports !== 0) {
            throw new \RuntimeException('Malformed export target loaded project data.');
        }
    }

    $app->input->pid = '7';
    if ($controller->export() !== true || $app->modelLoads !== 1
        || $app->model->exports !== 1
        || !str_contains((string) $controller->redirect['url'], 'pid=7')) {
        throw new \RuntimeException('Authorized administrator XML export no longer works.');
    }

    echo "Joomla 5/6 administrator XML export ACL and IDs OK\n";
}
