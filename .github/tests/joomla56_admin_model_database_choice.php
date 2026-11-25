<?php
/**
 * Joomla 5/6 administrator form-model database choice and fallback regression.
 *
 * Exercises the actual shared administrator model with small Joomla doubles.
 * Invalid model selectors must not silently open the wrong database.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS\Application {
    class AdministratorApplication
    {
        public mixed $state = 0;

        /**
         * Inject a Joomla request with unfiltered component settings.
         *
         * @param object $input Fake Joomla request.
         */
        public function __construct(public object $input) {}

        /**
         * Provide the simulated Joomla input object.
         *
         * @return object Current administrator request.
         */
        public function getInput(): object { return $this->input; }

        /**
         * Read only the administrator database selector preference.
         *
         * @param string $name Joomla state key.
         * @param mixed $default State fallback.
         * @return mixed Stored database preference.
         */
        public function getUserState(string $name, mixed $default = null): mixed
        {
            if ($name !== 'com_sportsmanagement.cfg_which_database') {
                throw new \RuntimeException('Unexpected administrator state.');
            }

            return $this->state ?? $default;
        }
    }
}

namespace Joomla\CMS\MVC\Model {
    class AdminModel
    {
        public ?\Joomla\Database\DatabaseInterface $selectedDatabase = null;
        public int $databaseSets = 0;

        /**
         * Accept native Joomla model constructor dependencies.
         *
         * @param array $config Model settings.
         * @param mixed $factory MVC model factory.
         * @param mixed $formFactory Joomla form factory.
         */
        public function __construct($config = [], $factory = null, $formFactory = null) {}

        /**
         * Capture the actual database selected by the model.
         *
         * @param \Joomla\Database\DatabaseInterface $db Connection to bind.
         * @return void
         */
        public function setDatabase(\Joomla\Database\DatabaseInterface $db): void
        {
            $this->selectedDatabase = $db;
            ++$this->databaseSets;
        }
    }
}

