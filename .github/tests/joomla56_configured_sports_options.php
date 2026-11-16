<?php
/**
 * Test Joomla 5/6 sport and quote bootstrapping from component option saves.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType { public const STRING = 'string'; public const INTEGER = 'integer'; }
}
namespace Joomla\CMS\Event\Model {
    final class AfterSaveEvent {
        /** Return an argument from the simulated Joomla extension after-save event. */
        public function __construct(private array $args) {}
        /** Read the named config-save event argument from the test payload. */
        public function getArgument(string $name): mixed { return $this->args[$name] ?? null; }
    }
}
namespace Joomla\Event { interface SubscriberInterface {} }
namespace Joomla\CMS\Plugin {
    class CMSPlugin {
        public static ?object $app = null;
        /** Return the active administrator application for the plugin. */
        protected function getApplication(): object { return self::$app; }
    }
}
namespace Joomla\CMS {
    final class Factory {
        public static object $container;
        /** Resolve the Joomla dependency container supplying the sports database. */
        public static function getContainer(): object { return self::$container; }
        /** Return the current fake Joomla application for installer postflight. */
        public static function getApplication(): object { return \Joomla\CMS\Plugin\CMSPlugin::$app; }
    }
}
namespace Joomla\CMS\Language {
    final class Text {
        /** Format the count in the translated success message. */
        public static function sprintf(string $key, int ...$counts): string { return $key . ':' . implode(':', $counts); }
        /** Return the Joomla generic error text in the test environment. */
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public const WARNING = 2;
        public static array $entries = [];
        /** Capture backend-only diagnostics without rendering them. */
        public static function add(string $message, int $level, string $category): void {
            self::$entries[] = [$message, $level, $category];
        }
    }
}
namespace Joomla\CMS\Component {
    final class ComponentHelper {
        public static object $params;
        /** Return the existing persisted component options for installer postflight. */
        public static function getParams(string $component): object {
            if ($component !== 'com_sportsmanagement') {
                throw new \RuntimeException('Wrong component parameters requested.');
            }
            return self::$params;
        }
    }
}
namespace Joomla\CMS\Installer {
    interface InstallerScriptInterface {}
    class Installer {}
    class InstallerAdapter {}
}
namespace Joomla\Registry {
    final class Registry {
        private array $data;
        /** Decode the just-saved extension params into a test registry. */
        public function __construct(string $json) { $this->data = json_decode($json, true) ?: []; }
        /** Return an option value from the saved extension table. */
        public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    final class DatabasetoolModel {
        public array $seeds = [];
        /** Capture standard events and position installation requests. */
        public function addStandardForSportType($name, $id, $token, $update = 0): void {
            $this->seeds[] = [$name, $id, $token];
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $temporary = sys_get_temp_dir() . '/joomla56-configured-sports-' . getmypid();
    define('JPATH_ADMINISTRATOR', $temporary);
    $xmlDirectory = $temporary . '/components/com_sportsmanagement/helpers/sp_structur';
    if (!is_dir($xmlDirectory) && !mkdir($xmlDirectory, 0777, true)) {
        throw new \RuntimeException('Temporary sport XML directory cannot be created.');
    }
    file_put_contents($xmlDirectory . '/soccer.xml', '<sport />');
    $quoteXmlDirectory = $temporary . '/components/com_sportsmanagement/helpers/xml_files';
    if (!is_dir($quoteXmlDirectory) && !mkdir($quoteXmlDirectory, 0777, true)) {
        throw new \RuntimeException('Temporary quote XML directory cannot be created.');
    }
    foreach (['player', 'staff', 'functionaries', 'reporter', 'others'] as $source) {
        $original = $root . '/admin/helpers/xml_files/quote_' . $source . '.xml';
        sportCheck(is_file($original), 'Bundled quote XML fixture is missing: ' . $source);
        copy($original, $quoteXmlDirectory . '/quote_' . $source . '.xml');
    }

    require_once $root . '/admin/src/Service/ConfiguredSportTypeInstaller.php';
    require_once $root . '/admin/src/Service/ConfiguredQuoteInstaller.php';
    require_once $root . '/plugins/jsm_sportbootstrap/src/Extension/SportsmanagementSportbootstrap.php';

    final class SportQuery {
        public string $name = '';
        public ?int $dailyNumber = null;
        /** Retain the query while selecting the sport identifier. */
        public function select(string $field): self { return $this; }
        /** Retain the query while selecting the sports-type table. */
        public function from(string $table): self { return $this; }
        /** Retain the bound query condition for the sports name. */
        public function where(string $condition): self { return $this; }
        /** Confirm the SQL parameter is bound as a string, not interpolated. */
        public function bind(string $key, string|int $value, string $type): self {
            if ($key === ':quoteDailyNumber' && is_int($value) && $type === 'integer') {
                $this->dailyNumber = $value;
                return $this;
            }
            if ($key !== ':configuredSportName' || !is_string($value) || $type !== 'string') {
                throw new \RuntimeException('Sport or quote category was not safely bound.');
            }
            $this->name = $value;
            return $this;
        }
    }

    final class SportDb implements \Joomla\Database\DatabaseInterface {
        public array $rows = [];
        public array $quotes = [];
        public int $quoteInserts = 0;
        public int $lastId = 0;
        public int $inserts = 0;
        public bool $fail = false;
        private SportQuery $query;
        /** Create a query that tracks the bound sport name. */
        public function createQuery(): SportQuery { return new SportQuery(); }
        /** Return a harmless quoted-name stand-in for the query double. */
        public function quoteName(string $identifier): string { return $identifier; }
        /** Record the current query for the next loadResult call. */
        public function setQuery(SportQuery $query, int $start = 0, int $limit = 0): self {
            $this->query = $query;
            return $this;
        }
        /** Resolve the stored sport ID by its bound translation key. */
        public function loadResult(): ?int {
            if ($this->query->dailyNumber !== null) {
                return count($this->quotes[$this->query->dailyNumber] ?? []);
            }
            return $this->rows[$this->query->name] ?? null;
        }
        /** Insert only unique sport types into the Joomla table. */
        public function insertObject(string $table, object $record): bool {
            if (!in_array($table, ['#__sportsmanagement_sports_type', '#__sportsmanagement_rquote'], true)) {
                throw new \RuntimeException('Unexpected component table.');
            }
            if ($this->fail) {
                $this->fail = false;
                throw new \RuntimeException('secret-sql-connection-token');
            }
            if ($table === '#__sportsmanagement_rquote') {
                $this->quotes[$record->daily_number][] = clone $record;
                $this->quoteInserts++;
                return true;
            }
            if (isset($this->rows[$record->name])) {
                throw new \RuntimeException('Duplicate sport name.');
            }
            $record->id = ++$this->lastId;
            $this->rows[$record->name] = $record->id;
            $this->inserts++;
            return true;
        }
        /** Return the last generated sport row identifier. */
        public function insertid(): int { return $this->lastId; }
    }

    /** Fail the regression when a requested installation invariant is broken. */
    function sportCheck(bool $ok, string $error): void {
        if (!$ok) { throw new \RuntimeException($error); }
    }

    // Both the component and plugin manifests must ship the enabled listener.
    $package = simplexml_load_file($root . '/sportsmanagement.xml');
    $declared = $package->xpath('//plugins/plugin[@plugin="jsm_sportbootstrap"]');
    sportCheck(count($declared) === 1
        && (string) $declared[0]['group'] === 'extension'
        && (string) $declared[0]['element'] === 'jsm_sportbootstrap',
        'Sport bootstrap listener is not declared in the component package.');
    $pluginManifest = simplexml_load_file($root . '/plugins/jsm_sportbootstrap/jsm_sportbootstrap.xml');
    sportCheck((string) $pluginManifest['group'] === 'extension',
        'The configured sports plugin must subscribe as an extension plugin.');
    sportCheck(str_contains(file_get_contents($root . '/script.joomla56.php'),
        "setPluginEnabled('extension', 'jsm_sportbootstrap', true)"),
        'Installer does not enable the configured sports listener.');

    // The SQL whitelist must remain exactly aligned with selectable options.
    $options = [];
    $config = simplexml_load_file($root . '/admin/config.xml');
    foreach ($config->xpath('//field[@name="cfg_sport_types"]/option') as $option) {
        $options[(string) $option['value']] = (string) $option;
    }
    $names = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredSportTypeInstaller::class
    ))->getReflectionConstant('SPORT_NAMES')->getValue();
    sportCheck($options === $names, 'Available sports differ from admin/config.xml.');

