<?php
/**
 * Joomla 5/6 compatibility entry point for the SportsManagement AJAX top navigation module.
 *
 * Normal module execution is handled by services/provider.php and the native dispatcher.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper\AjaxTopNavigationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(AjaxTopNavigationHelper::class)) {
    require_once __DIR__ . '/src/Helper/AjaxTopNavigationHelper.php';
}

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

$legacyLayout = (string) $params->get('layout', 'default');
if ($legacyLayout === '' || $legacyLayout === 'native' || $legacyLayout === '_:native') {
    $legacyLayout = 'default';
}

$payload = (new AjaxTopNavigationHelper())->getData($params, $module, $app);
extract($payload, EXTR_SKIP);

$document = $app->getDocument();
$assets = $document->getWebAssetManager();
$assets->useScript('bootstrap.tab');
$assets->registerAndUseStyle(
    'mod_sportsmanagement_ajax_top_navigation_menu',
    'modules/mod_sportsmanagement_ajax_top_navigation_menu/css/mod_sportsmanagement_ajax_top_navigation_menu.css',
    ['version' => 'auto']
);
$assets->registerAndUseStyle(
    'mod_sportsmanagement_ajax_top_navigation_menu.tabs',
    'modules/mod_sportsmanagement_ajax_top_navigation_menu/css/mod_sportsmanagement_ajax_top_navigation_tabs_sliders.css',
    ['version' => 'auto']
);
$assets->registerAndUseScript(
    'mod_sportsmanagement_ajax_top_navigation_menu.native',
    'modules/mod_sportsmanagement_ajax_top_navigation_menu/js/native.js',
    ['version' => 'auto'],
    ['defer' => true]
);
$document->addScriptOptions(
    'mod_sportsmanagement_ajax_top_navigation_menu.' . (int) $module->id,
    $clientConfig
);

require ModuleHelper::getLayoutPath($module->module, 'native');
