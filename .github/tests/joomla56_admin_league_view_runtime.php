<?php
/**
 * Verify Joomla 5/6 league editor error privacy and repeatable script assets.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Application {
    final class AdministratorApplication
    {
        public array $messages = [];

        /**
         * Confirm the active fake client is Joomla's administrator.
         *
         * @param string $client Joomla application client name.
         * @return bool Whether administrator access is active.
         */
        public function isClient(string $client): bool
        {
            return $client === 'administrator';
        }

        /**
         * Return an input collector for the league editor's view state.
         *
         * @return object Simple request-input implementation.
         */
        public function getInput(): object
        {
            return new class {
                /**
                 * Accept the editor's hide-main-menu request.
                 *
                 * @param string $name Joomla input state name.
                 * @param mixed $value Desired flag.
                 * @return void
                 */
                public function set(string $name, mixed $value): void {}
            };
        }

        /**
         * Record the safe warning displayed by the league editor.
         *
         * @param string $message Translated Joomla warning.
         * @param string $type Message severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    }
}

namespace Joomla\CMS\Form {
    class Form {}
    interface FormFactoryInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Emulate loading persisted extended form values.
         *
         * @param string $value Previously saved configuration.
         * @return void
         */
        public function loadString(string $value): void {}
    }
}

namespace Joomla\CMS\Document {
    final class HtmlDocument
    {
        /**
         * Create a Joomla document with an injected test asset registry.
         *
         * @param \SportsManagement\LeagueTests\FakeAssets $assets Shared asset manager.
         */
        public function __construct(private \SportsManagement\LeagueTests\FakeAssets $assets) {}

        /**
         * Return the same asset registry for repeated view renders.
         *
         * @return \SportsManagement\LeagueTests\FakeAssets The fake WebAssetManager.
         */
        public function getWebAssetManager(): \SportsManagement\LeagueTests\FakeAssets
        {
            return $this->assets;
        }
    }
}

namespace Joomla\CMS\MVC\View {
    class HtmlView
    {
        public array $responses = [];
        public mixed $document = null;

        /**
         * Provide the requested fake Joomla MVC model value.
         *
         * @param string $key Model state field name.
         * @return mixed Stored test value, when present.
         */
        public function get(string $key): mixed
        {
            return $this->responses[$key] ?? null;
        }

        /**
         * Return the optional document assigned to the view.
         *
         * @return mixed The current HTML document, if any.
         */
        public function getDocument(): mixed
        {
            return $this->document;
        }

        /**
         * Stand in for Joomla's final view rendering.
         *
         * @param mixed $tpl Optional layout.
         * @return void
         */
        public function display($tpl = null) {}
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return the untranslated generic Joomla error key.
         *
         * @param string $key Joomla translation key.
         * @return string Key used to assert no SQL error text reaches the user.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 'error';
        public const WARNING = 'warning';
        public static array $entries = [];

