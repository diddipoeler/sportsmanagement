<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap as AdministratorLegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap as SiteLegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\Input\Input;

final class SportsManagementMVCFactory extends MVCFactory
{
    public function createController(
        $name,
        $prefix,
        array $config,
        CMSApplicationInterface $app,
        Input $input
    ) {
        $prefix = $this->normalisePrefix((string) $prefix);
        $controller = parent::createController($name, $prefix, $config, $app, $input);

        if ($controller !== null) {
            return $controller;
        }

        if (!$this->loadLegacyController((string) $name, $prefix)) {
            return null;
        }

        return parent::createController($name, $prefix, $config, $app, $input);
    }

    public function createModel($name, $prefix = '', array $config = [])
    {
        $prefix = $this->normalisePrefix((string) $prefix);

        // Joomla 6 no longer supports injecting the database through the model constructor.
        // SportsManagement restores the resolved database through setDatabase() below.
        unset($config['dbo']);

        $model = parent::createModel($name, $prefix, $config);

        if ($model === null && $this->loadLegacyModel((string) $name, $prefix)) {
            $model = parent::createModel($name, $prefix, $config);
        }

        $this->restoreSportsManagementDatabase($model);

        return $model;
    }

    public function createView($name, $prefix = '', $type = '', array $config = [])
    {
        $prefix = $this->normalisePrefix((string) $prefix);
        $type = $type !== '' ? (string) $type : 'html';
        $this->bridgeAdministratorTemplatePath((string) $name, $prefix, $type, $config);
        $view = parent::createView($name, $prefix, $type, $config);

        if ($view !== null) {
            return $view;
        }

        $legacyBasePath = $this->loadLegacyView((string) $name, $prefix, $type);

        if ($legacyBasePath === null) {
            return null;
        }

        $config['template_path'] = $legacyBasePath . '/views/' . strtolower((string) $name) . '/tmpl';

        return parent::createView($name, $prefix, $type, $config);
    }

    private function bridgeAdministratorTemplatePath(string $name, string $prefix, string $type, array &$config): void
    {
        if ($prefix !== 'Administrator' || strtolower($type) !== 'html' || isset($config['template_path'])) {
            return;
        }

        $view = strtolower($name);
        $nativeTemplatePath = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tmpl/' . $view;
        $legacyTemplatePath = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/' . $view . '/tmpl';

        if (!Folder::exists($nativeTemplatePath) && Folder::exists($legacyTemplatePath)) {
            $config['template_path'] = $legacyTemplatePath;
        }
    }

    private function loadLegacyController(string $name, string $prefix): bool
    {
        $prefix = $this->normalisePrefix($prefix);
        $basePath = $this->legacyBasePath($prefix);

        if ($basePath === null) {
            return false;
        }

        $this->bootLegacy($prefix, $name);
        $targetClass = 'Diddipoeler\\Component\\SportsManagement\\' . $prefix . '\\Controller\\' . ucfirst($name) . 'Controller';

        if (class_exists($targetClass, false)) {
            return true;
        }

        foreach ($this->legacySearchPaths($prefix) as $searchPath) {
            $file = $searchPath . '/controllers/' . strtolower($name) . '.php';

            if (!is_file($file)) {
                continue;
            }

            require_once $file;

            if (class_exists($targetClass, false)) {
                return true;
            }

            foreach ([
                'sportsmanagementController' . ucfirst($name),
                'SportsManagementController' . ucfirst($name),
            ] as $legacyClass) {
                if (class_exists($legacyClass, false)) {
                    return class_alias($legacyClass, $targetClass);
                }
            }
        }

        return false;
    }

