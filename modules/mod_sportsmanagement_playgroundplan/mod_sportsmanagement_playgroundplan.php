<?php
/** Joomla 5/6 compatibility entry point for mod_sportsmanagement_playgroundplan. */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$app = Factory::getApplication();
$app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

if (!class_exists(PlaygroundPlanHelper::class)) {
    require_once __DIR__ . '/src/Helper/PlaygroundPlanHelper.php';
}

$list = (new PlaygroundPlanHelper())->getData($params, $app, $module);
$wam = $app->getDocument()->getWebAssetManager();
$wam->registerAndUseStyle(
    'mod_sportsmanagement_playgroundplan',
    'modules/mod_sportsmanagement_playgroundplan/css/mod_sportsmanagement_playgroundplan.css'
);

if ((int) $params->get('mode', 0) === 0) {
    $wam->registerAndUseScript(
        'mod_sportsmanagement_playgroundplan.ticker',
        'modules/mod_sportsmanagement_playgroundplan/js/ticker.js'
    );
}

require ModuleHelper::getLayoutPath(
    $module->module,
    (string) $params->get('layout', 'default')
);
