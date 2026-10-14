<?php
/**
 * Native Joomla 5/6 helper for the SportsManagement GCalendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementGcalendar\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class GcalendarHelper
{
    public function getData(
        Registry $params,
        object $module,
        CMSApplicationInterface $app,
        DatabaseInterface $db
    ): array {
        $calendars = $this->getCalendars($params, $app, $db);
        $calendarIds = array_values(array_unique(array_filter(array_map(
            static fn (object $calendar): int => (int) ($calendar->id ?? 0),
            $calendars
        ), static fn (int $id): bool => $id > 0)));

        $date = Date::getInstance('now', (string) $app->get('offset', 'UTC'));
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

    /**
     * Return calendars visible to the current user.
     *
     * Public for the legacy helper bridge while native module rendering uses the
     * same implementation through getData().
     */
    public function getCalendars(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $db
    ): array {
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_gcalendar'));

        $rawCalendarIds = $params->get('calendarids', []);
        $selectionRequested = is_array($rawCalendarIds)
            ? $rawCalendarIds !== [] && $rawCalendarIds !== ['']
            : $rawCalendarIds !== null && $rawCalendarIds !== '';
        $calendarIds = $this->normaliseCalendarIds($rawCalendarIds);

        // An explicitly configured but invalid selection must not expose all calendars.
        if ($selectionRequested && $calendarIds === []) {
            return [];
        }

        if ($calendarIds !== []) {
            $query->whereIn($db->quoteName('id'), $calendarIds, ParameterType::INTEGER);
        }

        $user = $app->getIdentity();

        // The JSON feed denies access without an identity; the module must too.
        if ($user === null) {
            return [];
        }

        if (!$user->authorise('core.admin', 'com_sportsmanagement')) {
            $levels = array_values(array_unique(array_filter(array_map(
                'intval',
                $user->getAuthorisedViewLevels()
            ), static fn (int $level): bool => $level > 0)));

            if ($levels === []) {
                return [];
            }

            // Match GoogleCalendarReadService so restricted calendar details
            // are not advertised by the module to unauthorized visitors.
            $query->whereIn($db->quoteName('access'), $levels, ParameterType::INTEGER);
            $query->whereIn($db->quoteName('access_content'), $levels, ParameterType::INTEGER);
        }

        $query->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * @return array<int>
     */
    private function normaliseCalendarIds(mixed $configured): array
    {
        $values = is_array($configured) ? $configured : [$configured];
        $ids = [];

        foreach ($values as $value) {
            if (!is_string($value) && !is_int($value)) {
                continue;
            }

            $parts = is_int($value) ? [$value] : (preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: []);

            foreach ($parts as $part) {
                if (is_int($part)) {
                    if ($part > 0) {
                        $ids[$part] = $part;
                    }
                    continue;
                }

                if (!preg_match('/^[1-9][0-9]*$/D', $part)) {
                    continue;
                }

                $id = filter_var($part, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id !== false) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }

}
