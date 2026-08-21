<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap as AdministratorLegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap as SiteLegacyBootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseInterface;

final class SportsManagementMVCFactory extends MVCFactory
{
    public function createModel($name, $prefix = '', array $config = [])
    {
        $model = parent::createModel($name, $prefix, $config);

        if ($model === null && $this->loadLegacyModel((string) $name, (string) $prefix)) {
            $model = parent::createModel($name, $prefix, $config);
        }

        $this->restoreSportsManagementDatabase($model);

        return $model;
    }

    public function createView($name, $prefix = '', $type = '', array $config = [])
    {
        $view = parent::createView($name, $prefix, $type, $config);

        if ($view !== null) {
            return $view;
        }

        $prefix = $this->normalisePrefix((string) $prefix);
        $type = $type !== '' ? (string) $type : 'html';
        $basePath = $this->legacyBasePath($prefix);

        if ($basePath === null || !$this->loadLegacyView((string) $name, $prefix, $type)) {
            return null;
        }

        $config['template_path'] = $basePath . '/views/' . strtolower((string) $name) . '/tmpl';

        return parent::createView($name, $prefix, $type, $config);
    }

    private function loadLegacyModel(string $name, string $prefix): bool
    {
        $prefix = $this->normalisePrefix($prefix);
        $basePath = $this->legacyBasePath($prefix);

        if ($basePath === null) {
            return false;
        }

        $this->bootLegacy($prefix, $name);
        $file = $basePath . '/models/' . strtolower($name) . '.php';

        if (!is_file($file)) {
            return false;
        }

        require_once $file;
        $legacyClass = 'sportsmanagementModel' . ucfirst($name);
        $targetClass = 'Diddipoeler\\Component\\SportsManagement\\' . $prefix . '\\Model\\' . ucfirst($name) . 'Model';

        if (!class_exists($legacyClass, false) || class_exists($targetClass, false)) {
            return class_exists($targetClass, false);
        }

        return class_alias($legacyClass, $targetClass);
    }

    private function loadLegacyView(string $name, string $prefix, string $type): bool
    {
        $prefix = $this->normalisePrefix($prefix);
        $basePath = $this->legacyBasePath($prefix);

        if ($basePath === null) {
            return false;
        }

        $this->bootLegacy($prefix, $name);
        $file = $basePath . '/views/' . strtolower($name) . '/view.' . strtolower($type) . '.php';

        if (!is_file($file)) {
            return false;
        }

        require_once $file;
        $legacyClass = 'sportsmanagementView' . ucfirst($name);
        $targetClass = 'Diddipoeler\\Component\\SportsManagement\\' . $prefix . '\\View\\' . ucfirst($name) . '\\' . ucfirst($type) . 'View';

        if (!class_exists($legacyClass, false) || class_exists($targetClass, false)) {
            return class_exists($targetClass, false);
        }

        return class_alias($legacyClass, $targetClass);
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
        if ($prefix === '') {
            $prefix = Factory::getApplication()->getName();
        }

        return ucfirst(strtolower($prefix));
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

        if (!class_exists('sportsmanagementHelper')) {
            $helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        try {
            if (!class_exists('sportsmanagementHelper')) {
                return;
            }

            $database = \sportsmanagementHelper::getDBConnection();

            if ($database instanceof DatabaseInterface) {
                $model->setDatabase($database);
            }
        } catch (\Throwable) {
            // Keep Joomla's injected database connection as fallback.
        }
    }
}