    $db = new SportDb();
    $installer = new \Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredSportTypeInstaller($db);
    sportCheck($installer->installMissing([null, [], 77, 'invalid_sport']) === [], 'Invalid types were accepted.');
    $db->rows['COM_SPORTSMANAGEMENT_ST_FISTBALL'] = 33;
    $created = $installer->installMissing(['soccer', 'tablesoccer', 'fistball', 'skaterhockey', 'soccer']);
    sportCheck(count($created) === 3 && $db->inserts === 3, 'Incorrect first sport installation count.');
    sportCheck(isset($db->rows['COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY']), 'Configured sport alias was lost.');
    sportCheck(!isset($db->rows['COM_SPORTSMANAGEMENT_ST_FAUSTBALL']), 'Existing legacy fistball duplicated.');
    sportCheck($installer->installMissing(['soccer', 'tablesoccer']) === [], 'Re-saved sports were inserted twice.');

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel();
    $created = $installer->installMissing(['basketball', 'floorball'], static function ($name, $id, $token) use ($model): void {
        $model->addStandardForSportType($name, $id, $token);
    });
    sportCheck(count($created) === 2 && $model->seeds === [], 'Missing XML must not block base installations.');

    $identity = new class {
        public int $id = 7;
        public bool $allowed = true;
        /** Authorise only the component administrator permission. */
        public function authorise(string $action, string $asset): bool {
            return $this->allowed && $action === 'core.admin' && $asset === 'com_sportsmanagement';
        }
    };
    $app = new class($identity, $model) {
        public bool $backend = true;
        public array $messages = [];
        public function __construct(private object $identity, private object $model) {}
        /** Restrict the callback to the administrator client. */
        public function isClient(string $client): bool { return $this->backend && $client === 'administrator'; }
        /** Return the active logged-in identity for permissions. */
        public function getIdentity(): object { return $this->identity; }
        /** Return the native component model factory for optional standard data. */
        public function bootComponent(string $name): object {
            if ($name !== 'com_sportsmanagement') { throw new \RuntimeException('Wrong component.'); }
            return new class($this->model) {
                public function __construct(private object $model) {}
                /** Return a fake MVC model factory after component boot. */
                public function getMVCFactory(): object {
                    return new class($this->model) {
                        public function __construct(private object $model) {}
                        /** Return the native database tool model for standard sport XML. */
                        public function createModel(string $name, string $client, array $config): object {
                            if ($name !== 'Databasetool' || $client !== 'Administrator') {
                                throw new \RuntimeException('Wrong sports model.');
                            }
                            return $this->model;
                        }
                    };
                }
            };
        }
        /** Capture success or generic warning messages for the administrator. */
        public function enqueueMessage(string $message, string $type): void { $this->messages[] = [$message, $type]; }
        /** Provide Joomla's language service for plugin translations. */
        public function getLanguage(): object {
            return new class {
                /** Accept the plugin language loading operation. */
                public function load(string $name, string $path, ?string $tag = null, bool $reload = false): bool {
                    return $name === 'plg_extension_jsm_sportbootstrap';
                }
                /** Return the current language tag. */
                public function getTag(): string { return 'en-GB'; }
            };
        }
    };
    \Joomla\CMS\Plugin\CMSPlugin::$app = $app;
    \Joomla\CMS\Factory::$container = new class($db) {
        public function __construct(private SportDb $db) {}
        /** Resolve only the Joomla database interface from the container. */
        public function get(string $service): SportDb {
            if ($service !== \Joomla\Database\DatabaseInterface::class) {
                throw new \RuntimeException('Wrong database service.');
            }
            return $this->db;
        }
    };
    $plugin = new \Diddipoeler\Plugin\Extension\SportsmanagementSportbootstrap\Extension\SportsmanagementSportbootstrap();
    $saved = static function (array $params, string $context = 'com_config.component', string $element = 'com_sportsmanagement') {
        return new \Joomla\CMS\Event\Model\AfterSaveEvent([
            'context' => $context,
            'subject' => (object) ['element' => $element, 'params' => json_encode($params)],
        ]);
    };

