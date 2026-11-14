<?php
/**
 * Joomla 5/6 legacy administrator match save constructor and date regression.
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

namespace Joomla\CMS\Date {
    final class Date extends \DateTime
    {
        /**
         * Construct a Joomla-style date for fixture timezone assertions.
         *
         * @param string $datetime Stored SQL datetime or 'now'.
         */
        public function __construct(string $datetime = 'now')
        {
            parent::__construct($datetime, new \DateTimeZone('UTC'));
        }

        /**
         * Format a Joomla date as SQL DATETIME for the legacy model.
         *
         * @return string Standard SQL timestamp.
         */
        public function toSql(): string { return $this->format('Y-m-d H:i:s'); }
    }
}

namespace Joomla\CMS\Application {
    final class AdministratorApplication
    {
        public array $states = ['com_sportsmanagement.cfg_which_database' => 1];

        /**
         * Return the current fake Joomla input with saved project filters.
         *
         * @return object Nested fake Joomla request input.
         */
        public function getInput(): object { return $GLOBALS['jsmLegacyInput']; }

        /**
         * Read an administrator user-state setting or default.
         *
         * @param string $name Joomla namespaced state key.
         * @param mixed $default Fallback when not stored.
         * @return mixed State value or default.
         */
        public function getUserState(string $name, mixed $default = null): mixed
        {
            return $this->states[$name] ?? $default;
        }

        /**
         * Persist a Joomla administrator form state value.
         *
         * @param string $name Joomla namespaced state key.
         * @param mixed $value New state value.
         * @return void
         */
        public function setUserState(string $name, mixed $value): void
        {
            $this->states[$name] = $value;
        }

        /**
         * Supply a harmless document fixture for legacy list rendering.
         *
         * @return object Simulated HTML document.
         */
        public function getDocument(): object { return (object) []; }

        /**
         * Supply an authenticated identity for legacy model initialisation.
         *
         * @return object Test administrator user.
         */
        public function getIdentity(): object { return (object) ['id' => 7, 'username' => 'editor']; }

        /**
         * Return a simple Joomla configuration with debug mode disabled.
         *
         * @return object Config fixture with a get() accessor.
         */
        public function getConfig(): object
        {
            return new class {
                /**
                 * Report that no debug messages should be emitted.
                 *
                 * @param string $key Configuration option.
                 * @param mixed $default Unconfigured fallback.
                 * @return mixed Disabled debug option or caller fallback.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return $key === 'debug' ? false : $default;
                }
            };
        }

        /**
         * Verify the administrator client in the native bridge fixture.
         *
         * @param string $client Expected Joomla application client.
         * @return bool Whether it is the administrator client.
         */
        public function isClient(string $client): bool { return $client === 'administrator'; }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        /**
         * Return the singleton fake Joomla service container.
         *
         * @return object Container resolving current app or database service.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Resolve a simulated service without using static Joomla APIs.
                 *
                 * @param string $service Fully-qualified Joomla service name.
                 * @return object Application or database fixture.
                 */
                public function get(string $service): object
                {
                    if ($service === \Joomla\CMS\Application\AdministratorApplication::class) {
                        return $GLOBALS['jsmLegacyApp'];
                    }

                    if ($service === \Joomla\Database\DatabaseInterface::class) {
                        return $GLOBALS['jsmLegacyDatabase'];
                    }

                    throw new \RuntimeException('Unexpected Joomla service request');
                }
            };
        }

        /**
         * Create Joomla's native date facade for legacy model timestamps.
         *
         * @param mixed $value Optional timestamp to convert to date object.
         * @return \Joomla\CMS\Date\Date Joomla-compatible test date.
         */
        public static function getDate(mixed $value = 'now'): \Joomla\CMS\Date\Date
        {
            $date = is_int($value) ? '@' . $value : (string) $value;
            return new \Joomla\CMS\Date\Date($date);
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ALL = 999;
        public const NOTICE = 5;

        /**
         * Simulate registration of the legacy administrator message queue.
         *
         * @param array<string,mixed> $config Joomla logger configuration.
         * @param int $priority Joomla log severity mask.
         * @param array<int,string> $categories Legacy log category list.
         * @return void
         */
        public static function addLogger(array $config, int $priority, array $categories): void {}

        /**
         * Absorb optional Joomla legacy diagnostics in the isolated fixture.
         *
         * @param string $message Log message.
         * @param int $priority Joomla diagnostic severity.
         * @param string $category Joomla category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void {}
    }
}

namespace Joomla\CMS\MVC\Model {
    #[\AllowDynamicProperties]
    class BaseDatabaseModel
    {
        protected mixed $database = null;

        /**
         * Initialise a Joomla-style model without the full MVC factory.
         *
         * @param array<string,mixed> $config Model constructor settings.
         */
        public function __construct($config = []) {}

        /**
         * Attach a Joomla database driver to the fake model.
         *
         * @param object $database Database returned by the native resolver.
         * @return void
         */
        public function setDatabase(object $database): void { $this->database = $database; }

        /**
         * Expose the selected database to the constructor assertions.
         *
         * @return mixed Selected database driver.
         */
        public function getDatabase(): mixed { return $this->database; }
    }

