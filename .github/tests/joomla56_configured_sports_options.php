<?php
/**
 * Test Joomla 5/6 sport, quote and association installs from component options.
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
        public static function sprintf(string $key, int|string ...$counts): string { return $key . ':' . implode(':', $counts); }
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
namespace Joomla\CMS\Filter {
    final class OutputFilter {
        /** Normalise an association name into a URL-safe alias for the importer. */
        public static function stringURLSafe(string $value): string {
            return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '', '-'));
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
    $associationXmlDirectory = $temporary . '/components/com_sportsmanagement/helpers/xml_files';
    $quoteXmlDirectory = $temporary . '/components/com_sportsmanagement/helpers/xml_files';
    if (!is_dir($quoteXmlDirectory) && !mkdir($quoteXmlDirectory, 0777, true)) {
        throw new \RuntimeException('Temporary quote XML directory cannot be created.');
    }
    foreach (['player', 'staff', 'functionaries', 'reporter', 'others'] as $source) {
        $original = $root . '/admin/helpers/xml_files/quote_' . $source . '.xml';
        sportCheck(is_file($original), 'Bundled quote XML fixture is missing: ' . $source);
        copy($original, $quoteXmlDirectory . '/quote_' . $source . '.xml');
    }

    foreach (['DEU', 'AUT', 'NZL', 'USA'] as $country) {
        $original = $root . '/admin/helpers/xml_files/associations_' . $country . '.xml';
        sportCheck(is_file($original), 'Bundled association XML fixture is missing: ' . $country);
        copy($original, $quoteXmlDirectory . '/associations_' . $country . '.xml');
    }

    require_once $root . '/admin/src/Service/ConfiguredSportTypeInstaller.php';
    require_once $root . '/admin/src/Service/ConfiguredQuoteInstaller.php';
    require_once $root . '/admin/src/Service/ConfiguredAssociationInstaller.php';
    require_once $root . '/plugins/jsm_sportbootstrap/src/Extension/SportsmanagementSportbootstrap.php';

    final class SportQuery {
        public string $name = '';
        public ?int $dailyNumber = null;
        public ?string $associationCountry = null;
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
            if ($key === ':configuredAssociationCountry' && is_string($value)
                && $type === 'string') {
                $this->associationCountry = $value;
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
        public array $associations = [];
        public int $associationInserts = 0;
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
            if ($this->query->associationCountry !== null) {
                return count($this->associations[$this->query->associationCountry] ?? []);
            }
            return $this->rows[$this->query->name] ?? null;
        }
        /** Insert only unique sport types into the Joomla table. */
        public function insertObject(string $table, object $record): bool {
            if (!in_array($table, ['#__sportsmanagement_sports_type', '#__sportsmanagement_rquote',
                '#__sportsmanagement_associations'], true)) {
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
            if ($table === '#__sportsmanagement_associations') {
                $record->id = ++$this->lastId;
                $this->associations[$record->country][] = clone $record;
                $this->associationInserts++;
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

    // Every accepted quote type must correspond exactly to the configured
    // choice and bundled XML file, with no arbitrary path from user input.
    $quoteChoices = [];
    foreach ($config->xpath('//field[@name="cfg_quotes"]/option') as $option) {
        $quoteChoices[(string) $option['value']] = true;
    }
    $quoteSources = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredQuoteInstaller::class
    ))->getReflectionConstant('QUOTE_SOURCES')->getValue();
    sportCheck(array_keys($quoteChoices) === array_keys($quoteSources),
        'Quote bootstrap whitelist differs from component options.');
    foreach ($quoteSources as $choice => [$type, $dailyNumber]) {
        sportCheck($choice === $type . ',' . $dailyNumber,
            'Quote source selection is not mapped to its daily_number category.');
    }

    $quoteInstaller = new \Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredQuoteInstaller($db);
    sportCheck($quoteInstaller->installMissing([null, [], false, 'player,2', '../secrets,9']) === [],
        'Malformed and unknown quote selections must not access the database.');
    sportCheck($db->quoteInserts === 0, 'Invalid selections inserted quotes.');

    // If one group already has user-managed quotes, it must never be reset.
    $preserved = (object) ['quote' => 'User-edited original quote', 'author' => 'Administrator',
        'notes' => 'do not overwrite', 'daily_number' => 3];
    $db->quotes[3] = [$preserved];
    $installedQuotes = $quoteInstaller->installMissing(
        ['player,1', 'functionaries,3', 'player,1', ['player,1']]
    );
    $xmlPlayer = simplexml_load_file($quoteXmlDirectory . '/quote_player.xml');
    $playerCount = count($xmlPlayer->quotes);
    sportCheck($installedQuotes === ['player,1' => $playerCount],
        'Only the missing player quote collection should be installed.');
    sportCheck($db->quoteInserts === $playerCount, 'Incorrect installed player quote count.');
    sportCheck($db->quotes[3] === [$preserved], 'Existing functionary quotes were modified.');
    sportCheck(
        $db->quotes[1][0]->quote === trim((string) $xmlPlayer->quotes[0]->quote)
        && $db->quotes[1][0]->author === (string) $xmlPlayer->quotes[0]->quote['author']
        && $db->quotes[1][0]->notes === (string) $xmlPlayer->quotes[0]->quote['notes'],
        'Quote text, author or notes were not imported from packaged XML.'
    );
    sportCheck($quoteInstaller->installMissing(['player,1']) === [],
        'Saving the same configured quotes twice must not duplicate them.');

    // A corrupt XML category must fail before *any* row is inserted.
    $reporterFile = $quoteXmlDirectory . '/quote_reporter.xml';
    $correctReporterXml = file_get_contents($reporterFile);
    file_put_contents($reporterFile,
        '<structur><quotes><quote daily_number="1" author="wrong">Mismatched category</quote></quotes></structur>');
    $beforeCorrupt = $db->quoteInserts;
    try {
        $quoteInstaller->installMissing(['reporter,4']);
        throw new \RuntimeException('Mismatched quote XML category was accepted.');
    } catch (\RuntimeException $exception) {
        sportCheck(str_contains($exception->getMessage(), 'category does not match'),
            'Wrong XML category must be rejected explicitly.');
    } finally {
        file_put_contents($reporterFile, $correctReporterXml);
    }
    sportCheck($db->quoteInserts === $beforeCorrupt,
        'Corrupt XML must never import a partial quote collection.');

    // Joomla's extension after-save event must install quotes even when
    // cfg_sport_types is empty, using the just-saved cfg_quotes values.
    $staffCount = count(simplexml_load_file($quoteXmlDirectory . '/quote_staff.xml')->quotes);
    $plugin->onExtensionAfterSave($saved(['cfg_quotes' => ['staff,2']]));
    sportCheck(count($db->quotes[2] ?? []) === $staffCount,
        'The Joomla option-save listener did not import staff quotes.');
    sportCheck(end($app->messages) === [
        'PLG_EXTENSION_JSM_SPORTBOOTSTRAP_QUOTES_INSTALLED:' . $staffCount . ':1', 'message'
    ], 'Wrong quote import administrator message.');
    $plugin->onExtensionAfterSave($saved(['cfg_quotes' => ['staff,2']]));
    sportCheck(count($db->quotes[2]) === $staffCount, 'Option resave duplicated staff quotes.');

    // Foreign components, frontend calls and users without admin rights
    // cannot import missing quote collections.
    $beforeDenied = $db->quoteInserts;
    $plugin->onExtensionAfterSave($saved(['cfg_quotes' => ['reporter,4']], 'com_content.article'));
    $plugin->onExtensionAfterSave($saved(['cfg_quotes' => ['reporter,4']], 'com_config.component', 'com_other'));
    $app->backend = false;
    $plugin->onExtensionAfterSave($saved(['cfg_quotes' => ['reporter,4']]));
    $app->backend = true;
    $identity->allowed = false;
    $plugin->onExtensionAfterSave($saved(['cfg_quotes' => ['reporter,4']]));
    $identity->allowed = true;
    sportCheck($db->quoteInserts === $beforeDenied,
        'Unauthorised or unrelated component option saves inserted quotes.');

    // Component update postflight must import collections saved *before* the
    // extension event listener was installed, without a configuration resave.
    \Joomla\CMS\Component\ComponentHelper::$params = new \Joomla\Registry\Registry(
        json_encode(['cfg_quotes' => ['others,5']])
    );
    $otherCount = count(simplexml_load_file($quoteXmlDirectory . '/quote_others.xml')->quotes);
    (new \ReflectionMethod($script, 'installConfiguredQuotes'))->invoke($script);
    sportCheck(count($db->quotes[5] ?? []) === $otherCount,
        'Installer postflight did not reconcile previously selected quotes.');
    (new \ReflectionMethod($script, 'installConfiguredQuotes'))->invoke($script);
    sportCheck(count($db->quotes[5]) === $otherCount,
        'Repeated component updates duplicated existing quotes.');

    // SQL failures must be logged privately and never expose credentials to
    // the administrator's warning message.
    $db->fail = true;
    $plugin->onExtensionAfterSave($saved(['cfg_quotes' => ['reporter,4']]));
    sportCheck(!isset($db->quotes[4]), 'Quote insert failure was reported as success.');
    sportCheck(end($app->messages) === ['JERROR_AN_ERROR_HAS_OCCURRED', 'warning'],
        'Private quote database exception was shown to the administrator.');
    sportCheck(count(\Joomla\CMS\Log\Log::$entries) === 2,
        'Quote import failure must produce one private log entry.');

    $reporterCount = count(simplexml_load_file($quoteXmlDirectory . '/quote_reporter.xml')->quotes);
    $plugin->onExtensionAfterSave($saved(['cfg_quotes' => ['reporter,4']]));
    sportCheck(count($db->quotes[4] ?? []) === $reporterCount,
        'Retry after SQL failure did not install reporter quote collection.');

    // Country selections are ISO alpha-3 values supplied by CountryField.
    // Reject all other values and never use them to construct filesystem paths.
    $associationInstaller = new \Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredAssociationInstaller($db);
    sportCheck($associationInstaller->installMissing([null, [], false, 'DEU/..', 'ZZ123', 'GER\\../../']) === [],
        'Malformed country codes must not be accepted.');
    sportCheck($db->associationInserts === 0, 'Invalid country selection inserted an association.');
    sportCheck($associationInstaller->getMissingSources() === [],
        'Invalid country codes must not be treated as missing source XML.');

    // A previously maintained country must remain untouched, even when a
    // bundled source exists for its country code.
    $manual = (object) ['id' => 994, 'country' => 'AUT', 'name' => 'User-managed association',
        'short_name' => 'Custom', 'parent_id' => 0];
    $db->associations['AUT'] = [$manual];
    $installedAssociations = $associationInstaller->installMissing(['AUT', 'XXX', 'XXX']);
    sportCheck($installedAssociations === [] && $db->associations['AUT'] === [$manual],
        'Manual association data must not be updated, deleted or duplicated.');
    sportCheck($associationInstaller->getMissingSources() === ['XXX'],
        'Unsupported selected country must be reported once.');

    // Malformed XML is validated before inserting the first country row.
    $nzlFile = $quoteXmlDirectory . '/associations_NZL.xml';
    $validNzlXml = file_get_contents($nzlFile);
    file_put_contents($nzlFile,
        '<structur><associations><assocname country="NZL" main="1" parentmain="0">Valid</assocname></associations>'
        . '<associations><assocname country="DEU" main="2" parentmain="1">Wrong country</assocname></associations></structur>'
    );
    $beforeInvalid = $db->associationInserts;
    try {
        $associationInstaller->installMissing(['NZL']);
        throw new \RuntimeException('The malformed XML country was incorrectly accepted.');
    } catch (\RuntimeException $exception) {
        sportCheck(str_contains($exception->getMessage(), 'invalid hierarchy'),
            'Malformed association XML must fail pre-validation.');
    } finally {
        file_put_contents($nzlFile, $validNzlXml);
    }
    sportCheck($db->associationInserts === $beforeInvalid,
        'XML validation failure caused partial association insertion.');

    // Component ownership, backend client and component-admin permission are
    // mandatory for automatic country bootstrap from the Joomla options save.
    $beforeDeniedAssociations = $db->associationInserts;
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['DEU']], 'com_content.article'));
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['DEU']], 'com_config.component', 'com_other'));
    $app->backend = false;
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['DEU']]));
    $app->backend = true;
    $identity->allowed = false;
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['DEU']]));
    $identity->allowed = true;
    sportCheck($db->associationInserts === $beforeDeniedAssociations,
        'Foreign, frontend or unauthorised option save created country associations.');

    // A save with only cfg_country_associations (no quotes/sports selected)
    // must install the German hierarchy, resolving parent ID references.
    $deuXml = simplexml_load_file($quoteXmlDirectory . '/associations_DEU.xml');
    $deuNames = [];
    foreach ($deuXml->associations as $item) {
        $deuNames[trim((string) $item->assocname)] = true;
    }
    $deuUniqueCount = count($deuNames);
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['DEU', 'AUT']]));
    sportCheck(count($db->associations['DEU'] ?? []) === $deuUniqueCount,
        'German associations were not imported from the saved options.');
    sportCheck($db->associations['AUT'] === [$manual],
        'Options save unexpectedly changed existing Austrian association data.');
    $byName = [];
    foreach ($db->associations['DEU'] as $item) {
        $byName[$item->name] = $item;
    }
    sportCheck(isset($byName['Norddeutscher Fußball-Verband (NFV)'])
        && isset($byName['Schleswig-Holsteinischer Fußballverband (SHFV)']),
        'Bundled German parent/child association names were not imported.');
    sportCheck(
        $byName['Schleswig-Holsteinischer Fußballverband (SHFV)']->parent_id
            === $byName['Norddeutscher Fußball-Verband (NFV)']->id,
        'German association child was not linked to its database parent ID.'
    );
    sportCheck(end($app->messages) === [
        'PLG_EXTENSION_JSM_SPORTBOOTSTRAP_ASSOCIATIONS_INSTALLED:' . $deuUniqueCount . ':1',
        'message',
    ], 'Wrong imported-association success feedback.');
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['DEU']]));
    sportCheck(count($db->associations['DEU']) === $deuUniqueCount,
        'Re-saving a country option duplicated imported associations.');

    // Unknown selected countries may lack packaged regional association XML.
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['XXX']]));
    sportCheck(end($app->messages) === [
        'PLG_EXTENSION_JSM_SPORTBOOTSTRAP_ASSOCIATIONS_XML_MISSING:XXX',
        'warning',
    ], 'Unavailable country XML should produce a translated warning.');

    // Updates must also reconcile selections saved before the options plugin
    // was installed, without an explicit administrator configuration save.
    \Joomla\CMS\Component\ComponentHelper::$params = new \Joomla\Registry\Registry(
        json_encode(['cfg_country_associations' => ['NZL']])
    );
    $nzlCount = count(simplexml_load_file($nzlFile)->associations);
    (new \ReflectionMethod($script, 'installConfiguredAssociations'))->invoke($script);
    sportCheck(count($db->associations['NZL'] ?? []) === $nzlCount,
        'Installer postflight did not install preselected New Zealand associations.');
    (new \ReflectionMethod($script, 'installConfiguredAssociations'))->invoke($script);
    sportCheck(count($db->associations['NZL']) === $nzlCount,
        'Repeated component upgrade inserted duplicate country associations.');

    // A failed SQL insert must not leak private connection details to the UI.
    $db->fail = true;
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['USA']]));
    sportCheck(!isset($db->associations['USA']),
        'Failed USA association import should not report an installed row.');
    sportCheck(end($app->messages) === ['JERROR_AN_ERROR_HAS_OCCURRED', 'warning'],
        'Association SQL exception leaked to the backend notification.');
    sportCheck(count(\Joomla\CMS\Log\Log::$entries) === 3,
        'Country SQL import failure should be logged privately once.');
    $plugin->onExtensionAfterSave($saved(['cfg_country_associations' => ['USA']]));
    sportCheck(count($db->associations['USA'] ?? []) === 1,
        'A safe retry after the first SQL failure did not import USA associations.');

    foreach (['player', 'staff', 'functionaries', 'reporter', 'others'] as $source) {
        unlink($quoteXmlDirectory . '/quote_' . $source . '.xml');
    }
    foreach (['DEU', 'AUT', 'NZL', 'USA'] as $country) {
        unlink($quoteXmlDirectory . '/associations_' . $country . '.xml');
    }
    rmdir($quoteXmlDirectory);

    unlink($xmlDirectory . '/soccer.xml');
    rmdir($xmlDirectory);
    rmdir(dirname($xmlDirectory));
    rmdir(dirname(dirname($xmlDirectory)));
    rmdir(dirname(dirname(dirname($xmlDirectory))));
    rmdir($temporary);

    echo "Joomla 5/6 configured sports, quotes and country association installation OK\n";
}
