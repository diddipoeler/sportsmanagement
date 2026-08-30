<?php
namespace Diddipoeler\Module\SportsManagementGcalendar\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class GcalendarHelper
{
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $calendars = $this->getCalendars($params, $app);
        $calendarIds = array_values(array_filter(array_map(
            static fn (object $calendar): int => (int) ($calendar->id ?? 0),
            $calendars
        )));

        $date = new Date('now');
        $dayNamesMin = [];
        $monthNames = [];
        $monthNamesShort = [];

        for ($day = 0; $day < 7; $day++) {
            $dayNamesMin[] = mb_substr($date->dayToString($day, true), 0, 2);
        }

        for ($month = 1; $month <= 12; $month++) {
            $monthNames[] = $date->monthToString($month, false);
            $monthNamesShort[] = $date->monthToString($month, true);
        }

        $color = strtoupper(ltrim((string) $params->get('event_color', '135CAE'), '#'));

        if (!preg_match('/^[0-9A-F]{6}$/', $color)) {
            $color = '135CAE';
        }

        $compact = max(0, min(2, (int) $params->get('compact_events', 1)));
        $feedUrl = Route::_(
            'index.php?option=com_sportsmanagement&view=jsonfeed&compact=' . $compact
                . '&format=raw&gcids=' . implode(',', $calendarIds),
            false
        );

        $this->registerAssets($app);

        return [
            'calendars' => $calendars,
            'calendarIds' => $calendarIds,
            'calendarConfig' => [
                'feedUrl' => $feedUrl,
                'weekStart' => max(0, min(6, (int) $params->get('weekstart', 0))),
                'titleFormat' => (string) $params->get('titleformat_month', 'M Y'),
                'timeFormat' => (string) $params->get('timeformat_month', 'g:i a'),
                'calendarHeight' => max(0, (int) $params->get('calendar_height', 0)),
                'eventColor' => '#' . $color,
                'compactMode' => $compact,
                'dayNamesMin' => $dayNamesMin,
                'monthNames' => $monthNames,
                'monthNamesShort' => $monthNamesShort,
                'previousLabel' => Text::_('JPREVIOUS'),
                'nextLabel' => Text::_('JNEXT'),
                'todayLabel' => Text::_('JTODAY'),
            ],
        ];
    }

    private function getCalendars(Registry $params, CMSApplicationInterface $app): array
    {
        /** @var DatabaseInterface $db */
        $db = $app->getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_gcalendar'));

        $calendarIds = $params->get('calendarids', []);

        if (!is_array($calendarIds)) {
            $calendarIds = preg_split('/\s*,\s*/', (string) $calendarIds, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $calendarIds = array_values(array_unique(array_filter(array_map('intval', $calendarIds))));

        if ($calendarIds) {
            $query->where($db->quoteName('id') . ' IN (' . implode(',', $calendarIds) . ')');
        }

        $user = $app->getIdentity();

        if ($user && !$user->authorise('core.admin', 'com_sportsmanagement')) {
            $levels = array_values(array_unique(array_filter(array_map(
                'intval',
                $user->getAuthorisedViewLevels()
            ))));

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

    private function registerAssets(CMSApplicationInterface $app): void
    {
        $wa = $app->getDocument()->getWebAssetManager();

        $wa->registerAndUseStyle(
            'mod_sportsmanagement_gcalendar.calendar',
            'modules/mod_sportsmanagement_gcalendar/tmpl/gcalendar.css'
        );
        $wa->registerAndUseScript(
            'mod_sportsmanagement_gcalendar.calendar',
            'modules/mod_sportsmanagement_gcalendar/js/gcalendar.js',
            [],
            ['defer' => true]
        );
    }
}
