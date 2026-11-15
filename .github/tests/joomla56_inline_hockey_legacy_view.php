<?php
/**
 * Isolated Joomla 5/6 compatibility checks for Inline Hockey extension views.
 *
 * Usage: php .github/tests/joomla56_inline_hockey_legacy_view.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS {
    final class Factory
    {
        public static object $container;

        /**
         * Return the fixture Joomla dependency-injection container.
         * @return object Shared test service container.
         */
        public static function getContainer(): object
        {
            return self::$container;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a stable language key for view notification checks.
         * @param string $key Requested extension translation.
         * @return string Unmodified fixture translation.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Toolbar {
    final class ToolbarHelper
    {
        /** @var list<string> */
        public static array $titles = [];
        /** @var list<string> */
        public static array $actions = [];

        /**
         * Record the administrator view title rather than rendering Joomla UI.
         * @param string $title Translated toolbar title.
         * @param string $icon Optional toolbar icon name.
         * @return void
         */
        public static function title(string $title, string $icon = ''): void
        {
            self::$titles[] = $title;
        }

        /**
         * Record a save/import task exposed by the legacy toolbar.
         * @param string $task Controller task name.
         * @param string $label Toolbar translation key.
         * @return void
         */
        public static function save(string $task, string $label): void
        {
            self::$actions[] = $task;
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class AdminController
    {
        /**
         * Require Joomla 5/6-compatible cache parameter arrays in the bridge.
         * @param bool $cachable Whether to cache the current view.
         * @param array $urlparams Cacheable Joomla URL parameters.
         * @return array{cachable:bool,params:array} Received values.
         */
        public function display($cachable = false, array $urlparams = []): array
        {
            return ['cachable' => (bool) $cachable, 'params' => $urlparams];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class InlineHockeyProjectService
    {
        public static int $calls = 0;
        public static int $lastProject = 0;

        /**
         * Accept the injected Joomla database without connecting remotely.
         * @param \Joomla\Database\DatabaseInterface $db Database service stub.
         */
        public function __construct(\Joomla\Database\DatabaseInterface $db)
        {
        }

        /**
         * Return a project link while recording lookup requests.
         * @param int $projectId Valid SportsManagement project ID.
         * @return string Fixture match URL for a selected project.
         */
        public function getMatchLink(int $projectId): string
        {
            ++self::$calls;
            self::$lastProject = $projectId;
            return 'https://example.test/matches/' . $projectId;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    /**
     * Record the legacy view's requested project identifier.
     */
    final class InlineHockeyViewTestInput
    {
        public int $projectId = 0;

        /**
         * Read an integer from the simulated Joomla request.
         * @param string $key Input field key.
         * @param int $default Fallback input value.
         * @return int Requested project ID.
         */
        public function getInt(string $key, int $default = 0): int
        {
            return $key === 'pid' ? $this->projectId : $default;
        }
    }

    /**
     * Record view messages and stored project state for migration checks.
     */
    final class InlineHockeyViewTestApplication
    {
        public int $savedProject = 0;
        /** @var list<string> */
        public array $messages = [];

        /**
         * Return the fixture project last selected by the current user.
         * @param string $key Joomla user-state key.
         * @param mixed $default Default state value.
         * @return int Persisted project identifier.
         */
        public function getUserState(string $key, mixed $default = null): int
        {
            return $this->savedProject;
        }

        /**
         * Record a Joomla warning or notice issued by the extension view.
         * @param string $message Translated message to enqueue.
         * @param string $type Message level.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = $message;
        }
    }

    /**
     * Minimal base view allowing extension rendering without a Joomla install.
     *
     * Exposes the same historic view variables used by the compatibility view
     * and simulates the selected layout for the migration regression.
     */
    class sportsmanagementView
    {
        public InlineHockeyViewTestInput $jinput;
        public InlineHockeyViewTestApplication $app;
        public string $option = 'com_sportsmanagement';
        public string $layout = 'default_4';

        /**
         * Return the active compatibility layout identifier.
         * @return string Currently selected legacy layout.
         */
        public function getLayout(): string
        {
            return $this->layout;
        }

        /**
         * Normalise the legacy layout to its Joomla 5/6 counterpart.
         * @param string $layout Supported native layout.
         * @return void
         */
        public function setLayout(string $layout): void
        {
            $this->layout = $layout;
        }
    }

    $root = dirname(__DIR__, 2);

    \Joomla\CMS\Factory::$container = new class {
        public int $requests = 0;

        /**
         * Resolve a fake typed Joomla database service for project lookups.
         * @param string $name Requested container service identifier.
         * @return \Joomla\Database\DatabaseInterface Database test double.
         */
        public function get(string $name): \Joomla\Database\DatabaseInterface
        {
            if ($name !== \Joomla\Database\DatabaseInterface::class) {
                throw new \RuntimeException('Unexpected service resolution.');
            }

            ++$this->requests;
            return new class implements \Joomla\Database\DatabaseInterface {};
        }
    };

    require_once $root . '/site/extensions/jsminlinehockey/admin/views/jsminlinehockey/view.html.php';
    require_once $root . '/site/extensions/jsminlinehockey/admin/controller.php';

    $view = new \sportsmanagementViewjsminlinehockey();
    $view->jinput = new InlineHockeyViewTestInput();
    $view->app = new InlineHockeyViewTestApplication();
    $view->init();

    if (($view->matchlink ?? null) !== ''
        || ($view->projectid ?? null) !== 0
        || $view->layout !== 'default'
        || count($view->app->messages) !== 1
        || \Joomla\CMS\Factory::$container->requests !== 0) {
        throw new \RuntimeException('No-project Inline Hockey view unexpectedly performed a database lookup.');
    }

    $view->jinput->projectId = 13;
    $view->init();

    if ($view->projectid !== 13
        || $view->matchlink !== 'https://example.test/matches/13'
        || \Joomla\CMS\Factory::$container->requests !== 1
        || \Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyProjectService::$lastProject !== 13) {
        throw new \RuntimeException('Selected project was not loaded through the native database service.');
    }

    $toolbar = new \ReflectionMethod($view, 'addToolbar');
    $toolbar->invoke($view);

    if (\Joomla\CMS\Toolbar\ToolbarHelper::$actions !== ['jsminlinehockey.getmatches']) {
        throw new \RuntimeException('Valid Inline Hockey project did not expose its import toolbar task.');
    }

    $controller = new \sportsmanagementController();
    if ($controller->display(false, false) !== ['cachable' => false, 'params' => []]
        || $controller->display(true, ['view' => 'jsminlinehockey']) !== ['cachable' => true, 'params' => ['view' => 'jsminlinehockey']]) {
        throw new \RuntimeException('Legacy Inline Hockey display arguments are not Joomla 5/6 compatible.');
    }

    echo "Joomla 5/6 Inline Hockey view and controller regressions passed.\n";
}
