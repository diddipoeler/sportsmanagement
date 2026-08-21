<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement count record module.
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCountRekord\Site\Helper\CountRekordHelper;

if (!class_exists(CountRekordHelper::class)) {
    require_once __DIR__ . '/src/Helper/CountRekordHelper.php';
}

if (!class_exists('modJSMStatistikRekordHelper', false)) {
    class_alias(CountRekordHelper::class, 'modJSMStatistikRekordHelper');
}
