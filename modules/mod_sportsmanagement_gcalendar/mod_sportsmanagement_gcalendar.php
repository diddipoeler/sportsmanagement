<?php
/**
 * Legacy entry bridge for the Joomla 5/6 SportsManagement Google calendar module.
 */

defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(GcalendarHelper::class)) {
    require_once __DIR__ . '/src/Helper/GcalendarHelper.php';
}

$data = (new GcalendarHelper())->getData($params, $module, Factory::getApplication());
extract($data, EXTR_SKIP);

require ModuleHelper::getLayoutPath('mod_sportsmanagement_gcalendar', $params->get('layout', 'default'));