    $count = $db->inserts;
    $plugin->onExtensionAfterSave($saved(['cfg_sport_types' => ['tennis']], 'com_content.article'));
    $plugin->onExtensionAfterSave($saved(['cfg_sport_types' => ['tennis']], 'com_config.component', 'com_other'));
    $app->backend = false;
    $plugin->onExtensionAfterSave($saved(['cfg_sport_types' => ['tennis']]));
    $app->backend = true;
    $identity->allowed = false;
    $plugin->onExtensionAfterSave($saved(['cfg_sport_types' => ['tennis']]));
    $identity->allowed = true;
    sportCheck($count === $db->inserts, 'Unrelated, frontend or unauthorised save wrote sports.');

    $plugin->onExtensionAfterSave($saved(['cfg_sport_types' => ['tennis', 'soccer'], 'install_standard_position' => '1']));
    sportCheck(isset($db->rows['COM_SPORTSMANAGEMENT_ST_TENNIS']), 'Newly saved tennis selection was not installed: ' . json_encode(\Joomla\CMS\Log\Log::$entries));
    sportCheck(count($model->seeds) === 0, 'Previously installed soccer was unexpectedly reseeded.');
    sportCheck(end($app->messages) === ['PLG_EXTENSION_JSM_SPORTBOOTSTRAP_INSTALLED:1', 'message'], 'Wrong success notification.');
    $plugin->onExtensionAfterSave($saved(['cfg_sport_types' => ['tennis']]));
    sportCheck($db->inserts === $count + 1, 'Repeated Joomla config save duplicated a sport.');

