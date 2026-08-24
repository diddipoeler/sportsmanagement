<?php
/** Joomla 5/6 compatibility entry point for the SportsManagement navigation menu. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NativeNavigationMenuHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(NativeNavigationMenuHelper::class)) {
    require_once __DIR__ . '/src/Helper/NavigationMenuHelper.php';
    require_once __DIR__ . '/src/Helper/NativeNavigationMenuHelper.php';
}

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
$payload = (new NativeNavigationMenuHelper())->getData($params, $app);
extract($payload, EXTR_SKIP);

$assets = $app->getDocument()->getWebAssetManager();
$assets->registerAndUseStyle(
    'mod_sportsmanagement_navigation_menu',
    'modules/mod_sportsmanagement_navigation_menu/css/mod_sportsmanagement_navigation_menu.css',
    ['version' => 'auto']
);
$assets->registerAndUseScript(
    'mod_sportsmanagement_navigation_menu',
    'modules/mod_sportsmanagement_navigation_menu/js/mod_sportsmanagement_navigation_menu.js',
    ['version' => 'auto'],
    ['defer' => true]
);

require ModuleHelper::getLayoutPath($module->module, (string) $params->get('layout', 'default'));
