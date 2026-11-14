<?php
/**
 * Joomla 5/6 GitHub update controller and archive privacy regressions.
 *
 * Exercises the native installer without network access, downloads or writes
 * outside the isolated temporary symlink fixture.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    class AdministratorApplication
    {
        public ?object $identity = null;
        public array $state = [];
        public array $messages = [];

        /**
         * Report the administrator application client for Joomla's resolver.
         *
         * @param string $client Expected Joomla application name.
         * @return bool Whether the active test client matches.
         */
        public function isClient(string $client): bool { return $client === 'administrator'; }

        /**
         * Return the simulated guest, absent or administrator identity.
         *
         * @return object|null Identity set by the test fixture.
         */
        public function getIdentity(): ?object { return $this->identity; }

        /**
         * Store the just-extracted update directory in Joomla's user state.
         *
         * @param string $key State key.
         * @param mixed $value State value.
         * @return void
         */
        public function setUserState(string $key, mixed $value): void { $this->state[$key] = $value; }

        /**
         * Capture translated installation feedback without displaying it.
         *
         * @param string $message Feedback to show to the administrator.
         * @param string $type Joomla message severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }

        /**
         * Supply an isolated temporary directory to the installer.
         *
         * @param string $key Joomla application setting.
         * @return string Existing temporary directory.
         */
        public function get(string $key): string { return sys_get_temp_dir(); }
    }
}

namespace Joomla\CMS {
    class Factory
    {
        public static ?object $application = null;

        /**
         * Return the dependency-injection container facade for the test.
         *
         * @return object Container able to resolve the administrator app.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Resolve the simulated Joomla administrator application.
                 *
                 * @param string $type Required service class.
                 * @return object Application fixture instance.
                 */
                public function get(string $type): object { return \Joomla\CMS\Factory::$application; }
            };
        }
    }
}

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {}
}

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected object $app;
        public bool $tokenValid = true;
        public int $modelCalls = 0;
        public mixed $testModel = false;
        public array $redirect = [];

        /**
         * Inject the test application into the native controller.
         *
         * @param array $config Joomla controller settings.
         * @param mixed $factory MVC factory.
         * @param object|null $app Administrator application.
         * @param mixed $input Request input.
         */
        public function __construct($config = [], $factory = null, $app = null, $input = null)
        {
            $this->app = $app;
        }

        /**
         * Simulate validation of the Joomla administrator form token.
         *
         * @throws \RuntimeException When token validation fails.
         * @return void
         */
        public function checkToken(): void
        {
            if (!$this->tokenValid) { throw new \RuntimeException('Invalid form token', 403); }
        }

        /**
         * Count MVC model lookup attempts for authorization assertions.
         *
         * @param string $name Model name.
         * @param string $prefix Joomla MVC namespace.
         * @param array $config Model factory options.
         * @return mixed Configured test model.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            ++$this->modelCalls;

            return $this->testModel;
        }

        /**
         * Capture the controller's error or success redirect.
         *
         * @param string $url Destination route.
         * @param string $message Feedback.
         * @param string $type Message type.
         * @return void
         */
        public function setRedirect(string $url, string $message = '', string $type = ''): void
        {
            $this->redirect = compact('url', 'message', 'type');
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static mixed $url = '';

        /**
         * Provide the configured update URL as a Joomla Registry-like object.
         *
         * @param string $component Joomla component identifier.
         * @return object Test configuration container.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Return a deliberately malformed or ordinary update URL.
                 *
                 * @param string $key Configuration entry.
                 * @param mixed $default Fallback.
                 * @return mixed Configured value for the installer.
                 */
                public function get(string $key, mixed $default = ''): mixed
                {
                    return ComponentHelper::$url;
                }
            };
        }
    }
}

namespace Joomla\CMS\Router {
    final class Route
    {
        /**
         * Preserve the Joomla administrator URL in the isolated fixture.
         *
         * @param string $url Component installer route.
         * @param bool $xhtml Whether Joomla would escape URL ampersands.
         * @return string Unchanged route for controller redirect assertions.
         */
        public static function _(string $url, bool $xhtml = true): string
        {
            return $url;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Use the language key as a deterministic test translation.
         *
         * @param string $key Joomla language key.
         * @return string Same key.
         */
        public static function _(string $key): string { return $key; }

        /**
         * Leave localized installer status text readable in the test.
         *
         * @param string $key Message format.
         * @param mixed ...$arguments Format arguments.
         * @return string Test message.
         */
        public static function sprintf(string $key, mixed ...$arguments): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 400;
        public static array $entries = [];

        /**
         * Record sensitive archive failures in a private test log.
         *
         * @param string $message Internal diagnostic.
         * @param int $level Joomla log severity.
         * @param string $category Component logging category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Installer {
    final class InstallerHelper
    {
        public static int $downloads = 0;
        public static bool $failDownload = false;

        /**
         * Simulate a package download and optional confidential failure.
         *
         * @param string $url Validated archive location.
         * @return string Simulated ZIP basename on success.
         * @throws \RuntimeException If the fixture requests a failed download.
         */
        public static function downloadPackage(string $url): string
        {
            ++self::$downloads;

            if (self::$failDownload) {
                throw new \RuntimeException('download failed: token=internal-secret');
            }

            return 'sportsmanagement-test.zip';
        }
    }
}

namespace Joomla\Archive {
    final class Archive
    {
        /**
         * Accept temporary archive extraction options.
         *
         * @param array $options Joomla archive options.
         */
        public function __construct(array $options = []) {}

