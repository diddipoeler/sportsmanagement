<?php
/**
 * Joomla 5/6 compatibility bridge for the SportsManagement GCalendar helper.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