    private function loadLegacyModel(string $name, string $prefix): bool
    {
        $prefix = $this->normalisePrefix($prefix);

        if ($this->legacyBasePath($prefix) === null) {
            return false;
        }

        $this->bootLegacy($prefix, $name);
        $targetClass = 'Diddipoeler\\Component\\SportsManagement\\' . $prefix . '\\Model\\' . ucfirst($name) . 'Model';

        if (class_exists($targetClass, false)) {
            return true;
        }

        foreach ($this->legacySearchPaths($prefix) as $searchPath) {
            $file = $searchPath . '/models/' . strtolower($name) . '.php';

            if (!is_file($file)) {
                continue;
            }

            require_once $file;
            $legacyClass = 'sportsmanagementModel' . ucfirst($name);

            if (class_exists($targetClass, false)) {
                return true;
            }

            if (class_exists($legacyClass, false)) {
                return class_alias($legacyClass, $targetClass);
            }
        }

        return false;
    }

    private function loadLegacyView(string $name, string $prefix, string $type): ?string
    {
        $prefix = $this->normalisePrefix($prefix);

        if ($this->legacyBasePath($prefix) === null) {
            return null;
        }

        $this->bootLegacy($prefix, $name);
        $targetClass = 'Diddipoeler\\Component\\SportsManagement\\' . $prefix . '\\View\\' . ucfirst($name) . '\\' . ucfirst($type) . 'View';

        foreach ($this->legacySearchPaths($prefix) as $searchPath) {
            $file = $searchPath . '/views/' . strtolower($name) . '/view.' . strtolower($type) . '.php';

            if (!is_file($file)) {
                continue;
            }

            require_once $file;
            $legacyClass = 'sportsmanagementView' . ucfirst($name);

            if (!class_exists($targetClass, false) && class_exists($legacyClass, false)) {
                class_alias($legacyClass, $targetClass);
            }

            if (class_exists($targetClass, false)) {
                return $searchPath;
            }
        }

        return null;
    }

    private function legacySearchPaths(string $prefix): array
    {
        $basePath = $this->legacyBasePath($prefix);

        if ($basePath === null) {
            return [];
        }

        $paths = [$basePath];

        if ($prefix !== 'Administrator') {
            return $paths;
        }

        if (!class_exists('sportsmanagementHelper')) {
            $helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        if (!class_exists('sportsmanagementHelper')) {
            return $paths;
        }

        try {
            $extensions = \sportsmanagementHelper::getExtensions();
        } catch (\Throwable) {
            return $paths;
        }

        foreach ((array) $extensions as $extensionName) {
            $extensionName = preg_replace('/[^A-Z0-9_-]/i', '', (string) $extensionName);

            if ($extensionName === '') {
                continue;
            }

            $extensionPath = JPATH_SITE
                . '/components/com_sportsmanagement/extensions/'
                . $extensionName
                . '/admin';

            if (is_dir($extensionPath)) {
                $paths[] = $extensionPath;
            }
        }

        return array_values(array_unique($paths));
    }

    private function bootLegacy(string $prefix, string $view): void
    {
        if ($prefix === 'Administrator') {
            AdministratorLegacyBootstrap::bootForView($view);

            return;
        }

        SiteLegacyBootstrap::bootForView($view);
    }

    private function normalisePrefix(string $prefix): string
    {
        $normalised = trim(strtolower(trim($prefix)), '\\');

        if ($normalised === '' || str_starts_with($normalised, 'sportsmanagement')) {
            return Factory::getApplication()->isClient('administrator')
                ? 'Administrator'
                : 'Site';
        }

        return match ($normalised) {
            'admin', 'administrator' => 'Administrator',
            'site' => 'Site',
            default => ucfirst($normalised),
        };
    }

    private function legacyBasePath(string $prefix): ?string
    {
        return match ($prefix) {
            'Administrator' => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement',
            'Site' => JPATH_SITE . '/components/com_sportsmanagement',
            default => null,
        };
    }

    private function restoreSportsManagementDatabase($model): void
    {
        if (!$model instanceof DatabaseAwareInterface) {
            return;
        }

        try {
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
            $model->setDatabase(SportsManagementDatabaseResolver::resolve($joomlaDatabase, 0));
        } catch (\Throwable) {
            // Keep Joomla's injected database connection as fallback.
        }
    }
}
