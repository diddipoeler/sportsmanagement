<?php
/**
 * Verify strict database selection in the shared Joomla 5/6 administrator form controller.
 *
 * An isolated Joomla controller, input, application and database service
 * simulate opening editor forms without loading a full Joomla installation.
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

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Joomla\CMS\Form {
    interface FormFactoryInterface {}
}

namespace Joomla\CMS\Application {
    interface CMSWebApplicationInterface {}
}

namespace Joomla\Input {
    class Input
    {
        public int $integerReads = 0;

        /**
         * Preserve request input values exactly as submitted to Joomla.
         *
         * @param array<string,mixed> $values Simulated component request data.
         */
        public function __construct(public array $values = []) {}

        /**
         * Read a raw request value, including deliberately malformed values.
         *
         * @param string $name Requested input field.
         * @param mixed $default Value returned when no field is supplied.
         * @param string $filter Joomla input filter type, normally raw here.
         * @return mixed Uncoerced request parameter.
         */
        public function get(string $name, mixed $default = null, string $filter = 'cmd'): mixed
        {
            return array_key_exists($name, $this->values) ? $this->values[$name] : $default;
        }

        /**
         * Return the component option in the command format used by Joomla.
         *
         * @param string $name Name of the requested option.
         * @param string $default Fallback for missing input.
         * @return string Requested option or default.
         */
        public function getCmd(string $name, string $default = ''): string
        {
            return $this->values[$name] ?? $default;
        }

        /**
         * Track accidental use of the lossy integer input accessor.
         *
         * @param string $name Requested parameter name.
         * @param int $default Integer fallback.
         * @return int Coerced integer for test detection only.
         */
        public function getInt(string $name, int $default = 0): int
        {
            ++$this->integerReads;

            return (int) ($this->values[$name] ?? $default);
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class FormController
    {
        protected mixed $app = null;
        protected mixed $input = null;

        /**
         * Model Joomla's dependency-injected controller constructor.
         *
         * @param array $config MVC controller configuration.
         * @param mixed $factory Injected MVC factory.
         * @param mixed $app Injected Joomla application.
         * @param mixed $input Injected request input.
         * @param mixed $formFactory Injected form factory.
         */
        public function __construct(
            $config = [],
            $factory = null,
            $app = null,
            $input = null,
            $formFactory = null
        ) {
            $this->app = $app;
            $this->input = $input;
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static \Joomla\Database\DatabaseInterface $database;

        /**
         * Expose the Joomla database service through a minimal test container.
         *
         * @return object Container which resolves DatabaseInterface.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Look up the configured Joomla database stub.
                 *
                 * @param string $name Requested service class.
                 * @return \Joomla\Database\DatabaseInterface Database provided by the test.
                 */
                public function get(string $name): \Joomla\Database\DatabaseInterface
                {
                    return Factory::$database;
                }
            };
        }

        /**
         * Provide a non-null clock value for the legacy controller context.
         *
         * @return object Placeholder Joomla date object.
         */
        public static function getDate(): object
        {
            return new \stdClass();
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static array $calls = [];

        /**
         * Observe the form's validated database selector without making SQL calls.
         *
         * @param \Joomla\Database\DatabaseInterface $fallback Joomla database connection.
         * @param int $selector Validated component database choice.
         * @return \Joomla\Database\DatabaseInterface Unchanged fallback database.
         */
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector = 0
        ): \Joomla\Database\DatabaseInterface {
            self::$calls[] = $selector;

            return $fallback;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportsManagementFormController;
    use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
    use Joomla\CMS\Application\CMSWebApplicationInterface;
    use Joomla\CMS\Factory;
    use Joomla\Input\Input;

    define('_JEXEC', 1);

    final class FormSelectorTestDatabase implements \Joomla\Database\DatabaseInterface {}

    final class FormSelectorTestApplication implements CMSWebApplicationInterface
    {
        public array $state = [];

        /**
         * Supply administrator preferences to the form controller.
         *
         * @param string $name Joomla state namespace.
         * @param mixed $default Fallback for unset state.
         * @return mixed Stored administrator value, when available.
         */
        public function getUserState(string $name, mixed $default = null): mixed
        {
            return $this->state[$name] ?? $default;
        }

        /**
         * Stand in for Joomla's document dependency.
         *
         * @return object Simplified Joomla document.
         */
        public function getDocument(): object
        {
            return new \stdClass();
        }

        /**
         * Stand in for the logged-in administrator.
         *
         * @return object Simplified Joomla identity.
         */
        public function getIdentity(): object
        {
            return new \stdClass();
        }
    }

    /**
     * Compare two values strictly, regardless of PHP assertion settings.
     *
     * @param mixed $expected Expected result.
     * @param mixed $actual Actual result.
     * @param string $message Diagnostic context on failure.
     * @return void
     * @throws \RuntimeException When the observed value is unexpected.
     */
    function formSelectorAssertSame(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException($message
                . ': expected ' . var_export($expected, true)
                . ', observed ' . var_export($actual, true));
        }
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Controller/SportsManagementFormController.php';

    Factory::$database = new FormSelectorTestDatabase();

    $stateKey = 'com_sportsmanagement.cfg_which_database';

    foreach ([0 => 0, 1 => 1, '0000' => 0, '0001' => 1] as $value => $expected) {
        $app = new FormSelectorTestApplication();
        $input = new Input(['cfg_which_database' => (string) $value]);
        SportsManagementDatabaseResolver::$calls = [];
        new SportsManagementFormController([], null, $app, $input, null);
        formSelectorAssertSame([$expected], SportsManagementDatabaseResolver::$calls,
            'Valid form database selector was rejected or changed');
        formSelectorAssertSame(0, $input->integerReads, 'Form controller used lossy getInt()');
    }

    foreach ([false, true, -1, 2, '', '1junk', '1.0', ' 1', '1 ', ['1'],
              new \stdClass(), PHP_INT_MAX . '9'] as $bad) {
        $app = new FormSelectorTestApplication();
        $input = new Input(['cfg_which_database' => $bad]);
        SportsManagementDatabaseResolver::$calls = [];

        try {
            new SportsManagementFormController([], null, $app, $input, null);
            throw new \RuntimeException('Malformed form database selector was accepted');
        } catch (\InvalidArgumentException $e) {
            formSelectorAssertSame(400, $e->getCode(), 'Malformed form selector HTTP status');
        }

        formSelectorAssertSame([], SportsManagementDatabaseResolver::$calls,
            'Malformed form database selector reached the external resolver');
        formSelectorAssertSame(0, $input->integerReads, 'Malformed form selector used getInt()');
    }

    foreach (['0001' => 1, '0000' => 0] as $saved => $expected) {
        $app = new FormSelectorTestApplication();
        $app->state[$stateKey] = $saved;
        $input = new Input();
        SportsManagementDatabaseResolver::$calls = [];
        new SportsManagementFormController([], null, $app, $input, null);
        formSelectorAssertSame([$expected], SportsManagementDatabaseResolver::$calls,
            'Saved administrator form preference was not respected');
    }

    $app = new FormSelectorTestApplication();
    $app->state[$stateKey] = ['unsafe'];
    SportsManagementDatabaseResolver::$calls = [];
    try {
        new SportsManagementFormController([], null, $app, new Input(), null);
        throw new \RuntimeException('Malformed stored form selector was accepted');
    } catch (\InvalidArgumentException $e) {
        formSelectorAssertSame(400, $e->getCode(), 'Malformed stored selector HTTP status');
    }
    formSelectorAssertSame([], SportsManagementDatabaseResolver::$calls,
        'Malformed stored form selector reached the resolver');

    echo "Joomla 5/6 administrator form database selection passed.\n";
}