        /**
         * Simulate an extraction error containing confidential diagnostics.
         *
         * @param string $file Downloaded ZIP path.
         * @param string $destination Temporary extraction root.
         * @throws \RuntimeException Always, to test error isolation.
         * @return bool Never reached.
         */
        public function extract(string $file, string $destination): bool
        {
            throw new \RuntimeException('archive failed: password=internal-secret');
        }
    }
}

namespace Joomla\Filesystem {
    final class File
    {
        /**
         * Simulate a downloaded package present in Joomla's temp directory.
         *
         * @param string $path Local archive path.
         * @return bool True for the simulated archive.
         */
        public static function exists(string $path): bool { return true; }
    }

    final class Folder
    {
        /**
         * Ask the operating system whether an extraction folder exists.
         *
         * @param string $path Candidate extracted folder.
         * @return bool Whether the directory exists.
         */
        public static function exists(string $path): bool { return is_dir($path); }
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Model/GithubinstallModel.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/GithubinstallController.php';

    $app = new \Joomla\CMS\Application\AdministratorApplication();
    \Joomla\CMS\Factory::$application = $app;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\GithubinstallModel();
    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubinstallController(
        [], null, $app
    );

    $controller->testModel = $model;

    // No identity and a guest with a spuriously granted ACL must be denied.
    foreach ([null, 0] as $userId) {
        $app->identity = $userId === null ? null : new class($userId) {
            /**
             * Create an identity fixture with the supplied Joomla user ID.
             *
             * @param int $id User ID, zero for a guest.
             */
            public function __construct(public int $id) {}

            /**
             * Simulate an over-permissive ACL for a guest account.
             *
             * @param string $action Joomla action.
             * @param string $asset Component ACL asset.
             * @return bool Always grants for purposes of this guard test.
             */
            public function authorise(string $action, string $asset): bool { return true; }
        };

        $denied = false;

        try {
            $controller->CopyGithubLink();
        } catch (\RuntimeException $e) {
            $denied = $e->getCode() === 403;
        }

        if (!$denied || $controller->modelCalls !== 0
            || \Joomla\CMS\Installer\InstallerHelper::$downloads !== 0) {
            throw new \RuntimeException('Guest or missing identity reached the GitHub update model.');
        }
    }

    $app->identity = new class {
        public int $id = 23;

        /**
         * Authorize the test administrator to install component updates.
         *
         * @param string $action Joomla action.
         * @param string $asset Component ACL asset.
         * @return bool True for SportsManagement core.admin.
         */
        public function authorise(string $action, string $asset): bool
        {
            return $action === 'core.admin' && $asset === 'com_sportsmanagement';
        }
    };

    \Joomla\CMS\Component\ComponentHelper::$url = ['unexpected' => 'array'];
    $controller->CopyGithubLink();

    if (\Joomla\CMS\Installer\InstallerHelper::$downloads !== 0
        || $controller->redirect['type'] !== 'error') {
        throw new \RuntimeException('Malformed update URL triggered a package download.');
    }

    $app->state['com_sportsmanagement.github_update_dir'] = 'stale';
    $result = $model->CopyGithubLink(new \stdClass());

    if ($result !== false || $app->state['com_sportsmanagement.github_update_dir'] !== null) {
        throw new \RuntimeException('Invalid archive configuration reused a stale extraction.');
    }

    \Joomla\CMS\Component\ComponentHelper::$url = 'https://github.com/example/repo/archive.zip';
    $controller->CopyGithubLink();

    if (\Joomla\CMS\Installer\InstallerHelper::$downloads !== 1
        || count(\Joomla\CMS\Log\Log::$entries) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'RuntimeException')
        || str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'internal-secret')
        || $controller->redirect['type'] !== 'error') {
        throw new \RuntimeException('Archive extraction error was not safely logged or handled.');
    }

    foreach ($app->messages as [$message]) {
        if (str_contains($message, 'internal-secret')) {
            throw new \RuntimeException('Archive extraction error exposed sensitive details to the administrator.');
        }
    }

    \Joomla\CMS\Installer\InstallerHelper::$failDownload = true;
    $app->state['com_sportsmanagement.github_update_dir'] = 'stale';
    $downloadResult = $model->CopyGithubLink('https://github.com/example/repo/archive.zip');
    \Joomla\CMS\Installer\InstallerHelper::$failDownload = false;

    if ($downloadResult !== false
        || \Joomla\CMS\Installer\InstallerHelper::$downloads !== 2
        || $app->state['com_sportsmanagement.github_update_dir'] !== null
        || count(\Joomla\CMS\Log\Log::$entries) !== 2
        || !str_contains(\Joomla\CMS\Log\Log::$entries[1][0], 'RuntimeException')
        || str_contains(\Joomla\CMS\Log\Log::$entries[1][0], 'internal-secret')) {
        throw new \RuntimeException('Download failure did not clear stale state and redact sensitive diagnostics.');
    }

    $tmp = sys_get_temp_dir() . '/jsm-link-guard-' . getmypid();
    $real = $tmp . '/actual-archive';
    $link = $tmp . '/sportsmanagement-test';

    if (!mkdir($tmp) || !mkdir($real)) {
        throw new \RuntimeException('Could not create temporary link-guard test fixture.');
    }

    try {
        if (!symlink($real, $link)) {
            throw new \RuntimeException('Could not create an extracted-folder symlink test fixture.');
        }

        $lookup = new \ReflectionMethod($model, 'findExtractedDirectory');

        if ($lookup->invoke($model, $tmp, 'test.zip', 'https://example.test/test.zip') !== null) {
            throw new \RuntimeException('Installer accepted a symbolic link as an extracted archive.');
        }
    } finally {
        if (is_link($link)) { unlink($link); }
        rmdir($real);
        rmdir($tmp);
    }

    echo "Joomla 5/6 GitHub installer identity, URL and archive privacy validation OK\n";
}
