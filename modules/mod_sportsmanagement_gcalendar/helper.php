<?php
/** Legacy helper bridge kept for third-party overrides that still call the old module helper class. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

if (!class_exists(GcalendarHelper::class)) {
    require_once __DIR__ . '/src/Helper/GcalendarHelper.php';
}

class sportsmanagementModGCalendarHelper
{
    public static function getCalendars($params): array
    {
        $registry = $params instanceof Registry ? $params : new Registry($params);

        return (new GcalendarHelper())->getCalendars($registry, Factory::getApplication());
    }
}
