<?php
/**
 * Joomla 5/6 source editor authorization, frontend write, and quote module guards.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    class AdministratorApplication {
        public object $identity;
        public array $form = [];
        public function __construct() {
            $this->identity = new class {
                public bool $allowed = false;
                public function authorise(string $action, string $asset): bool {
                    return $this->allowed
                        && $action === 'core.admin'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
        public function getIdentity(): object { return $this->identity; }
        public function getInput(): object {
            return new class($this) {
                public object $post;
                public function __construct(AdministratorApplication $app) {
                    $this->post = new class($app) {
                        public function __construct(private AdministratorApplication $app) {}
                        public function get(string $key, mixed $default = null, string $filter = ''): mixed {
                            return $this->app->form;
                        }
                    };
                }
            };
        }
    }
    interface CMSApplicationInterface {}
}
namespace Joomla\CMS\Form {
    class Form {}
    class FormHelper {
        public static function addFormPath(string $path): void {}
    }
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $message): string { return $message; }
    }
}
namespace Joomla\CMS\Router {
    class Route {
        public static function _(string $value, bool $route = true): string { return $value; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class FormController {
        protected object $app;
        public mixed $model = false;
        public string $task = 'save';
        public bool $tokenValid = true;
        public int $getModelCalls = 0;
        public ?array $redirect = null;
        public function setApp(object $app): void { $this->app = $app; }
        protected function checkToken(): void {
            if (!$this->tokenValid) { throw new \RuntimeException('Invalid token', 403); }
        }
        protected function getModel(
            $name = 'Smquotetxt',
            $prefix = 'Administrator',
            $config = []
        ) {
            ++$this->getModelCalls;
            return $this->model;
        }
        protected function getTask(): string { return $this->task; }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}
namespace Joomla\CMS\MVC\Model {
    class AdminModel {
        protected string $error = '';
        public function setError(string $message): void { $this->error = $message; }
        public function getError(): string { return $this->error; }
        protected function loadForm(string $name, string $type, array $options): mixed { return null; }
    }
    class FormModel {
        protected function getDatabase(): object { return $GLOBALS['quoteTestSiteDb']; }
    }
}
namespace Joomla\Filesystem {
    class File {
        public static array $writes = [];
        public static function write(string $path, string $data): bool {
            self::$writes[] = $path;
            return file_put_contents($path, $data) === strlen($data);
        }
    }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed {
            return $this->values[$key] ?? $default;
        }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class SportsManagementAdministratorApplicationResolver {
        public static function resolve(): \Joomla\CMS\Application\AdministratorApplication {
            return $GLOBALS['quoteTestAdminApp'];
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementSiteApplicationResolver {
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface {
            return $GLOBALS['quoteTestSiteApp'];
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $base = dirname(__DIR__, 2);
    define('JPATH_ADMINISTRATOR', $base . '/admin');
    define('JPATH_SITE', sys_get_temp_dir() . '/jsm-source-editor-' . getmypid());

    require_once $base . '/admin/src/Controller/SmquotetxtController.php';
    require_once $base . '/admin/src/Model/SmquotetxtModel.php';
    require_once $base . '/site/src/Model/UpdsportsmanagementModel.php';
    require_once $base . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php';

    $root = JPATH_SITE . '/modules/mod_sportsmanagement_rquotes';
    if (!is_dir($root) && !mkdir($root, 0700, true) && !is_dir($root)) {
        throw new \RuntimeException('Failed to create test sandbox.');
    }
    file_put_contents($root . '/quotes.txt', "old quote\n");
    $outside = JPATH_SITE . '/outside.txt';
    file_put_contents($outside, "private\n");
    symlink($outside, $root . '/linked.txt');
    $GLOBALS['quoteTestAdminApp'] = new \Joomla\CMS\Application\AdministratorApplication();
    $app = $GLOBALS['quoteTestAdminApp'];
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotetxtModel();
    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\SmquotetxtController();
    $controller->setApp($app);
    $controller->model = $model;
    $app->form = ['filename' => 'quotes.txt', 'source' => "new quote\n"];

    try {
        $controller->save();
        throw new \RuntimeException('Non-admin source edit passed the controller ACL.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    if ($controller->getModelCalls !== 0
        || file_get_contents($root . '/quotes.txt') !== "old quote\n") {
        throw new \RuntimeException('Unauthorized source-editor request touched the model.');
    }

    // Model calls can bypass a specific controller; enforce ACL there too.
    if ($model->save(['filename' => 'quotes.txt', 'source' => 'unauthorized']) !== false) {
        throw new \RuntimeException('Quote source model accepted unauthorised writes.');
    }
    $app->identity->allowed = true;
    $controller->tokenValid = false;
    try {
        $controller->save();
        throw new \RuntimeException('Missing CSRF token was accepted.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    $controller->tokenValid = true;
    $controller->save();
    if (file_get_contents($root . '/quotes.txt') !== "new quote\n"
        || ($controller->redirect['type'] ?? '') !== 'message') {
        throw new \RuntimeException('Authorized quote source edit no longer works.');
    }
    $app->form = ['filename' => ['quotes.txt'], 'source' => "invalid\n"];
    $controller->save();
    if (($controller->redirect['type'] ?? '') !== 'error') {
        throw new \RuntimeException('Malformed source filename caused no error.');
    }
    $controller->model = false;
    $controller->save();
    if (($controller->redirect['type'] ?? '') !== 'error') {
        throw new \RuntimeException('Missing Joomla source-editor model was not handled.');
    }

    foreach ([
        ['filename' => 'linked.txt', 'source' => 'bad'],
        ['filename' => 'created.php', 'source' => '<?php die();'],
        ['filename' => '../outside.txt', 'source' => 'bad'],
        ['filename' => 'quotes.txt', 'source' => str_repeat('A', 1048577)],
        ['filename' => 'quotes.txt', 'source' => ['invalid']],
        ['filename' => ['quotes.txt'], 'source' => 'invalid'],
    ] as $data) {
        if ($model->save($data) !== false) {
            throw new \RuntimeException('Unsafe source-editor file or content was accepted.');
        }
    }
    if (file_get_contents($outside) !== "private\n") {
        throw new \RuntimeException('Symlink target outside module was changed.');
    }

    $siteDb = new class {
        public int $writes = 0;
        public ?object $last = null;
        public function updateObject(string $table, object $data, string $key): bool {
            ++$this->writes;
            $this->last = $data;
            return true;
        }
    };
    $GLOBALS['quoteTestSiteDb'] = $siteDb;
    $GLOBALS['quoteTestSiteApp'] = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public bool $allowed = false;
        public function getIdentity(): object {
            return new class($this) {
                public function __construct(private object $app) {}
                public function authorise(string $action, string $asset): bool {
                    return $this->app->allowed
                        && $action === 'core.edit'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
    };
    $site = new \Diddipoeler\Component\SportsManagement\Site\Model\UpdsportsmanagementModel();
    if ($site->updItem(['id' => 5, 'greeting' => 'not authorised']) !== false
        || $siteDb->writes !== 0) {
        throw new \RuntimeException('Frontend update model accepted a direct unauthorized write.');
    }
    $GLOBALS['quoteTestSiteApp']->allowed = true;
    foreach ([
        ['id' => 5, 'greeting' => ['array']],
        ['id' => '5junk', 'greeting' => 'invalid'],
        ['id' => 5, 'greeting' => str_repeat('g', 4097)],
        ['id' => 5, 'greeting' => "zero\0byte"],
        ['id' => 5, 'greeting' => '  '],
    ] as $invalid) {
        if ($site->updItem($invalid) !== false || $siteDb->writes !== 0) {
            throw new \RuntimeException('Malformed frontend update reached the database.');
        }
    }
    if (!$site->updItem(['id' => 5, 'greeting' => ' welcome '])
        || $siteDb->writes !== 1
        || $siteDb->last->greeting !== 'welcome') {
        throw new \RuntimeException('Valid frontend update was not persisted.');
    }

    $module = new \Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper();
    $ids = new \ReflectionMethod($module, 'normaliseIds');
    if ($ids->invoke($module, ['2:my-team,3', '2', '4:slug']) !== [2, 3, 4]
        || $ids->invoke($module, [str_repeat('2', 4200), '9']) !== [9]
        || $ids->invoke($module, [implode(',', range(1, 150))]) !== range(1, 100)) {
        throw new \RuntimeException('Quote category IDs were not canonical or bounded.');
    }

    // An explicitly configured .php file or a symlink must never become
    // publicly displayed quote text.
    $line = new \ReflectionMethod($module, 'textLine');
    foreach (['../config.txt', 'module.php', ['invalid'], 'linked.txt'] as $name) {
        if ($line->invoke($module, new \Joomla\Registry\Registry(['filename' => $name]),
            new class implements \Joomla\CMS\Application\CMSApplicationInterface {}) !== '') {
            throw new \RuntimeException('Quote reader accepted an unsafe filename.');
        }
    }

    unlink($root . '/linked.txt');
    unlink($root . '/quotes.txt');
    unlink($outside);
    rmdir($root);
    rmdir(dirname($root));
    rmdir(JPATH_SITE);
    echo "Joomla 5/6 source editor/frontend updater/quote module guards OK\n";
}