    class AdminModel extends BaseDatabaseModel {}
    class ListModel extends BaseDatabaseModel {}
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Return the Joomla administrator app through the injected fixture.
         *
         * @return \Joomla\CMS\Application\AdministratorApplication Active app.
         */
        public static function resolve(): \Joomla\CMS\Application\AdministratorApplication
        {
            return $GLOBALS['jsmLegacyApp'];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class SportsManagementDatabaseResolver
    {
        public static int $selector = -1;

        /**
         * Record the database selector and return the selected fallback driver.
         *
         * @param mixed $selector Database choice from the administrator.
         * @param \Joomla\Database\DatabaseInterface|null $database Joomla driver.
         * @return \Joomla\Database\DatabaseInterface Resolved database.
         */
        public function resolve(
            mixed $selector = null,
            ?\Joomla\Database\DatabaseInterface $database = null
        ): \Joomla\Database\DatabaseInterface {
            self::$selector = (int) $selector;
            return $database;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', sys_get_temp_dir());

    $GLOBALS['jsmLegacyApp'] = new \Joomla\CMS\Application\AdministratorApplication();
    $GLOBALS['jsmLegacyDatabase'] = new class implements \Joomla\Database\DatabaseInterface {
        public int $queryCount = 0;

        /**
         * Create a fake Joomla query to satisfy legacy constructor fields.
         *
         * @return object Empty SQL builder fixture.
         */
        public function createQuery(): object
        {
            ++$this->queryCount;
            return new \stdClass();
        }
    };
    $GLOBALS['jsmLegacyInput'] = new class {
        public object $post;
        public array $values = [];

        /**
         * Install a Joomla POST array collector for the model constructor.
         */
        public function __construct()
        {
            $this->post = new class {
                /**
                 * Return empty POST data for constructor-only regression.
                 *
                 * @param array<string,mixed>|null $fields Optional field request.
                 * @return array<string,mixed> Empty input.
                 */
                public function getArray(?array $fields = null): array { return []; }
            };
        }

        /**
         * Provide the selected Joomla input parameter as an integer.
         *
         * @param string $key Field name.
         * @param int $default Fallback value.
         * @return int Configured project/database setting.
         */
        public function getInt(string $key, int $default = 0): int
        {
            return (int) ($this->values[$key] ?? $default);
        }

        /**
         * Supply current component and view selectors for the match editor.
         *
         * @param string $key Requested Joomla selector.
         * @param string $default Default selector.
         * @return string Simulated administrator component/view.
         */
        public function getCmd(string $key, string $default = ''): string
        {
            return match ($key) {
                'option' => 'com_sportsmanagement',
                'view' => 'match',
                default => $default,
            };
        }

        /**
         * Return a legacy Joomla form parameter with the given fallback.
         *
         * @param string $key Input parameter name.
         * @param mixed $default Default field value.
         * @param string $filter Optional Joomla input filter.
         * @return mixed Requested value.
         */
        public function get(string $key, mixed $default = null, string $filter = ''): mixed
        {
            return $this->values[$key] ?? $default;
        }

        /**
         * Store a legacy model project selection in the fake input.
         *
         * @param string $key Form field name.
         * @param mixed $value Form field value.
         * @return void
         */
        public function set(string $key, mixed $value): void { $this->values[$key] = $value; }

        /**
         * Return a legacy input value used only by optional debug rendering.
         *
         * @param string $key Field whose value is requested.
         * @return mixed Input data or empty string.
         */
        public function getVar(string $key): mixed { return ''; }
    };

    require dirname(__DIR__, 2) . '/admin/src/Helper/LegacyModelDatabaseHelper.php';
    require dirname(__DIR__, 2) . '/admin/libraries/sportsmanagement/model.php';
    require dirname(__DIR__, 2) . '/admin/src/Helper/SportsManagementDateHelper.php';
    require dirname(__DIR__, 2) . '/admin/models/match.php';

    // The legacy global sportsmanagementHelper is deliberately NOT loaded.
    foreach ([new \JSMModelAdmin(), new \JSMModelList(), new \JSMModelLegacy(), new \sportsmanagementModelMatch()] as $model) {
        if ($model->getDatabase() !== $GLOBALS['jsmLegacyDatabase']) {
            throw new \RuntimeException('Legacy model did not use the native Joomla database service.');
        }
    }

    if (\Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver::$selector !== 1
        || class_exists('sportsmanagementHelper', false)) {
        throw new \RuntimeException('Match model required missing global helper or ignored database selection.');
    }

    $dates = \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper::class;
    if ($dates::convertDate('15-11-2026', 0) !== '2026-11-15'
        || $dates::convertDate('2026-11-15', 1) !== '15-11-2026'
        || $dates::getTimestamp('2026-11-15 16:30:00') <= 0) {
        throw new \RuntimeException('Legacy match date conversion failed without global helper.');
    }

    $match = (object) ['match_date' => '2026-11-15 16:30:00', 'timezone' => 'Europe/Berlin'];
    $dates::convertMatchDateToTimezone($match);
    if (!$match->match_date instanceof \Joomla\CMS\Date\Date
        || $match->timezone !== 'Europe/Berlin') {
        throw new \RuntimeException('Legacy match timezone conversion needs the removed helper.');
    }

    $library = file_get_contents(dirname(__DIR__, 2) . '/admin/libraries/sportsmanagement/model.php');
    $matchSource = file_get_contents(dirname(__DIR__, 2) . '/admin/models/match.php');

    foreach ([
        'sportsmanagementHelper::getDBConnection(',
        'sportsmanagementHelper::convertDate(',
        'sportsmanagementHelper::getTimestamp(',
        'sportsmanagementHelper::recordActionLog(',
        'sportsmanagementHelper::saveExtraFields(',
    ] as $call) {
        if (str_contains($library, $call) || str_contains($matchSource, $call)) {
            throw new \RuntimeException('A removed global helper is still used by match saves: ' . $call);
        }
    }

    echo "Joomla 5/6 match edit legacy constructor and date helpers: OK\n";
}
