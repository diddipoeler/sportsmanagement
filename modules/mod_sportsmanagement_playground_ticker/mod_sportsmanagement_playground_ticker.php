<?php
/** Joomla 5/6 compatibility entry point for the SportsManagement playground ticker module. */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

$app = Factory::getApplication();
$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$module->picture_server = $componentParams->get('cfg_which_database')
    ? (string) $componentParams->get('cfg_which_database_server')
    : Uri::root();

$playgrounds = modJSMPlaygroundTicker::getData($params);

$app->getDocument()
    ->getWebAssetManager()
    ->registerAndUseStyle(
        'mod_sportsmanagement_playground_ticker',
        'modules/' . $module->module . '/css/' . $module->module . '.css'
    );

require ModuleHelper::getLayoutPath(
    $module->module,
    (string) $params->get('layout', 'default')
);
