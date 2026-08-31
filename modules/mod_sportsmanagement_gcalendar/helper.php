<?php
/** Legacy helper bridge kept for third-party overrides that still call the old module helper class. */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class sportsmanagementModGCalendarHelper
{
    public static function getCalendars($params): array
    {
        $app = Factory::getApplication();
        /** @var DatabaseInterface $db */
        $db = $app->getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_gcalendar'));

        $calendarIds = $params ? $params->get('calendarids', []) : [];

        if (!is_array($calendarIds)) {
            $calendarIds = preg_split('/\s*,\s*/', (string) $calendarIds, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $calendarIds = array_values(array_unique(array_filter(array_map('intval', $calendarIds))));

        if ($calendarIds) {
            $query->where($db->quoteName('id') . ' IN (' . implode(',', $calendarIds) . ')');
        }

        $user = $app->getIdentity();

        if ($user && !$user->authorise('core.admin', 'com_sportsmanagement')) {
            $levels = array_values(array_unique(array_filter(array_map('intval', $user->getAuthorisedViewLevels()))));

            if ($levels) {
                $query->where($db->quoteName('access') . ' IN (' . implode(',', $levels) . ')');
            } else {
                $query->where('1 = 0');
            }
        }

        $query->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