    // A newly selected sport with bundled XML receives its default records.
    unset($db->rows['COM_SPORTSMANAGEMENT_ST_SOCCER']);
    $plugin->onExtensionAfterSave($saved([
        'cfg_sport_types' => ['soccer'],
        'install_standard_position' => '1',
    ]));
    sportCheck(count($model->seeds) === 1 && $model->seeds[0][2] === 'soccer',
        'Newly installed XML sport should receive standard position and event data.');

    // Component updates also reconcile options which were saved before the
    // extension listener existed, without making dashboard reads mutate data.
    \Joomla\CMS\Component\ComponentHelper::$params = new \Joomla\Registry\Registry(
        json_encode(['cfg_sport_types' => ['handball'], 'install_standard_position' => '0'])
    );
    require_once $root . '/script.joomla56.php';
    $script = new \com_sportsmanagementInstallerScript();
    (new \ReflectionMethod($script, 'installConfiguredSportTypes'))->invoke($script);
    sportCheck(isset($db->rows['COM_SPORTSMANAGEMENT_ST_HANDBALL']),
        'Existing saved sport options were not reconciled during component update.');

    $db->fail = true;
    $plugin->onExtensionAfterSave($saved(['cfg_sport_types' => ['baseball']]));
    sportCheck(!isset($db->rows['COM_SPORTSMANAGEMENT_ST_BASEBALL']), 'Failed insert was reported as success.');
    sportCheck(end($app->messages) === ['JERROR_AN_ERROR_HAS_OCCURRED', 'warning'], 'Private SQL error was shown to user.');
    sportCheck(count(\Joomla\CMS\Log\Log::$entries) === 1, 'Private SQL failure was not logged.');

    unlink($xmlDirectory . '/soccer.xml');
    rmdir($xmlDirectory);
    rmdir(dirname($xmlDirectory));
    rmdir(dirname(dirname($xmlDirectory)));
    rmdir(dirname(dirname(dirname($xmlDirectory))));
    rmdir($temporary);

    echo "Joomla 5/6 configuration sport type installation OK\n";
}
