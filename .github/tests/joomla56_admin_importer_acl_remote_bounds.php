<?php
/**
 * Joomla 5/6 administrator importer permission and remote payload regression.
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
namespace Joomla\CMS\Log {
    class Log {
        public const NOTICE = 200;
        public const WARNING = 300;
        public static function add(string $message, int $severity, string $category): void {}
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {
        protected object $app;
        protected object $input;
        public bool $validToken = true;
        public ?array $redirect = null;
        public function __construct($config = [], $factory = null, $app = null, $input = null) {}
        public function setTestApp(object $app): void {
            $this->app = $app;
            $this->input = $app->getInput();
        }
        protected function registerTask(string $task, string $handler): void {}
        protected function getTask(): string { return 'select'; }
        protected function checkToken(): void {
            if (!$this->validToken) { throw new \RuntimeException('Invalid CSRF token', 403); }
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
        public function display($cacheable = false, $urlparams = []) { return true; }
    }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(array $values = []) {}
    }
}
namespace Joomla\Http {
    class HttpFactory {
        public static string $body = 'sample';
        public static int $code = 200;
        public static function getHttp(object $options, array $transports): object {
            return new class {
                public function get(string $url): object {
                    return (object) ['code' => HttpFactory::$code, 'body' => HttpFactory::$body];
                }
            };
        }
    }
}
namespace Joomla\Archive { class Archive {} }
namespace Joomla\Filesystem {
    class File {
        public static int $writes = 0;
        public static function exists(string $path): bool { return false; }
        public static function write(string $path, string $contents): bool {
            ++self::$writes;
            return true;
        }
        public static function makeSafe(string $value): string { return $value; }
    }
    class Folder {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class ImportModelStub {
        public int $loads = 0;
        public function getData(...$args): void { ++$this->loads; }
    }
    class JlextdbbimportModel extends ImportModelStub {}
    class JlextlmoimportsModel extends ImportModelStub {}
    class JlextdfbnetplayerimportModel extends ImportModelStub {}
    class JlextprofleagimportModel extends ImportModelStub {}
    class JlextsisimportModel extends ImportModelStub {}
}
namespace {
    define('_JEXEC', 1);
    define('JPATH_SITE', sys_get_temp_dir() . '/jsm-import-fixture');
    $root = dirname(__DIR__, 2);
    foreach ([
        'JlextdbbimportController',
        'JlextlmoimportsController',
        'JlextdfbnetplayerimportController',
        'JlextprofleagimportController',
        'JlextsisimportController',
        'JlxmlimportController',
    ] as $file) {
        require_once $root . '/admin/src/Controller/' . $file . '.php';
    }

    class ImportInput {
        public object $post;
        public object $files;
        public array $sets = [];
        public function __construct() {
            $this->post = new class {
                public function getArray(): array { return []; }
            };
            $this->files = new class {
                public function get(string $name, mixed $fallback = []): mixed { return $fallback; }
            };
        }
        public function getCmd(string $name, string $fallback = ''): string {
            return $name === 'option' ? 'com_content' : $fallback;
        }
        public function getInt(string $name, int $default = 0): int { return $default; }
        public function getString(string $name, string $default = ''): string { return $default; }
        public function set(string $name, mixed $value): void { $this->sets[$name] = $value; }
    }
    class ImportApp {
        public ImportInput $input;
        public object $identity;
        public int $stateWrites = 0;
        public int $modelCreates = 0;
        public array $models = [];
        public function __construct() {
            $this->input = new ImportInput();
            $this->identity = new class {
                public bool $admin = false;
                public function authorise(string $permission, string $asset): bool {
                    return $this->admin
                        && $permission === 'core.admin'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
        public function getIdentity(): object { return $this->identity; }
        public function getInput(): ImportInput { return $this->input; }
        public function setUserState(string $key, mixed $value): void { ++$this->stateWrites; }
        public function enqueueMessage(string $message, string $level): void {}
        public function bootComponent(string $component): object {
            return new class($this) {
                public function __construct(private ImportApp $app) {}
                public function getMVCFactory(): object {
                    return new class($this->app) {
                        public function __construct(private ImportApp $app) {}
                        public function createModel(string $name, string $prefix, array $config): object {
                            ++$this->app->modelCreates;
                            $type = 'Diddipoeler\\Component\\SportsManagement\\Administrator\\Model\\' . $name . 'Model';
                            return $this->app->models[$name] ??= new $type();
                        }
                    };
                }
            };
        }
    }

    $controllers = [
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdbbimportController::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextlmoimportsController::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdfbnetplayerimportController::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextprofleagimportController::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextsisimportController::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlimportController::class,
    ];

    foreach ($controllers as $controllerType) {
        $app = new ImportApp();
        $controller = new $controllerType();
        $controller->setTestApp($app);
        try {
            $controller->save();
            throw new \RuntimeException($controllerType . ' accepted a non-admin importer write.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
        if ($app->stateWrites !== 0 || $app->modelCreates !== 0
            || $controller->redirect !== null) {
            throw new \RuntimeException($controllerType . ' processed import state before ACL.');
        }

        $controller->validToken = false;
        $app->identity->admin = true;
        try {
            $controller->save();
            throw new \RuntimeException($controllerType . ' accepted a missing CSRF token.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }

        $controller->validToken = true;
        $result = $controller->save();
        if (str_contains($controllerType, 'profleagimport')) {
            if ($result !== false || $app->modelCreates !== 0) {
                throw new \RuntimeException('ProfiLeague controller accepted nonexistent import file.');
            }
        } elseif ($result !== true || $controller->redirect === null
            || !str_contains($controller->redirect['url'], 'option=com_sportsmanagement')
            || str_contains($controller->redirect['url'], 'option=com_content')) {
            throw new \RuntimeException($controllerType . ' failed allowed import/redirect routing.');
        }
    }

    $xmlApp = new ImportApp();
    $xml = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlimportController();
    $xml->setTestApp($xmlApp);

    try {
        $xml->select();
        throw new \RuntimeException('Unprivileged XML import selection was accepted.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    if ($xmlApp->stateWrites !== 0 || $xmlApp->input->sets !== []) {
        throw new \RuntimeException('Unprivileged XML import selection changed Joomla state.');
    }
    $xmlApp->identity->admin = true;
    if ($xml->select() !== true || $xmlApp->stateWrites !== 2) {
        throw new \RuntimeException('Authorized XML import selection no longer works.');
    }

    // A remote export can be legitimate yet unexpectedly huge.
    // Do not write the payload into the Joomla temp import file.
    \Joomla\Http\HttpFactory::$body = str_repeat('x', 25 * 1024 * 1024 + 1);
    $fetch = new \ReflectionMethod($xml, 'downloadRemoteImport');
    $written = \Joomla\Filesystem\File::$writes;
    if ($fetch->invoke($xml, 3, false) !== false
        || \Joomla\Filesystem\File::$writes !== $written) {
        throw new \RuntimeException('Oversized remote JLG payload was persisted.');
    }

    \Joomla\Http\HttpFactory::$body = 'valid import';
    if ($fetch->invoke($xml, 3, false) !== true
        || \Joomla\Filesystem\File::$writes !== $written + 1) {
        throw new \RuntimeException('Small remote JLG imports stopped working.');
    }
    echo "Joomla 5/6 importer ACL, routing and remote payload guards OK\n";
}
