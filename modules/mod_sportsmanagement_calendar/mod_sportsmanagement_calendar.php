<?php
/**
 * Legacy entry bridge for the Joomla 5/6 SportsManagement calendar module.
 *
 * The active implementation is loaded through services/provider.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementCalendar\Site\Helper\CalendarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if (!class_exists(CalendarHelper::class)) {
    require_once __DIR__ . '/src/Helper/CalendarHelper.php';
}

$data = (new CalendarHelper())->getData($params, $module, Factory::getApplication());
extract($data, EXTR_SKIP);

require ModuleHelper::getLayoutPath(
    'mod_sportsmanagement_calendar',
    (string) $params->get('which_layout', $params->get('layout', 'default'))
);