namespace Joomla\CMS\MVC\Model {
    /** Reuse the database capture fixture for administrator list models. */
    class ListModel extends AdminModel {}
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    class SportsManagementAdministratorApplicationResolver
    {
        public static ?\Joomla\CMS\Application\AdministratorApplication $application = null;

        /**
         * Resolve an injected administrator application or simulate early boot.
         *
         * @return \Joomla\CMS\Application\AdministratorApplication Backend app.
         */
        public static function resolve(): \Joomla\CMS\Application\AdministratorApplication
        {
            if (self::$application === null) {
                throw new \RuntimeException('Application is not initialised.');
            }

            return self::$application;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    class SportsManagementDatabaseResolver
    {
        public static array $choices = [];
        public static bool $failConnection = false;
        public static \Joomla\Database\DatabaseInterface $external;

        /**
         * Observe validated 0/1 modes and optionally simulate failed connection.
         *
         * @param mixed $selector Submitted database mode.
         * @param \Joomla\Database\DatabaseInterface|null $fallback Joomla DB.
         * @return \Joomla\Database\DatabaseInterface Selected test database.
         */
        public function resolve(
            mixed $selector = null,
            ?\Joomla\Database\DatabaseInterface $fallback = null
        ): \Joomla\Database\DatabaseInterface {
            self::$choices[] = $selector;

            if (self::$failConnection) {
                throw new \RuntimeException('External database unavailable.');
            }

            return $selector === 1 ? self::$external : $fallback;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
    use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;
    use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
    use Joomla\CMS\Application\AdministratorApplication;
    use Joomla\Database\DatabaseInterface;

    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/SportsManagementAdminModel.php';

    require_once dirname(__DIR__, 2) . '/admin/src/Model/SportsManagementListModel.php';

    class DatabaseChoiceTestModel extends SportsManagementAdminModel {}
    class DatabaseChoiceTestListModel extends \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel {}

    final class DatabaseChoiceTestConnection implements DatabaseInterface {}

    final class DatabaseChoiceTestInput
    {
        public int $integerReads = 0;
        public int $rawReads = 0;

        /**
         * Keep the simulated request value types intact.
         *
         * @param array<string,mixed> $values Raw request fields.
         */
        public function __construct(public array $values = []) {}

        /**
         * Read a raw request field without Joomla integer coercion.
         *
         * @param string $name Input variable name.
         * @param mixed $default Fallback value.
         * @param string $filter Filter requested by the model.
         * @return mixed Exact submitted value.
         */
        public function get(string $name, mixed $default = null, string $filter = 'cmd'): mixed
        {
            if ($filter !== 'raw') {
                throw new \RuntimeException('The model must request raw database input.');
            }

            ++$this->rawReads;
            return array_key_exists($name, $this->values) ? $this->values[$name] : $default;
        }

        /**
         * Fail when the model tries the unsafe Joomla integer accessor.
         *
         * @param string $name Field name.
         * @param int $default Fallback integer.
         * @return int Unused integer fallback.
         */
        public function getInt(string $name, int $default = 0): int
        {
            ++$this->integerReads;
            throw new \RuntimeException('Lossy model getInt() call.');
        }
    }

    /**
     * Compare exact values independent of PHP assertion configuration.
     *
     * @param mixed $expected Required result.
     * @param mixed $actual Observed result.
     * @param string $context Failure explanation.
     * @return void
     */
    function modelChoiceAssert(mixed $expected, mixed $actual, string $context): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $context . ': ' . var_export($actual, true)
            );
        }
    }

    // The native base class is already loaded before its test subclass.
    $joomla = new DatabaseChoiceTestConnection();
    $external = new DatabaseChoiceTestConnection();
    SportsManagementDatabaseResolver::$external = $external;

    foreach ([[0, 0], [1, 1], ['0', 0], ['1', 1],
              ['0000', 0], ['0001', 1]] as [$value, $expected]) {
        $input = new DatabaseChoiceTestInput(['cfg_which_database' => $value]);
        SportsManagementAdministratorApplicationResolver::$application = new AdministratorApplication($input);
        SportsManagementDatabaseResolver::$choices = [];
        $model = new DatabaseChoiceTestModel();
        $model->setDatabase($joomla);

        modelChoiceAssert([$expected], SportsManagementDatabaseResolver::$choices,
            'Valid model selector was not honoured');
        modelChoiceAssert($expected === 1 ? $external : $joomla,
            $model->selectedDatabase, 'Wrong model database selected');
        modelChoiceAssert(1, $input->rawReads, 'Raw model selector was not read');
        modelChoiceAssert(0, $input->integerReads, 'Model used the lossy input getter');
    }

    foreach ([false, true, [], ['1'], new \stdClass(), 2, -1, '2',
              '1junk', ' 1', '1 ', '1.0', '', PHP_INT_MAX . '0',
              str_repeat('0', 65) . '1'] as $bad) {
        $input = new DatabaseChoiceTestInput(['cfg_which_database' => $bad]);
        SportsManagementAdministratorApplicationResolver::$application = new AdministratorApplication($input);
        SportsManagementDatabaseResolver::$choices = [];
        $model = new DatabaseChoiceTestModel();

        try {
            $model->setDatabase($joomla);
            throw new \RuntimeException('Malformed model selector was accepted.');
        } catch (\InvalidArgumentException $error) {
            modelChoiceAssert(400, $error->getCode(), 'Malformed model selector status');
        }

        modelChoiceAssert([], SportsManagementDatabaseResolver::$choices,
            'Invalid choice reached model database resolver');
        modelChoiceAssert(0, $model->databaseSets,
            'Invalid model database choice silently selected a connection');
    }

    // A missing request selector uses the valid stored administrator setting.
    foreach ([['0001', 1], [0, 0]] as [$saved, $expected]) {
        $app = new AdministratorApplication(new DatabaseChoiceTestInput());
        $app->state = $saved;
        SportsManagementAdministratorApplicationResolver::$application = $app;
        SportsManagementDatabaseResolver::$choices = [];
        $model = new DatabaseChoiceTestModel();
        $model->setDatabase($joomla);

        modelChoiceAssert([$expected], SportsManagementDatabaseResolver::$choices,
            'Stored model database preference was not parsed');
    }

    $app->state = ['1'];
    SportsManagementDatabaseResolver::$choices = [];
    try {
        (new DatabaseChoiceTestModel())->setDatabase($joomla);
        throw new \RuntimeException('Malformed saved preference was accepted.');
    } catch (\InvalidArgumentException $error) {
        modelChoiceAssert(400, $error->getCode(), 'Malformed saved state status');
    }
    modelChoiceAssert([], SportsManagementDatabaseResolver::$choices,
        'Malformed saved selector reached the resolver');

    // A failure to resolve an external DB must preserve the Joomla connection.
    $app->state = 1;
    SportsManagementDatabaseResolver::$failConnection = true;
    $model = new DatabaseChoiceTestModel();
    $model->setDatabase($joomla);
    modelChoiceAssert($joomla, $model->selectedDatabase,
        'External database failure lost the Joomla fallback');

    SportsManagementDatabaseResolver::$failConnection = false;
    SportsManagementAdministratorApplicationResolver::$application = null;
    $model = new DatabaseChoiceTestModel();
    $model->setDatabase($joomla);
    modelChoiceAssert($joomla, $model->selectedDatabase,
        'Model early construction without application lost Joomla fallback');

    foreach ([0, '0001', true, ['1'], '1junk', 2, str_repeat('0', 65) . '1'] as $raw) {
        $app = new AdministratorApplication(new DatabaseChoiceTestInput(['cfg_which_database' => $raw]));
        SportsManagementAdministratorApplicationResolver::$application = $app;
        SportsManagementDatabaseResolver::$choices = [];
        $list = new DatabaseChoiceTestListModel();
        $list->setDatabase($joomla);

        $expected = $raw === 0 ? [0] : ($raw === '0001' ? [1] : []);
        modelChoiceAssert($expected, SportsManagementDatabaseResolver::$choices,
            'List model database resolver received an invalid mode');
        modelChoiceAssert($raw === '0001' ? $external : $joomla,
            $list->selectedDatabase, 'List model selected the wrong database');
    }

    echo "Joomla 5/6 administrator model database mode checks: OK\n";
}