        /**
         * Capture a redacted Joomla log entry for inspection.
         *
         * @param string $message Message written by the league view.
         * @param string $level Joomla log severity.
         * @param string $category Component log category.
         * @return void
         */
        public static function add(string $message, string $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace SportsManagement\LeagueTests {
    final class FakeAssets
    {
        public array $registered = [];
        public array $used = [];

        /**
         * Report whether the editor JavaScript was registered already.
         *
         * @param string $kind Script or style asset type.
         * @param string $name Named Joomla web asset.
         * @return bool True if registered.
         */
        public function assetExists(string $kind, string $name): bool
        {
            return isset($this->registered[$kind . ':' . $name]);
        }

        /**
         * Track the first script registration, attributes and dependencies.
         *
         * @param string $name Joomla script identifier.
         * @param string $path Site-relative JavaScript file.
         * @param array $options Version and cache options.
         * @param array $attributes Script HTML attributes.
         * @param array $dependencies Joomla asset dependencies.
         * @return void
         */
        public function registerScript(string $name, string $path, array $options, array $attributes, array $dependencies): void
        {
            if ($this->assetExists('script', $name)) {
                throw new \RuntimeException('Duplicate league editor script registration.');
            }

            $this->registered['script:' . $name] = [$path, $options, $attributes, $dependencies];
        }

        /**
         * Track activation of the editor script and core validation script.
         *
         * @param string $name Joomla web asset name.
         * @return void
         */
        public function useScript(string $name): void
        {
            $this->used[] = $name;
        }
    }

    final class FakeContainer
    {
        public \Joomla\CMS\Application\AdministratorApplication $app;

        /**
         * Configure the fake administrator app used by the league editor.
         */
        public function __construct()
        {
            $this->app = new \Joomla\CMS\Application\AdministratorApplication();
        }

        /**
         * Return Joomla services, raising a fake internal SQL error for forms.
         *
         * @param string $id Requested service class.
         * @return object Application or form factory stub.
         */
        public function get(string $id): object
        {
            if ($id === \Joomla\CMS\Application\AdministratorApplication::class) {
                return $this->app;
            }

            if ($id === \Joomla\CMS\Form\FormFactoryInterface::class) {
                return new class {
                    /**
                     * Emulate a driver exception while creating an optional form.
                     *
                     * @param string $name Form ID.
                     * @param array $options Joomla form factory options.
                     * @return never Always raises an internal error.
                     */
                    public function createForm(string $name, array $options): never
                    {
                        throw new \RuntimeException('SQL SELECT secret_password FROM hidden_table');
                    }
                };
            }

            throw new \RuntimeException('Unexpected Joomla service ' . $id);
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static ?\SportsManagement\LeagueTests\FakeContainer $container = null;

        /**
         * Return the shared fake Joomla dependency injection container.
         *
         * @return \SportsManagement\LeagueTests\FakeContainer Service registry.
         */
        public static function getContainer(): \SportsManagement\LeagueTests\FakeContainer
        {
            return self::$container ?? throw new \RuntimeException('Container not prepared.');
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\View\League\HtmlView;
    use Joomla\CMS\Document\HtmlDocument;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Log\Log;
    use SportsManagement\LeagueTests\FakeAssets;
    use SportsManagement\LeagueTests\FakeContainer;

    define('_JEXEC', 1);

    $temp = sys_get_temp_dir() . '/jsm-league-' . bin2hex(random_bytes(6));
    define('JPATH_ADMINISTRATOR', $temp);

    $history = $temp . '/components/com_sportsmanagement/forms';
    if (!mkdir($history, 0700, true)
        || file_put_contents($history . '/leaguelogohistory.xml', '<form/>') === false) {
        throw new \RuntimeException('Could not create league test XML.');
    }

    $extended = $temp . '/extended.xml';
    file_put_contents($extended, '<form/>');

    Factory::$container = new FakeContainer();
    require dirname(__DIR__, 2) . '/admin/src/View/League/HtmlView.php';

    $view = new HtmlView();

    // The form factory throws an SQL-bearing exception. The user must see
    // only Joomla's generic warning, and logs may record the exception class.
    foreach ([
        ['loadExtendedForm', ['extended', $extended, '']],
        ['loadLogoHistoryForm', []],
    ] as [$name, $parameters]) {
        $result = (new \ReflectionMethod($view, $name))->invokeArgs($view, $parameters);
        if ($result !== null) {
            throw new \RuntimeException('Failed optional league form returned data.');
        }
    }

    foreach (Factory::$container->app->messages as [$message, $severity]) {
        if ($message !== 'JERROR_AN_ERROR_HAS_OCCURRED' || $severity !== 'warning') {
            throw new \RuntimeException('Private league form error was reflected to the user.');
        }
    }

    $logs = implode(' ', array_map(static fn (array $entry): string => $entry[0], Log::$entries));

    if (str_contains($logs, 'secret_password')
        || str_contains($logs, 'SELECT ')
        || count(Factory::$container->app->messages) !== 2
        || count(Log::$entries) !== 2) {
        throw new \RuntimeException('League optional form failure exposed private SQL or was not logged.');
    }

    // A model error list must also be redacted in the primary league editor.
    $view->responses = [
        'Form' => new \Joomla\CMS\Form\Form(),
        'Item' => (object) ['id' => 1],
        'State' => null,
        'Errors' => ['SQL SELECT internal_table WHERE auth_token=secret'],
    ];

    try {
        $view->display();
        throw new \RuntimeException('League model errors should fail the editor request.');
    } catch (\RuntimeException $exception) {
        if ($exception->getMessage() !== 'JERROR_AN_ERROR_HAS_OCCURRED') {
            throw new \RuntimeException('League model failure exposes internal error details.');
        }
    }

    $assets = new FakeAssets();
    $view->document = new HtmlDocument($assets);
    $register = new \ReflectionMethod($view, 'registerLeagueScript');
    $register->invoke($view);
    $register->invoke($view);

    $asset = 'com_sportsmanagement.admin.league';
    if ($assets->registered !== [
            'script:' . $asset => [
                'administrator/components/com_sportsmanagement/assets/js/league.js',
                ['version' => 'auto'], ['defer' => true], ['core', 'form.validate'],
            ],
        ] || $assets->used !== ['form.validate', $asset, 'form.validate', $asset]) {
        throw new \RuntimeException('League editor script was not reused safely.');
    }

    echo "League editor: private model/form errors and repeatable assets verified.\n";
}
