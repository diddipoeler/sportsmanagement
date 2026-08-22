<?php
/**
 * Legacy entry bridge for the Joomla 5/6 SportsManagement playground ticker module.
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper\PlaygroundTickerHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(PlaygroundTickerHelper::class)) {
    require_once __DIR__ . '/src/Helper/PlaygroundTickerHelper.php';
}

$app = Factory::getApplication();
$helper = new PlaygroundTickerHelper();
$playgrounds = $helper->getData($params, $app);
$module->picture_server = $helper->getPictureServer();

$app->getDocument()
    ->getWebAssetManager()
    ->registerAndUseStyle(
        'mod_sportsmanagement_playground_ticker',
        'modules/mod_sportsmanagement_playground_ticker/css/mod_sportsmanagement_playground_ticker.css',
        ['version' => 'auto']
    );

require ModuleHelper::getLayoutPath(
    $module->module,
    (string) $params->get('layout', 'default')
);
