<?php
/**
 * Legacy entry bridge for the Joomla 5/6 SportsManagement count record module.
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(CountRekordHelper::class)) {
    require_once __DIR__ . '/src/Helper/CountRekordHelper.php';
}

$list = (new CountRekordHelper())->getData($params, $module, Factory::getApplication());

require ModuleHelper::getLayoutPath(
    $module->module,
    (string) $params->get('layout', 'default')
);
