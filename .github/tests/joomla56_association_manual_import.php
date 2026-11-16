<?php
/**
 * Test safe manual import of configured Landesverbände in Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
    final class ParameterType {
        public const STRING = 'string';
        public const INTEGER = 'integer';
    }
}

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Joomla\CMS\Component {
    final class ComponentHelper {
        public static mixed $selected = [];
        /**
         * Provide the selected component country settings in the test process.
         *
         * @param string $component Requested extension identifier.
         * @return object Registry-compatible mock settings.
         */
        public static function getParams(string $component): object {
            return new class {
                /**
                 * Return the current country multi-selection without Joomla.
                 *
                 * @param string $name Requested component option key.
                 * @param mixed $default Default when no options are configured.
                 * @return mixed Current country options.
                 */
                public function get(string $name, mixed $default = []): mixed {
                    return $name === 'cfg_country_associations'
                        ? ComponentHelper::$selected : $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text {
        /**
         * Preserve Joomla translation keys for deterministic assertions.
         *
         * @param string $key Language key.
         * @return string Untranslated language key.
         */
        public static function _(string $key): string { return $key; }
        /**
         * Represent one translated notification with interpolation values.
         *
         * @param string $key Language key.
         * @param mixed ...$args Translation arguments.
         * @return string Stable placeholder notification.
         */
        public static function sprintf(string $key, mixed ...$args): string {
            return $key . ':' . implode(':', $args);
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log {
        public const ERROR = 1;
        public static array $entries = [];
        /**
         * Keep component diagnostics private within the isolated test.
         *
         * @param string $message Safe failure details.
         * @param int $level Log severity.
         * @param string $category Joomla component category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    /** Observe the model's real delegation without writing a database. */
    final class ConfiguredAssociationInstaller {
        public static array $counts = [];
        public static array $missing = [];
        public static bool $fail = false;
        public static array $received = [];
        /**
         * Accept the active model database for safe import delegation.
         *
         * @param \Joomla\Database\DatabaseInterface $db Administrator database.
         */
        public function __construct(\Joomla\Database\DatabaseInterface $db) {
            $GLOBALS['associationManualDatabaseAccepted'] =
                $db === $GLOBALS['associationManualDatabase'];
        }
        /**
         * Return simulated new rows and track the configured selections.
         *
         * @param mixed $selected Country options including previous values.
         * @return array<string,int> Newly installed country association counts.
         */
        public function installMissing(mixed $selected): array {
            self::$received[] = $selected;
            if (self::$fail) {
                throw new \RuntimeException('SQL password=never_show_me');
            }
            return self::$counts;
        }
        /**
         * Return country codes without packaged data for warning checks.
         *
         * @return array<int,string> Unavailable XML country codes.
         */
        public function getMissingSources(): array { return self::$missing; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {
        /**
         * Initialise Joomla's list-model double without a live MVC factory.
         *
         * @param array $config Model configuration.
         * @param \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null $factory Model factory.
         */
        public function __construct($config = [], ?\Joomla\CMS\MVC\Factory\MVCFactoryInterface $factory = null) {}
        /**
         * Use the same database in the controller and the imported model.
         *
         * @return \Joomla\Database\DatabaseInterface Stub database instance.
         */
        protected function getDatabase(): \Joomla\Database\DatabaseInterface {
            return $GLOBALS['associationManualDatabase'];
        }
        /**
         * Return a mock Joomla administrator application for notifications.
         *
         * @return object Mock app.
         */
        protected function administratorApplication(): object {
            return $GLOBALS['associationManualApplication'];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController {
        public bool $token = true;
        public bool $allowed = true;
        public ?string $redirect = null;
        public array $permissions = [];
        /**
         * Resolve the real association list model for controller delegation.
         *
         * @param string $name MVC model name.
         * @param string $prefix Administrator MVC namespace.
         * @param array $config Factory options.
         * @return object Current association list model.
         */
        public function getModel($name = '', $prefix = '', $config = []): object {
            return $GLOBALS['associationManualModel'];
        }
        /**
         * Enforce a valid POST token before the import model can be reached.
         *
         * @return void
         */
        protected function checkToken(): void {
            if (!$this->token) {
                throw new \RuntimeException('Missing form token', 403);
            }
        }
        /**
         * Reject administrators without core.create permission.
         *
         * @param string $action Required Joomla component action.
         * @return void
         */
        protected function assertWritePermission(string $action = 'core.edit'): void {
            $this->permissions[] = $action;
            if (!$this->allowed) {
                throw new \RuntimeException('Permission denied', 403);
            }
        }
        /**
         * Record the native Joomla redirect after successful import.
         *
         * @param string $url Joomla administrator list URL.
         * @return void
         */
        protected function setRedirect(string $url): void { $this->redirect = $url; }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/JlextassociationsModel.php';
    require_once $root . '/admin/src/Controller/JlextassociationsController.php';

    /**
     * Fail the manual association import regression with a focused message.
     *
     * @param bool $condition Whether the tested invariant holds.
     * @param string $message Failure detail.
     * @return void
     */
    function assertAssociationImport(bool $condition, string $message): void {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $db = new class implements \Joomla\Database\DatabaseInterface {};
    $GLOBALS['associationManualDatabase'] = $db;
    $GLOBALS['associationManualApplication'] = new class {
        public array $messages = [];
        /**
         * Capture only administrator-visible notification text and type.
         *
         * @param string $text User-facing message text.
         * @param string $type Joomla message severity.
         * @return void
         */
        public function enqueueMessage(string $text, string $type): void {
            $this->messages[] = [$text, $type];
        }
    };

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\JlextassociationsModel();
    $GLOBALS['associationManualModel'] = $model;
    $app = $GLOBALS['associationManualApplication'];
    $service = \Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredAssociationInstaller::class;

    // Old destructive behaviour must not be reachable: the double DB has no
    // delete, update, transaction or query methods at all. Only service
    // delegation is permitted for manual imports.
    \Joomla\CMS\Component\ComponentHelper::$selected = ['DEU', 'AUT', 'USA'];
    $service::$counts = ['DEU' => 431, 'USA' => 1];
    $service::$missing = ['AUT'];
    $summary = $model->checkAssociations();
    assertAssociationImport($summary === [
        'inserted' => 432, 'updated' => 0, 'deleted' => 0,
        'missing' => ['AUT'], 'failed' => false,
    ], 'Manual import did not preserve non-destructive summary counts.');
    assertAssociationImport($GLOBALS['associationManualDatabaseAccepted'] === true
        && $service::$received[0] === ['DEU', 'AUT', 'USA'],
        'Import did not use the saved configured country selection and model database.');
    assertAssociationImport($app->messages === [
        ['COM_SPORTSMANAGEMENT_ASSOCIATIONS_IMPORT_INSTALLED:432:2', 'message'],
        ['COM_SPORTSMANAGEMENT_ASSOCIATIONS_IMPORT_XML_MISSING:AUT', 'warning'],
    ], 'Manual import did not emit translated result and missing XML messages.');

    // Empty selection and existing countries must not produce side effects.
    \Joomla\CMS\Component\ComponentHelper::$selected = [];
    $service::$counts = [];
    $service::$missing = [];
    $app->messages = [];
    assertAssociationImport($model->checkAssociations() === [
        'inserted' => 0, 'updated' => 0, 'deleted' => 0,
        'missing' => [], 'failed' => false,
    ] && $app->messages === [],
        'Empty or already populated country configuration should be a harmless no-op.');

    // Import diagnostics remain in the private Joomla log.
    $service::$fail = true;
    $summary = $model->checkAssociations();
    assertAssociationImport($summary['failed'] === true
        && $summary['deleted'] === 0
        && end($app->messages) === ['JERROR_AN_ERROR_HAS_OCCURRED', 'warning'],
        'SQL failure should not expose private credentials to administrators.');
    assertAssociationImport(count(\Joomla\CMS\Log\Log::$entries) === 1
        && !str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'password')
        && \Joomla\CMS\Log\Log::$entries[0][2] === 'com_sportsmanagement',
        'Private association import exception details leaked into logs or messages.');
    $service::$fail = false;

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextassociationsController();
    $before = count($service::$received);
    $controller->token = false;
    try {
        $controller->import();
        throw new \RuntimeException('Missing form token incorrectly accepted.');
    } catch (\RuntimeException $exception) {
        assertAssociationImport($exception->getCode() === 403,
            'Import must reject missing form tokens.');
    }
    assertAssociationImport(count($service::$received) === $before,
        'CSRF rejection still invoked the importer.');

    $controller->token = true;
    $controller->allowed = false;
    try {
        $controller->import();
        throw new \RuntimeException('Unauthorized import incorrectly accepted.');
    } catch (\RuntimeException $exception) {
        assertAssociationImport($exception->getCode() === 403,
            'Import must enforce write permission.');
    }
    assertAssociationImport(count($service::$received) === $before
        && in_array('core.create', $controller->permissions, true),
        'ACL rejection still invoked the importer or accepted the wrong permission.');

    $controller->allowed = true;
    $controller->import();
    assertAssociationImport(count($service::$received) === $before + 1
        && $controller->redirect === 'index.php?option=com_sportsmanagement&view=jlextassociations',
        'Authorized manual import failed to delegate and redirect.');

    echo "Joomla 5/6 safe manual association import tests passed.\n";
}
