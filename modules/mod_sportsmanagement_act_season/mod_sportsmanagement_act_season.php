<?php
/** Joomla 5/6 compatibility entry point for the current-season module. */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementActSeason\Site\Helper\ActSeasonHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(ActSeasonHelper::class)) {
    require_once __DIR__ . '/src/Helper/ActSeasonHelper.php';
}

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$result = (new ActSeasonHelper())->getData(
    $componentParams->get('current_season', []),
    $componentParams,
    $app
);

$list = $result['list'];
$federations = $result['federations'];
$countriesByFederation = $result['countriesByFederation'];

require ModuleHelper::getLayoutPath(
    $module->module,
    (string) $params->get('layout', 'default')
);
