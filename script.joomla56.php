<?php
/**
 * Joomla 5/6 installer for com_sportsmanagement.
 *
 * This installer deliberately keeps the package-specific module/plugin installation
 * behaviour while avoiding Joomla 3/4 compatibility branches and APIs removed in
 * Joomla 6.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class com_sportsmanagementInstallerScript
{
    private string $release = '4.24.00';

    public function install($adapter): bool
    {
        return true;
    }

    public function update($adapter): bool
    {
        Factory::getApplication()->enqueueMessage(
            Text::_('COM_SPORTSMANAGEMENT_UPDATE_TEXT') . $this->release,
            'message'
        );

        return true;
    }

    public function uninstall($adapter): bool
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

    public function preflight($route, $adapter): bool
    {
        $app = Factory::getApplication();
        $currentVersion = $this->getInstalledVersion();

        if ($route === 'update' && $currentVersion !== '' && version_compare($currentVersion, $this->release, 'gt')) {
            $app->enqueueMessage(
                sprintf('SportsManagement %s cannot be downgraded to %s.', $currentVersion, $this->release),
                'error'
            );

            return false;
        }

        $app->enqueueMessage(
            sprintf('SportsManagement: %s → %s (%s)', $currentVersion ?: 'new install', $this->release, $route),
            'message'
        );

        return true;
    }

    public function postflight($route, $adapter): bool
    {
        if (!in_array($route, ['install', 'update', 'discover_install'], true)) {
            return true;
        }

        try {
            $this->installModules($adapter);
            $this->installPlugins($adapter);
            $this->ensureActionLogConfiguration();
            $this->createImagesFolder();
            $this->deleteInstallFiles();
        } catch (\Throwable $exception) {
            Log::add(
                __METHOD__ . ': ' . $exception->getMessage(),
                Log::ERROR,
                'jsmerror'
            );
            Factory::getApplication()->enqueueMessage(
                'SportsManagement installer: ' . $exception->getMessage(),
                'error'
            );

            return false;
        }

        Factory::getApplication()->enqueueMessage(
            Text::_('COM_SPORTSMANAGEMENT_POSTFLIGHT_' . $route . '_TEXT') . $this->release,
            'message'
        );

        return true;
    }

    private function getDatabase(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    private function getInstalledVersion(): string
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('manifest_cache'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_sportsmanagement'));
        $db->setQuery($query);
        $manifest = json_decode((string) $db->loadResult(), true);

        return is_array($manifest) ? (string) ($manifest['version'] ?? '') : '';
    }

    private function installModules($adapter): void
    {
        $source = $adapter->getParent()->getPath('source');
        $manifest = $adapter->getParent()->manifest;
        $modules = $manifest->xpath('modules/module') ?: [];
        $db = $this->getDatabase();

        foreach ($modules as $module) {
            $name = (string) $module['module'];
            $client = (string) $module['client'] ?: 'site';
            $position = (string) $module['position'];
            $published = (string) $module['published'];
            $path = $client === 'administrator'
                ? $source . '/admin/modules/' . $name
                : $source . '/modules/' . $name;

            if (!is_dir($path)) {
                Factory::getApplication()->enqueueMessage('Module package not found: ' . $name, 'warning');
                continue;
            }

            $installer = new Installer();

            if (!$installer->install($path)) {
                Factory::getApplication()->enqueueMessage('Module installation failed: ' . $name, 'warning');
                continue;
            }

            if ($position !== '') {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__modules'))
                    ->set($db->quoteName('position') . ' = ' . $db->quote($position))
                    ->set($db->quoteName('ordering') . ' = ' . ($client === 'administrator' ? 1 : 99))
                    ->set($db->quoteName('published') . ' = ' . (int) ($published !== '' ? $published : 0))
                    ->where($db->quoteName('module') . ' = ' . $db->quote($name));
                $db->setQuery($query)->execute();
            }

            if ($client === 'administrator') {
                $this->ensureAdministratorModuleAssignment($name);
            }
        }
    }

    private function installPlugins($adapter): void
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

            $installer = new Installer();

            if (!$installer->install($path)) {
                Factory::getApplication()->enqueueMessage('Plugin installation failed: ' . $name, 'warning');
            }
        }

        $this->setPluginEnabled('system', 'jsm_registercomp', true);
    }

    private function setPluginEnabled(string $folder, string $element, bool $enabled): void
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = ' . (int) $enabled)
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
            ->where($db->quoteName('element') . ' = ' . $db->quote($element));
        $db->setQuery($query)->execute();
    }

    private function ensureAdministratorModuleAssignment(string $moduleName): void
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('module') . ' = ' . $db->quote($moduleName));
        $db->setQuery($query);
        $moduleId = (int) $db->loadResult();

        if ($moduleId <= 0) {
            return;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__modules_menu'))
            ->where($db->quoteName('moduleid') . ' = ' . $moduleId);
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $assignment = (object) ['moduleid' => $moduleId, 'menuid' => 0];
            $db->insertObject('#__modules_menu', $assignment);
        }
    }

    private function uninstallBundledExtensions($adapter, string $xpath, string $type): void
    {
        $manifest = $adapter->getParent()->manifest;
        $items = $manifest->xpath($xpath) ?: [];
        $db = $this->getDatabase();
        $installer = new Installer();

        foreach ($items as $item) {
            if ($type === 'module') {
                $element = (string) $item['module'];
            } else {
                $element = (string) $item['element'];

                if ($element === '') {
                    $element = (string) $item['plugin'];
                }
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote($type))
                ->where($db->quoteName('element') . ' = ' . $db->quote($element));

            if ($type === 'plugin') {
                $query->where($db->quoteName('folder') . ' = ' . $db->quote((string) $item['group']));
            }

            $db->setQuery($query);

            foreach ($db->loadColumn() ?: [] as $extensionId) {
                $installer->uninstall($type, (int) $extensionId);
            }
        }
    }

    private function ensureActionLogConfiguration(): void
    {
        $db = $this->getDatabase();
        $extension = 'com_sportsmanagement';

        $query = $db->getQuery(true)
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
            $query = $db->getQuery(true)
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

    private function createImagesFolder(): void
    {
        $base = JPATH_ROOT . '/images/com_sportsmanagement/database';
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

        if (!is_dir($base)) {
            Folder::create($base);
        }

        foreach ($folders as $folder) {
            $path = $base . '/' . $folder;

            if (!is_dir($path)) {
                Folder::create($path);
            }

            $indexSource = JPATH_ROOT . '/images/index.html';
            $indexTarget = $path . '/index.html';

            if (is_file($indexSource) && !is_file($indexTarget)) {
                File::copy($indexSource, $indexTarget);
            }
        }
    }

    private function deleteInstallFiles(): void
    {
        foreach ([
            JPATH_ROOT . '/tmp/master.zip',
            JPATH_ROOT . '/tmp/sportsmanagement-master.zip',
        ] as $file) {
            if (is_file($file)) {
                File::delete($file);
            }
        }

        $folder = JPATH_ROOT . '/tmp/sportsmanagement-master';

        if (is_dir($folder)) {
            Folder::delete($folder);
        }
    }
}
