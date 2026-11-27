<?php
/**
 * Joomla 5/6 installer for com_sportsmanagement.
 *
 * Keeps the SportsManagement package-specific module/plugin installation behaviour
 * while using the Joomla 5/6 installer and database APIs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredSportTypeInstaller;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredQuoteInstaller;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ConfiguredAssociationInstaller;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

final class com_sportsmanagementInstallerScript implements InstallerScriptInterface
{
    private string $release = '4.24.00';

    /**
     * Initialise the component installation lifecycle.
     * Reads the release version from the incoming manifest; bundled extension installation and media preparation are deferred to postflight.
     * @param InstallerAdapter $adapter Joomla component installation adapter.
     * @return bool True to allow installation to continue.
     */
    public function install(InstallerAdapter $adapter): bool
    {
        $this->syncReleaseFromManifest($adapter);

        return true;
    }

    /**
     * Perform the initial Joomla component update callback.
     * Uses the incoming manifest version for the update message and leaves bundled modules and plugins to the postflight stage.
     * @param InstallerAdapter $adapter Joomla component update adapter.
     * @return bool True to allow the update to continue.
     */
    public function update(InstallerAdapter $adapter): bool
    {
        $this->syncReleaseFromManifest($adapter);

        Factory::getApplication()->enqueueMessage(
            Text::_('COM_SPORTSMANAGEMENT_UPDATE_TEXT') . $this->release,
            'message'
        );

        return true;
    }

    /**
     * Clean up optional bundled modules and plugins during component removal.
     * Consults stored component options before uninstalling dependent extensions, and reports the uninstall result via Joomla messages.
     * @param InstallerAdapter $adapter Installer supplying the bundled extension manifest.
     * @return bool True after the configured cleanup steps complete.
     */
    public function uninstall(InstallerAdapter $adapter): bool
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');

        if ((int) $params->get('jsm_deinstall_module', 0) === 1) {
            $this->uninstallBundledExtensions($adapter, 'modules/module', 'module');
        }

        if ((int) $params->get('jsm_deinstall_plugin', 0) === 1) {
            $this->uninstallBundledExtensions($adapter, 'plugins/plugin', 'plugin');
        }

        Factory::getApplication()->enqueueMessage(
            Text::_('COM_SPORTSMANAGEMENT_UNINSTALL_TEXT'),
            'message'
        );

        return true;
    }

    /**
     * Check the upgrade version before Joomla mutates component files.
     * Prevents downgrading from a newer installed release, and displays the source version, target version and operation type.
     * @param string $type Installer operation such as install or update.
     * @param InstallerAdapter $adapter Incoming component installation adapter.
     * @return bool False only for a forbidden downgrade.
     */
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        $this->syncReleaseFromManifest($adapter);

        $app = Factory::getApplication();
        $currentVersion = $this->getInstalledVersion();

        if ($type === 'update' && $currentVersion !== '' && version_compare($currentVersion, $this->release, 'gt')) {
            $app->enqueueMessage(
                sprintf('SportsManagement %s cannot be downgraded to %s.', $currentVersion, $this->release),
                'error'
            );

            return false;
        }

        $app->enqueueMessage(
            sprintf('SportsManagement: %s → %s (%s)', $currentVersion ?: 'new install', $this->release, $type),
            'message'
        );

        return true;
    }

    /**
     * Finish the SportsManagement package installation or upgrade.
     * Installs bundled modules and plugins, enables the options listener, installs missing sports, quotes and associations already selected in saved settings, and prepares logging/media folders.
     * @param string $type Joomla installer operation.
     * @param InstallerAdapter $adapter Source manifest and extracted package paths.
     * @return bool True on success; false for a fatal package setup failure.
     */
    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        $this->syncReleaseFromManifest($adapter);

        if (!in_array($type, ['install', 'update', 'discover_install'], true)) {
            return true;
        }

        try {
            $this->installModules($adapter);
            $this->installPlugins($adapter);
            $this->installConfiguredSportTypes();
            $this->installConfiguredQuotes();
            $this->installConfiguredAssociations();
            $this->ensureActionLogConfiguration();
            $this->createImagesFolder();
            $this->deleteInstallFiles();
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            Factory::getApplication()->enqueueMessage(
                'SportsManagement installer: ' . $exception->getMessage(),
                'error'
            );

            return false;
        }

        Factory::getApplication()->enqueueMessage(
            Text::_('COM_SPORTSMANAGEMENT_POSTFLIGHT_' . $type . '_TEXT') . $this->release,
            'message'
        );

        return true;
    }

    /**
     * Resolve Joomla's main database through dependency injection.
     * The installer must use the same connection as the extension registry and the SportsManagement sports-type tables.
     * @return DatabaseInterface Active Joomla database connection.
     * @throws \RuntimeException If the container does not provide a database driver.
     */
    private function getDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement installer database service is unavailable.');
        }

        return $database;
    }

    /**
     * Create a Joomla extension installer for packaged dependencies.
     * Injects the shared database service so module and plugin installation use the parent Joomla installation context.
     * @return Installer Configured Joomla installer instance.
     */
    private function getInstaller(): Installer
    {
        $installer = new Installer();
        $installer->setDatabase($this->getDatabase());

        return $installer;
    }

    /**
     * Read the installed SportsManagement component release number.
     * Looks up the component entry in Joomla's extension registry using a native query.
     * Extracts a textual version from its JSON manifest cache and ignores malformed or non-string cache values.
     * @return string Existing component version or empty string.
     */
    private function getInstalledVersion(): string
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('manifest_cache'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_sportsmanagement'));
        $db->setQuery($query);
        $cache = $db->loadResult();
        if (!is_string($cache) || $cache === '') {
            return '';
        }

        $manifest = json_decode($cache, true);
        $version = is_array($manifest) ? ($manifest['version'] ?? null) : null;

        return is_string($version) ? trim($version) : '';
    }

    /**
     * Update the installer release label from the packaged manifest.
     * Keeps user-facing installation and upgrade messages aligned with the actual manifest version rather than a hard-coded fallback.
     * @param InstallerAdapter $adapter Source of the component manifest.
     * @return void
     */
    private function syncReleaseFromManifest(InstallerAdapter $adapter): void
    {
        $version = trim((string) ($adapter->getParent()->manifest->version ?? ''));

        if ($version !== '') {
            $this->release = $version;
        }
    }

    /**
     * Install bundled Joomla site and administrator modules.
     * Iterates the component manifest's module entries, installs extracted module packages and applies necessary administrator module assignments.
     * @param InstallerAdapter $adapter Component manifest and extracted source location.
     * @return void
     */
    private function installModules(InstallerAdapter $adapter): void
    {
        $source = $adapter->getParent()->getPath('source');
        $manifest = $adapter->getParent()->manifest;
        $modules = $manifest->xpath('modules/module') ?: [];
        $db = $this->getDatabase();

        foreach ($modules as $module) {
            $name = (string) $module['module'];
            $client = (string) $module['client'] ?: 'site';
            $clientId = $client === 'administrator' ? 1 : 0;
            $position = (string) $module['position'];
            $published = (string) $module['published'];
            $path = $clientId === 1
                ? $source . '/admin/modules/' . $name
                : $source . '/modules/' . $name;

            if (!is_dir($path)) {
                Factory::getApplication()->enqueueMessage('Module package not found: ' . $name, 'warning');
                continue;
            }

            try {
                $installer = $this->getInstaller();

                if (!$installer->install($path)) {
                    $this->logInstallerWarning('Module installation failed: ' . $name);
                    continue;
                }
            } catch (\Throwable $exception) {
                $this->logInstallerWarning(
                    sprintf('Module installation failed for %s: %s', $name, $exception->getMessage())
                );
                continue;
            }

            if ($position !== '') {
                $query = $db->createQuery()
                    ->update($db->quoteName('#__modules'))
                    ->set($db->quoteName('position') . ' = ' . $db->quote($position))
                    ->set($db->quoteName('ordering') . ' = ' . ($clientId === 1 ? 1 : 99))
                    ->set($db->quoteName('published') . ' = ' . (int) ($published !== '' ? $published : 0))
                    ->where($db->quoteName('module') . ' = ' . $db->quote($name))
                    ->where($db->quoteName('client_id') . ' = ' . $clientId);
                $db->setQuery($query)->execute();
            }

            if ($clientId === 1) {
                $this->ensureAdministratorModuleAssignment($name);
            }
        }
    }

    /**
     * Install and activate supported SportsManagement companion plugins.
     * Installs extension packages declared in the component manifest, records nonfatal failures and enables the mandatory registration and configured-sport listeners.
     * @param InstallerAdapter $adapter Manifest describing packaged plugin folders.
     * @return void
     */
    private function installPlugins(InstallerAdapter $adapter): void
    {
        $source = $adapter->getParent()->getPath('source');
        $manifest = $adapter->getParent()->manifest;
        $plugins = $manifest->xpath('plugins/plugin') ?: [];

        foreach ($plugins as $plugin) {
            $name = (string) $plugin['plugin'];
            $path = $source . '/plugins/' . $name;

            if (!is_dir($path)) {
                Factory::getApplication()->enqueueMessage('Plugin package not found: ' . $name, 'warning');
                continue;
            }

            try {
                $installer = $this->getInstaller();

                if (!$installer->install($path)) {
                    $this->logInstallerWarning('Plugin installation failed: ' . $name);
                }
            } catch (\Throwable $exception) {
                $this->logInstallerWarning(
                    sprintf('Plugin installation failed for %s: %s', $name, $exception->getMessage())
                );
            }
        }

        $this->setPluginEnabled('system', 'jsm_registercomp', true);
        // com_config imports extension plugins before emitting onExtensionAfterSave.
        // Enable the bundled listener so selected sport types are installed on save.
        $this->setPluginEnabled('extension', 'jsm_sportbootstrap', true);
    }

    /**
     * Install sport types already selected in saved options during an upgrade.
     *
     * New installs and upgrades run the same idempotent service as the
     * onExtensionAfterSave listener. This covers configurations where sport
     * choices were saved before the listener was installed, without requiring
     * an administrator to resave options or causing writes on dashboard GET.
     * The selected standard-position setting is honoured when the native
     * database-tool model is available. A failed optional bootstrap reports a
     * warning but must not abort the component installation transaction.
     *
     * @return void
     */
    private function installConfiguredSportTypes(): void
    {
        try {
            $params = ComponentHelper::getParams('com_sportsmanagement');
            $selected = $params->get('cfg_sport_types', []);

            if ($selected === [] || $selected === '') {
                return;
            }

            // During postflight Joomla may not yet have booted the component
            // namespace, even though all administrator files are installed.
            if (!class_exists(ConfiguredSportTypeInstaller::class)) {
                $file = JPATH_ADMINISTRATOR
                    . '/components/com_sportsmanagement/src/Service/ConfiguredSportTypeInstaller.php';

                if (!is_file($file)) {
                    throw new \RuntimeException('SportsManagement configured sport installer is missing.');
                }

                require_once $file;
            }

            $seed = null;

            if ((string) $params->get('install_standard_position', '0') === '1') {
                try {
                    $component = Factory::getApplication()->bootComponent('com_sportsmanagement');
                    $model = $component->getMVCFactory()->createModel(
                        'Databasetool',
                        'Administrator',
                        ['ignore_request' => true]
                    );

                    if ($model instanceof DatabasetoolModel) {
                        $seed = static function (string $name, int $id, string $token) use ($model): void {
                            $model->addStandardForSportType($name, $id, $token, 0);
                        };
                    } else {
                        Log::add(
                            'SportsManagement sport standard definitions model is unavailable in postflight.',
                            Log::WARNING,
                            'com_sportsmanagement'
                        );
                    }
                } catch (\Throwable $exception) {
                    Log::add(
                        'SportsManagement sport standards could not be initialised: ' . $exception->getMessage(),
                        Log::WARNING,
                        'com_sportsmanagement'
                    );
                }
            }

            $installed = (new ConfiguredSportTypeInstaller($this->getDatabase()))
                ->installMissing($selected, $seed);

            if ($installed !== []) {
                $app = Factory::getApplication();
                $app->getLanguage()->load(
                    'plg_extension_jsm_sportbootstrap',
                    JPATH_ADMINISTRATOR,
                    $app->getLanguage()->getTag(),
                    true
                );
                $app->enqueueMessage(
                    Text::sprintf('PLG_EXTENSION_JSM_SPORTBOOTSTRAP_INSTALLED', count($installed)),
                    'message'
                );
            }
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement configured sport upgrade failed: ' . $exception->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
            );
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        }
    }

    /**
     * Install quote collections already selected before the extension update.
     *
     * The options listener only runs when an administrator saves the component
     * configuration. Reconciling saved quote selections during installer
     * postflight also fixes earlier installations with empty quote categories.
     * Each category is imported only if it has no stored quote records.
     * Read-only administrator pages never insert quotes. Missing XML or SQL
     * failures are logged and reported generically without aborting upgrades.
     *
     * @return void
     */
    private function installConfiguredQuotes(): void
    {
        try {
            $selection = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_quotes', []);

            if ($selection === [] || $selection === '') {
                return;
            }

            // The component namespace might not be bootstrapped yet in a
            // Joomla install/update postflight callback.
            if (!class_exists(ConfiguredQuoteInstaller::class)) {
                $file = JPATH_ADMINISTRATOR
                    . '/components/com_sportsmanagement/src/Service/ConfiguredQuoteInstaller.php';

                if (!is_file($file)) {
                    throw new \RuntimeException('SportsManagement quote installation service is missing.');
                }

                require_once $file;
            }

            $installed = (new ConfiguredQuoteInstaller($this->getDatabase()))->installMissing($selection);

            if ($installed === []) {
                return;
            }

            $app = Factory::getApplication();
            $app->getLanguage()->load(
                'plg_extension_jsm_sportbootstrap',
                JPATH_ADMINISTRATOR,
                $app->getLanguage()->getTag(),
                true
            );
            $app->enqueueMessage(
                Text::sprintf(
                    'PLG_EXTENSION_JSM_SPORTBOOTSTRAP_QUOTES_INSTALLED',
                    array_sum($installed),
                    count($installed)
                ),
                'message'
            );
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement configured quote upgrade failed: ' . $exception->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
            );
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        }
    }

    /**
     * Reconcile configured country associations during component postflight.
     *
     * Selected countries can already be saved in the Joomla component options
     * before the extension listener is installed. Uses the same non-destructive
     * installer as option saves, bootstrapping only empty country categories
     * from local XML and keeping all existing association records untouched.
     * Reports missing bundled country XML as a warning and logs SQL failures
     * privately instead of interrupting the component upgrade.
     *
     * @return void
     */
    private function installConfiguredAssociations(): void
    {
        try {
            $selected = ComponentHelper::getParams('com_sportsmanagement')
                ->get('cfg_country_associations', []);

            if ($selected === [] || $selected === '') {
                return;
            }

            // The namespace may be unavailable before the component has been
            // booted in Joomla's install/update postflight lifecycle.
            if (!class_exists(ConfiguredAssociationInstaller::class)) {
                $file = JPATH_ADMINISTRATOR
                    . '/components/com_sportsmanagement/src/Service/ConfiguredAssociationInstaller.php';

                if (!is_file($file)) {
                    throw new \RuntimeException('SportsManagement association installer is unavailable.');
                }

                require_once $file;
            }

            $importer = new ConfiguredAssociationInstaller($this->getDatabase());
            $installed = $importer->installMissing($selected);
            $missing = $importer->getMissingSources();

            if ($installed === [] && $missing === []) {
                return;
            }

            $app = Factory::getApplication();
            $app->getLanguage()->load(
                'plg_extension_jsm_sportbootstrap',
                JPATH_ADMINISTRATOR,
                $app->getLanguage()->getTag(),
                true
            );

            if ($installed !== []) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'PLG_EXTENSION_JSM_SPORTBOOTSTRAP_ASSOCIATIONS_INSTALLED',
                        array_sum($installed),
                        count($installed)
                    ),
                    'message'
                );
            }

            if ($missing !== []) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'PLG_EXTENSION_JSM_SPORTBOOTSTRAP_ASSOCIATIONS_XML_MISSING',
                        implode(', ', $missing)
                    ),
                    'warning'
                );
            }
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement configured association upgrade failed: ' . $exception->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
            );
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        }
    }

    /**
     * Set the activation state of one explicitly identified Joomla plugin.
     * Updates Joomla's extension registry by the plugin's group, element and type, avoiding unintended changes to similarly named extensions.
     * @param string $folder Plugin group such as system or extension.
     * @param string $element Joomla plugin element name.
     * @param bool $enabled Whether to activate the extension.
     * @return void
     */
    private function setPluginEnabled(string $folder, string $element, bool $enabled): void
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = ' . (int) $enabled)
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
            ->where($db->quoteName('element') . ' = ' . $db->quote($element));
        $db->setQuery($query)->execute();
    }

    /**
     * Make an installed administrator module visible on all backend pages.
     * Finds the module by name and administrator client, and adds a default modules_menu assignment only when none exists.
     * @param string $moduleName Joomla administrator module element.
     * @return void
     */
    private function ensureAdministratorModuleAssignment(string $moduleName): void
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('module') . ' = ' . $db->quote($moduleName))
            ->where($db->quoteName('client_id') . ' = 1');
        $db->setQuery($query);
        $moduleId = (int) $db->loadResult();

        if ($moduleId <= 0) {
            return;
        }

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__modules_menu'))
            ->where($db->quoteName('moduleid') . ' = ' . $moduleId);
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $db->insertObject('#__modules_menu', (object) ['moduleid' => $moduleId, 'menuid' => 0]);
        }
    }

    /**
     * Remove bundled extensions selected by the component removal settings.
     * Resolves installed module or plugin IDs from manifest entries and Joomla's extension registry, then requests the native installer to remove them.
     * @param InstallerAdapter $adapter Component manifest containing bundled dependencies.
     * @param string $xpath Manifest path identifying modules or plugins.
     * @param string $type Joomla extension type to uninstall.
     * @return void
     */
    private function uninstallBundledExtensions(
        InstallerAdapter $adapter,
        string $xpath,
        string $type
    ): void {
        $manifest = $adapter->getParent()->manifest;
        $items = $manifest->xpath($xpath) ?: [];
        $db = $this->getDatabase();
        $installer = $this->getInstaller();

        foreach ($items as $item) {
            $element = $type === 'module'
                ? (string) $item['module']
                : ((string) $item['element'] ?: (string) $item['plugin']);

            $query = $db->createQuery()
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote($type))
                ->where($db->quoteName('element') . ' = ' . $db->quote($element));

            if ($type === 'plugin') {
                $query->where($db->quoteName('folder') . ' = ' . $db->quote((string) $item['group']));
            } else {
                $client = (string) $item['client'] ?: 'site';
                $query->where($db->quoteName('client_id') . ' = ' . ($client === 'administrator' ? 1 : 0));
            }

            $db->setQuery($query);

            foreach ($db->loadColumn() ?: [] as $extensionId) {
                $installer->uninstall($type, (int) $extensionId);
            }
        }
    }

    /**
     * Register SportsManagement in Joomla's action log configuration.
     * Creates the required action-logging extension configuration only when no matching component record exists.
     * @return void
     */
    private function ensureActionLogConfiguration(): void
    {
        $db = $this->getDatabase();
        $extension = 'com_sportsmanagement';

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__action_logs_extensions'))
            ->where($db->quoteName('extension') . ' = ' . $db->quote($extension));
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $db->insertObject('#__action_logs_extensions', (object) ['extension' => $extension]);
        }

        foreach ([
            ['club', '#__sportsmanagement_club'],
            ['league', '#__sportsmanagement_league'],
        ] as [$title, $table]) {
            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__action_log_config'))
                ->where($db->quoteName('type_alias') . ' = ' . $db->quote($extension))
                ->where($db->quoteName('type_title') . ' = ' . $db->quote($title));
            $db->setQuery($query);

            if ((int) $db->loadResult() !== 0) {
                continue;
            }

            $db->insertObject('#__action_log_config', (object) [
                'id' => 0,
                'type_title' => $title,
                'type_alias' => $extension,
                'id_holder' => 'id',
                'title_holder' => $title,
                'table_name' => $table,
                'text_prefix' => 'COM_SPORTSMANAGEMENT_TRANSACTION',
            ]);
        }
    }

    /**
     * Prepare writable SportsManagement image folders for a Joomla installation.
     * Creates the component image hierarchy, merges packaged media assets into compatible locations and installs standard placeholder images.
     * @return void
     */
    private function createImagesFolder(): void
    {
        $imageRoot = JPATH_ROOT . '/images/com_sportsmanagement';
        $base = $imageRoot . '/database';
        $folders = [
            'agegroups', 'clubs', 'clubs/large', 'clubs/medium', 'clubs/small',
            'clubs/trikot_home', 'clubs/trikot_away', 'clubs/trikot', 'laender_karten',
            'events', 'leagues', 'positions', 'divisions', 'person_playground',
            'associations', 'flags_associations', 'persons', 'placeholders',
            'predictionusers', 'playgrounds', 'projects', 'projectimages',
            'projectreferees', 'projectteams', 'projectteams/trikot_home',
            'projectteams/trikot_away', 'flag_maps', 'flag_maps_world', 'rosterground',
            'matchreport', 'seasons', 'sport_types', 'rounds', 'teams', 'flags',
            'teamplayers', 'teamstaffs', 'venues', 'jl_images', 'statistics',
        ];

        $this->ensureDirectory($imageRoot);
        $this->ensureDirectory($base);
        $this->copyIndexFile($imageRoot);
        $this->copyIndexFile($base);

        foreach ($folders as $folder) {
            $path = $base . '/' . $folder;
            $this->ensureDirectory($path);
            $this->copyIndexFile($path);
        }

        $this->mergeBundledMedia($base, $folders);
        $this->copyPlaceholderAssets($base);
    }

    /**
     * Copy existing component media assets into the installation image structure.
     * Respects the configured list of image subfolders and skips missing package media roots to preserve existing user-managed files.
     * @param string $targetBase Root target image directory.
     * @param array $folders Expected image subdirectories.
     * @return void
     */
    private function mergeBundledMedia(string $targetBase, array $folders): void
    {
        $sourceBase = JPATH_ROOT . '/media/com_sportsmanagement';

        if (!is_dir($sourceBase)) {
            return;
        }

        foreach ($folders as $folder) {
            $source = $sourceBase . '/' . $folder;

            if (!is_dir($source)) {
                continue;
            }

            try {
                $this->mergeFolder($source, $targetBase . '/' . $folder);
            } catch (\Throwable $exception) {
                $this->logInstallerWarning(
                    sprintf('Could not migrate media folder %s: %s', $folder, $exception->getMessage())
                );
            }
        }
    }

    /**
     * Merge one packaged media directory into a component image directory.
     * Traverses the source tree, creates needed target directories and preserves existing destination files instead of overwriting user assets.
     * @param string $source Source folder to merge.
     * @param string $target Destination folder for media assets.
     * @return void
     */
    private function mergeFolder(string $source, string $target): void
    {
        $this->ensureDirectory($target);
        $source = rtrim($source, '/\\');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = ltrim(substr($item->getPathname(), strlen($source)), '/\\');
            $destination = $target . '/' . str_replace('\\', '/', $relative);

            if ($item->isDir()) {
                $this->ensureDirectory($destination);
                continue;
            }

            if ($item->isFile() && !is_file($destination)) {
                $this->ensureDirectory(dirname($destination));
                File::copy($item->getPathname(), $destination);
            }
        }
    }

    /**
     * Populate missing person and general-purpose image placeholders.
     * Copies the standard placeholder files into the appropriate SportsManagement directories without replacing installed customized images.
     * @param string $base Root images/com_sportsmanagement database directory.
     * @return void
     */
    private function copyPlaceholderAssets(string $base): void
    {
        $placeholderBase = $base . '/placeholders';
        $targets = [
            'persons' => [
                'men_small.png', 'men_large.png', 'woman_small.png', 'woman_large.png', 'placeholder_150_2.png',
            ],
            'projectreferees' => [
                'men_small.png', 'men_large.png', 'woman_small.png', 'woman_large.png', 'placeholder_150_2.png',
            ],
            'teamplayers' => [
                'men_small.png', 'men_large.png', 'woman_small.png', 'woman_large.png', 'placeholder_150_2.png',
            ],
            'flags_associations' => ['placeholder_flags.png'],
            'flags' => ['placeholder_flags.png'],
            'positions' => ['placeholder_150_3.png'],
            'teams' => ['placeholder_450_3.png'],
            'projectteams' => ['placeholder_450_3.png'],
            'projects' => ['placeholder_450_2.png'],
            'playgrounds' => ['placeholder_stadium.png'],
            'agegroups' => ['placeholder_21.png'],
            'leagues' => ['placeholder_21.png'],
            'sport_types' => ['placeholder_21.png'],
            'clubs/trikot_home' => ['placeholder_small.gif'],
            'clubs/trikot_away' => ['placeholder_small.gif'],
            'projectteams/trikot_home' => ['placeholder_small.gif'],
            'projectteams/trikot_away' => ['placeholder_small.gif'],
            'clubs/trikot' => ['placeholder_small.gif'],
            'clubs/small' => ['placeholder_wappen_20.png'],
            'clubs/medium' => ['placeholder_50.png', 'placeholder_wappen_50.png', 'placeholder_flags.png'],
            'associations' => ['placeholder_50.png', 'placeholder_wappen_50.png', 'placeholder_flags.png'],
            'flag_maps' => ['placeholder_50.png', 'placeholder_wappen_50.png', 'placeholder_flags.png'],
            'clubs/large' => ['placeholder_150.png', 'placeholder_wappen_150.png'],
        ];

        foreach ($targets as $folder => $files) {
            foreach ($files as $file) {
                $source = $placeholderBase . '/' . $file;
                $destination = $base . '/' . $folder . '/' . $file;

                if (!is_file($source) || is_file($destination)) {
                    continue;
                }

                try {
                    File::copy($source, $destination);
                } catch (\Throwable $exception) {
                    $this->logInstallerWarning(
                        sprintf('Could not copy placeholder %s to %s: %s', $file, $folder, $exception->getMessage())
                    );
                }
            }
        }
    }

    /**
     * Ensure a required component image directory exists.
     * Uses Joomla's Filesystem Folder service to create the directory only when it is not already available.
     * @param string $path Absolute directory path to create.
     * @return void
     */
    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            Folder::create($path);
        }
    }

    /**
     * Add an index.html access placeholder to a component image directory.
     * Copies Joomla's standard index file into directories where no index file exists, reducing casual directory browsing.
     * @param string $targetDirectory Destination directory to protect.
     * @return void
     */
    private function copyIndexFile(string $targetDirectory): void
    {
        $source = JPATH_ROOT . '/images/index.html';
        $target = $targetDirectory . '/index.html';

        if (is_file($source) && !is_file($target)) {
            File::copy($source, $target);
        }
    }

    /**
     * Delete obsolete temporary archive files after component installation.
     * Removes recognised SportsManagement package archives and stale temporary artifacts without affecting unrelated files.
     * @return void
     */
    private function deleteInstallFiles(): void
    {
        foreach ([
            JPATH_ROOT . '/tmp/master.zip',
            JPATH_ROOT . '/tmp/sportsmanagement-master.zip',
        ] as $file) {
            if (!is_file($file)) {
                continue;
            }

            try {
                File::delete($file);
            } catch (\Throwable $exception) {
                $this->logInstallerWarning('Could not remove temporary file ' . $file . ': ' . $exception->getMessage());
            }
        }

        $folder = JPATH_ROOT . '/tmp/sportsmanagement-master';

        if (is_dir($folder)) {
            try {
                Folder::delete($folder);
            } catch (\Throwable $exception) {
                $this->logInstallerWarning('Could not remove temporary folder ' . $folder . ': ' . $exception->getMessage());
            }
        }
    }

    /**
     * Log and display a nonfatal bundled extension installation warning.
     * Writes diagnostic text at warning severity to Joomla's installer log and notifies the administrator that a companion extension needs attention.
     * @param string $message Installer failure summary.
     * @return void
     */
    private function logInstallerWarning(string $message): void
    {
        Log::add($message, Log::WARNING, 'jsmerror');
        Factory::getApplication()->enqueueMessage('SportsManagement installer: ' . $message, 'warning');
    }
}
