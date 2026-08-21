<?php
/**
 * Joomla 5/6 runtime helper for the SportsManagement calendar module.
 *
 * The original helper.php is retained as a legacy fallback. New Joomla 5/6
 * requests use this file so removed Joomla 2.5/3 APIs are not part of the
 * normal module bootstrap.
 *
 * @package     Sportsmanagement
 * @subpackage  mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

require_once __DIR__ . '/calendarClass.php';
require_once __DIR__ . '/calendarFunctions.php';
require_once __DIR__ . '/calendarRuntime_j5.php';
require_once __DIR__ . '/ajaxHelper_j5.php';

final class modJSMCalendarHelper
{
    public function showCal(Registry $params, int $year, int $month, int $modid, int $ajax = 0): array
    {
        $app = Factory::getApplication();
        $language = $app->getLanguage();
        $language->load('mod_sportsmanagement_calendar');

        $calendar = new JSMCalendar();
        $dayNameLength = max(1, (int) $params->get('cal_length_days', 2));

        $calendar->dayNames = [
            substr(Text::_('SUN'), 0, $dayNameLength),
            substr(Text::_('MON'), 0, $dayNameLength),
            substr(Text::_('TUE'), 0, $dayNameLength),
            substr(Text::_('WED'), 0, $dayNameLength),
            substr(Text::_('THU'), 0, $dayNameLength),
            substr(Text::_('FRI'), 0, $dayNameLength),
            substr(Text::_('SAT'), 0, $dayNameLength),
        ];
        $calendar->monthNames = [
            Text::_('JANUARY'),
            Text::_('FEBRUARY'),
            Text::_('MARCH'),
            Text::_('APRIL'),
            Text::_('MAY'),
            Text::_('JUNE'),
            Text::_('JULY'),
            Text::_('AUGUST'),
            Text::_('SEPTEMBER'),
            Text::_('OCTOBER'),
            Text::_('NOVEMBER'),
            Text::_('DECEMBER'),
        ];

        $calendar->startDay = (int) $params->get('cal_start_day', 0);
        $calendar->lightbox = (int) $params->get('lightbox', 1);
        $calendar->lightbox_on_pageload = (int) $params->get('lightbox_on_pageload', 0);
        $calendar->usedteams = $params->get('usedteams', '');
        $calendar->usedclubs = $params->get('usedclubs', '');
        $calendar->modid = $modid;
        $calendar->ajax = $ajax;

        JSMCalendar::$prefix = (string) $params->get('custom_prefix', '');
        JSMCalendar::$params = $params;
        JSMCalendar::getMatches($month, $year);

        $counter = [];
        $offset = (string) $app->get('offset', 'UTC');

        foreach (JSMCalendar::$matches as $row) {
            if (!isset($row['date'])) {
                continue;
            }

            $created = self::dateFromValue($row['date'], $offset);
            if (!$created) {
                continue;
            }

            $createdYear = $created->format('Y');
            $createdMonth = $created->format('m');
            $createdDay = $created->format('d');
            $createdDate = $createdYear . $createdMonth . $createdDay;

            if (!isset($counter[$createdDate])) {
                $counter[$createdDate] = [
                    'createdYear' => $createdYear,
                    'createdMonth' => $createdMonth,
                    'createdDay' => $createdDay,
                    'tiptitle' => $created->format('l, d.m.Y'),
                    'count' => 0,
                ];
            }

            $counter[$createdDate]['count']++;
        }

        $single = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCH');
        $plural = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHES');
        $dayLabel = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_MATCHTHISDAY');
        $inject = (int) $params->get('inject', 0);

        foreach ($counter as $createdDate => $value) {
            $title = $value['tiptitle'] . ' :: ' . $value['count'] . ' ';
            $title .= $value['count'] > 1 ? $plural : $single;
            $title .= ' ' . $dayLabel;

            JSMCalendar::$linklist[$createdDate]['click'] = 'jlCalmod_showhide(\'jlCalList-' . $modid
                . '\', \'jlcal_' . $value['createdYear'] . '-' . $value['createdMonth'] . '-' . $value['createdDay'] . '-' . $modid
                . '\', \'' . addslashes(str_replace(' :: ', ': ', $title)) . '\', ' . $inject . ', ' . $modid . ');';
            JSMCalendar::$linklist[$createdDate]['link'] = 'javascript:void(0)\" title=\"' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        }

        return $calendar->getMonthView($month, $year);
    }

    private static function dateFromValue(mixed $value, string $offset): ?Date
    {
        try {
            if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                $date = new Date('@' . (int) $value);
                $timezone = new \DateTimeZone($offset ?: 'UTC');
                $date->setTimezone($timezone);

                return $date;
            }

            return new Date((string) $value, $offset ?: 'UTC');
        } catch (\Throwable) {
            return null;
        }
    }
}
