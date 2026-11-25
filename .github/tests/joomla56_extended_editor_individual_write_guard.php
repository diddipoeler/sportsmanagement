<?php
/**
 * Joomla 5/6 regression for executable source-editor writes and individual-match actions.
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
        public static function sprintf(string $key, int $count): string { return $key . $count; }
    }
}
namespace Joomla\CMS\Session {
    class Session {
        public static bool $valid = true;
        public static function checkToken(string $type): bool { return self::$valid && $type === 'post'; }
    }
}
namespace Joomla\CMS\Application {
    class AdministratorApplication {
        public object $identity;
        public object $input;
        public array $post = [];
        public function __construct() {
            $this->identity = new class {
                public array $granted = [];
                public function authorise(string $action, string $asset): bool {
                    return $asset === 'com_sportsmanagement' && in_array($action, $this->granted, true);
                }
            };
            $this->input = new class($this) {
                public object $post;
                public function __construct(private AdministratorApplication $app) {
                    $this->post = new class($app) {
                        public function __construct(private AdministratorApplication $app) {}
                        public function get(string $key, mixed $default = null, string $filter = ''): mixed {
                            return $this->app->post[$key] ?? $default;
                        }
                        public function getArray(): array { return $this->app->post; }
                    };
                }
                public function getString(string $key): string {
                    return (string) ($this->app->post[$key] ?? '');
                }
                public function getInt(string $key, int $default = 0): int { return $default; }
                /**
                 * Return raw selector input for the native database bridge.
                 * @param string $key Request option name.
                 * @param mixed $default Default when the option is absent.
                 * @param string $filter Requested Joomla input filter.
                 * @return mixed Raw fixture request value.
                 */
                public function get(string $key, mixed $default = null, string $filter = ''): mixed {
                    return $this->app->post[$key] ?? $default;
                }
            };
        }
        public function getIdentity(): object { return $this->identity; }
        public function getInput(): object { return $this->input; }
        public function getUserState(string $name, mixed $default = null): mixed { return $default; }
    }
}
namespace Joomla\CMS\Router {
    class Route {
        public static function _(string $url, bool $x = true): string { return $url; }
    }
}
namespace Joomla\CMS\MVC\Model {
    class AdminModel {
        private string $error = '';
        public function setError(string $error): void { $this->error = $error; }
        public function getError(): string { return $this->error; }
        protected function loadForm(string $a, string $b, array $c): mixed { return null; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class AdminController {
        protected object $app;
        public ?array $redirect = null;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
        /**
         * Simulate the native controller POST token guard with the shared test session.
         * @return void
         * @throws \RuntimeException When the test session rejects the token.
         */
        protected function checkToken(): void {
            if (!\Joomla\CMS\Session\Session::checkToken('post')) {
                throw new \RuntimeException('CSRF token rejected.', 403);
            }
        }
        public function saveorder() { return true; }
    }
    class FormController {
        protected object $app;
        public ?array $redirect = null;
        public bool $token = true;
        public int $modelCalls = 0;
        public mixed $model = null;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function checkToken(): void {
            if (!$this->token) { throw new \RuntimeException('CSRF token rejected.', 403); }
        }
        public function getModel($name = '', $prefix = '', $config = []) {
            ++$this->modelCalls;
            return $this->model;
        }
        protected function getTask(): string { return 'apply'; }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}
namespace Joomla\CMS\Form {
    class FormHelper {
        public static function addFormPath(string $path): void {}
    }
}
namespace Joomla\Filesystem {
    class File {
        public static array $writes = [];
        public static function write(string $path, string $content): bool {
            self::$writes[] = $path;
            return file_put_contents($path, $content) === strlen($content);
        }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
}
namespace Joomla\CMS {
    class Factory {
        public static \Joomla\Database\DatabaseInterface $db;
        /**
         * Supply the default database to the actual native bridge.
         * @return object Minimal Joomla dependency container fixture.
         */
        public static function getContainer(): object {
            return new class {
                /**
                 * Resolve the database service for this test.
                 * @param string $key Requested service interface.
                 * @return object Configured test database.
                 */
                public function get(string $key): object { return Factory::$db; }
            };
        }
        public static function getDate(): object {
            return new class {
                public function toSql(): string { return '2026-10-16 08:00:00'; }
            };
        }
    }
}
namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public static array $messages = [];
        /**
         * Capture redacted controller diagnostics without filesystem writes.
         * @param string $message Logged message.
         * @param int $level Joomla log severity.
         * @param string $category Log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class SportsManagementDatabaseResolver {
        public static array $selectors = [];
        /**
         * Record validated database selection and return the fixture driver.
         * @param mixed $selector Validated component database choice.
         * @param \Joomla\Database\DatabaseInterface $fallback Default database.
         * @return \Joomla\Database\DatabaseInterface Selected test driver.
         */
        public function resolve(mixed $selector, \Joomla\Database\DatabaseInterface $fallback): \Joomla\Database\DatabaseInterface {
            self::$selectors[] = $selector;
            return $fallback;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class SportsManagementAdministratorApplicationResolver {
        public static object $app;
        public static function resolve(): \Joomla\CMS\Application\AdministratorApplication {
            return self::$app;
        }
    }
    class IndividualMatchAdminService {
        public static array $events = [];
        public function __construct(\Joomla\Database\DatabaseInterface $db) {}
        public function addMatch(array $post): bool {
            self::$events[] = ['add', $post];
            return true;
        }
        public function generateSingles(array $post, int $actorId, string $now): array {
            self::$events[] = ['generate', $post];
            return [1, 0];
        }
        public function deleteSingles(array $ids): bool {
            self::$events[] = ['delete', $ids];
            return true;
        }
        public function setPublished(array $ids, int $state): bool {
            self::$events[] = ['publish', $ids, $state];
            return true;
        }
    }
    class IndividualMatchWriteService {
        public static array $events = [];
        public function __construct(\Joomla\Database\DatabaseInterface $db) {}
        public function saveShort(array $post, array $ids, int $actorId, string $date): bool {
            self::$events[] = [$ids, $actorId, $date];
            return true;
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $dir = sys_get_temp_dir() . '/sportsmanagement-extended-guard-' . getmypid();
    define('JPATH_ADMINISTRATOR', $dir);
    $extended = $dir . '/components/com_sportsmanagement/assets/extended';
    if (!is_dir($extended) && !mkdir($extended, 0700, true)) {
        throw new \RuntimeException('Could not create isolated source-editor test directory.');
    }
    register_shutdown_function(static function () use ($dir, $extended): void {
        foreach (['real.php', 'config.xml', 'link.php', 'too-big.xml'] as $name) {
            @unlink($extended . '/' . $name);
        }
        @rmdir($extended);
        @rmdir(dirname($extended));
        @rmdir(dirname(dirname($extended)));
        @rmdir(dirname(dirname(dirname($extended))));
        @rmdir($dir);
    });
    file_put_contents($extended . '/real.php', '<?php return true;');
    file_put_contents($extended . '/config.xml', '<root/>');
    file_put_contents($extended . '/too-big.xml', str_repeat('a', 1048577));
    @symlink($extended . '/config.xml', $extended . '/link.php');

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Helper/LegacyModelDatabaseHelper.php';
    require_once $root . '/admin/src/Controller/SportsManagementAdminController.php';
    require_once $root . '/admin/src/Controller/JlextindividualsportController.php';
    require_once $root . '/admin/src/Controller/JlextindividualsportesController.php';
    require_once $root . '/admin/src/Model/SmextxmleditorModel.php';
    require_once $root . '/admin/src/Controller/SmextxmleditorController.php';

    $app = new \Joomla\CMS\Application\AdministratorApplication();
    \Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver::$app = $app;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorModel();
    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\SmextxmleditorController();
    $controller->setTestApp($app);
    $controller->model = $model;
    $app->post = ['jform' => ['filename' => 'real.php', 'source' => '<?php return 123;'], 'file_name' => 'real.php'];

    foreach ([
        static fn() => $model->save($app->post['jform']),
        static fn() => $model->getSource(),
        static fn() => $controller->save(),
    ] as $operation) {
        try {
            $operation();
            throw new \RuntimeException('Extended source editor ignored admin ACL.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    if (file_get_contents($extended . '/real.php') !== '<?php return true;'
        || \Joomla\Filesystem\File::$writes !== []
        || $controller->modelCalls !== 0) {
        throw new \RuntimeException('Unauthorized file editor changed a source file.');
    }

    $app->identity->granted = ['core.admin'];
    $controller->token = false;
    try {
        $controller->save();
        throw new \RuntimeException('Source-editor controller accepted invalid CSRF token.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    $controller->token = true;
    if (!$controller->save() || file_get_contents($extended . '/real.php') !== '<?php return 123;') {
        throw new \RuntimeException('Authorized source-file editing stopped working.');
    }
    if ($model->getSource()->source !== '<?php return 123;') {
        throw new \RuntimeException('Authorized source-file reading stopped working.');
    }

    foreach ([
        ['filename' => '../real.php', 'source' => 'bad'],
        ['filename' => '/tmp/real.php', 'source' => 'bad'],
        ['filename' => 'new.php', 'source' => 'bad'],
        ['filename' => 'real..php', 'source' => 'bad'],
        ['filename' => 'real.php/../real.php', 'source' => 'bad'],
        ['filename' => 'real.php', 'source' => ['bad']],
        ['filename' => ['real.php'], 'source' => 'bad'],
        ['filename' => 'real.php', 'source' => str_repeat('x', 1048577)],
        ['filename' => 'link.php', 'source' => 'bad'],
    ] as $invalid) {
        $writes = count(\Joomla\Filesystem\File::$writes);
        if ($model->save($invalid) !== false || count(\Joomla\Filesystem\File::$writes) !== $writes) {
            throw new \RuntimeException('Invalid/symlink/oversized source-editor write was accepted.');
        }
    }
    $app->post['file_name'] = 'too-big.xml';
    $oversizedRead = $model->getSource();
    if (isset($oversizedRead->source)) {
        throw new \RuntimeException('Oversized XML/PHP editor source was read.');
    }
    $app->post['file_name'] = 'link.php';
    $linkedRead = $model->getSource();
    if (isset($linkedRead->source)) {
        throw new \RuntimeException('Editor followed a symlink.');
    }

    $db = new class implements \Joomla\Database\DatabaseInterface {};
    \Joomla\CMS\Factory::$db = $db;
    $individual = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextindividualsportController();
    $list = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextindividualsportesController();
    $individual->setTestApp($app);
    $list->setTestApp($app);
    $app->post = ['match_id' => 4, 'round_id' => 9, 'cid' => ['12', '12bad', '14', '12', '0', '9999999999999999999999']];
    $writes = \Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService::$events;
    $app->identity->granted = [];
    foreach ([
        static fn() => $individual->addmatch(),
        static fn() => $list->generatematchsingles(),
        static fn() => $list->saveshort(),
        static fn() => $list->applyshort(),
        static fn() => $list->publish(),
        static fn() => $list->unpublish(),
        static fn() => $list->delete(),
    ] as $action) {
        try {
            $action();
            throw new \RuntimeException('Individual-match controller accepted unauthorized write.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    if (\Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService::$events !== $writes) {
        throw new \RuntimeException('Unauthorized individual-match operation wrote data.');
    }

    $app->identity->granted = ['core.create', 'core.edit', 'core.edit.state', 'core.delete'];
    \Joomla\CMS\Session\Session::$valid = false;
    foreach ([static fn() => $individual->addmatch(), static fn() => $list->publish()] as $action) {
        try {
            $action();
            throw new \RuntimeException('Individual-match write ignored CSRF.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    \Joomla\CMS\Session\Session::$valid = true;
    $app->post['cfg_which_database'] = '1bad';
    $individual->addmatch();
    $list->generatematchsingles();
    if (\Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService::$events !== $writes
        || \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver::$selectors !== []
        || $individual->redirect['type'] !== 'error' || $list->redirect['type'] !== 'error') {
        throw new \RuntimeException('Malformed database selector reached an individual-match write.');
    }
    $app->post['cfg_which_database'] = '0001';
    $individual->addmatch();
    $list->generatematchsingles();
    $list->saveshort();
    $list->publish();
    $list->unpublish();
    $list->delete();
    $events = \Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService::$events;
    if (count($events) !== 5
        || $events[0][0] !== 'add'
        || $events[1][0] !== 'generate'
        || $events[2] !== ['publish', [12, 14], 1]
        || $events[3] !== ['publish', [12, 14], 0]
        || $events[4] !== ['delete', [12, 14]]) {
        throw new \RuntimeException('Authorized individual-match operation or canonical IDs failed.');
    }
    if (\Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver::$selectors !== array_fill(0, 6, 1)) {
        throw new \RuntimeException('Individual-match controllers did not share the validated database selector.');
    }
    $app->post['cid'] = array_fill(0, 501, '12');
    try {
        $list->delete();
        throw new \RuntimeException('Overlarge individual-match selection was accepted.');
    } catch (\UnexpectedValueException $e) {
        if ($e->getCode() !== 400) { throw $e; }
    }

    echo "Joomla 5/6 extended editor and individual-match permissions OK\n";
}
