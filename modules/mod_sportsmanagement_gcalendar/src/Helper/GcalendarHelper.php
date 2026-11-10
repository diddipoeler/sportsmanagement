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
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class GcalendarHelper
{
    private const MAX_CALENDARS = 50;
    private const MAX_ID_INPUTS = 200;
    private const MAX_ID_STRING_LENGTH = 4096;
    /**
     * Build the Joomla GCalendar module configuration and visible calendars.
     *
     * Loads authorised calendars, creates the native JSON feed URL and
     * prepares translated day/month names and bounded presentation options.
     * Invalid legacy scalar settings and malformed site timezones are
     * replaced with safe defaults before Joomla date labels are created.
     *
     * @param Registry $params Joomla calendar module configuration.
     * @param object $module Current Joomla module record.
     * @param CMSApplicationInterface $app Active Joomla application.
     * @param DatabaseInterface $db Database connection for calendar lookups.
     * @return array{calendars:array<int,object>,calendarIds:array<int,int>,calendarConfig:array<string,mixed>} Calendar options and authorised records.
     */
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

        $date = Date::getInstance('now', self::safeTimezone($app->get('offset', 'UTC')));
        $dayNamesMin = [];
        $monthNames = [];
        $monthNamesShort = [];

        for ($day = 0; $day < 7; $day++) {
            $dayNamesMin[] = self::shortWeekdayLabel(
                (string) $date->dayToString($day, true)
            );
        }

        for ($month = 1; $month <= 12; $month++) {
            $monthNames[] = $date->monthToString($month, false);
            $monthNamesShort[] = $date->monthToString($month, true);
        }

        $rawColor = $params->get('event_color', '135CAE');
        $color = strtoupper(ltrim(is_string($rawColor) ? $rawColor : '135CAE', '#'));

        if (!preg_match('/^[0-9A-F]{6}$/', $color)) {
            $color = '135CAE';
        }

        $compact = self::boundedOption($params->get('compact_events', 1), 1, 2);
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
                'weekStart' => self::boundedOption($params->get('weekstart', 0), 0, 6),
                'titleFormat' => is_string($params->get('titleformat_month', 'M Y'))
                    ? $params->get('titleformat_month', 'M Y') : 'M Y',
                'timeFormat' => is_string($params->get('timeformat_month', 'g:i a'))
                    ? $params->get('timeformat_month', 'g:i a') : 'g:i a',
                'calendarHeight' => self::boundedOption($params->get('calendar_height', 0), 0, 4000),
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
     * Resolve a safe PHP timezone name from Joomla's legacy site offset.
     *
     * Site configurations carried across Joomla versions can contain arrays,
     * invalid timezone names or control characters. Date::getInstance() must
     * not receive those values because PHP 8 throws for invalid timezones and
     * converting an array to a string raises a warning. Allow valid named and
     * numeric-offset PHP timezones, otherwise use UTC for the calendar labels.
     *
     * @param mixed $configured Raw application timezone or legacy offset.
     * @return string Valid PHP timezone identifier, defaulting to UTC.
     */
    private static function safeTimezone(mixed $configured): string
    {
        if (!is_string($configured) || $configured === ''
            || strlen($configured) > 128
            || preg_match('/[\\x00-\\x1F\\x7F]/', $configured) === 1) {
            return 'UTC';
        }

        try {
            new \DateTimeZone($configured);
        } catch (\Throwable) {
            return 'UTC';
        }

        return $configured;
    }

    /**
     * Abbreviate a translated calendar weekday without splitting UTF-8.
     *
     * Uses mbstring when available but supports otherwise compatible PHP
     * installations with a Unicode regular-expression fallback. Names such
     * as Ägypten or 日曜日 must not be cut in the middle of a multibyte
     * character while preparing Joomla's calendar day headers.
     *
     * @param string $name Translated weekday label from Joomla\CMS\Date\Date.
     * @return string First two Unicode characters, or an empty label.
     */
    public static function shortWeekdayLabel(string $name): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($name, 0, 2, 'UTF-8');
        }

        if (preg_match('/^.{1,2}/us', $name, $characters) === 1) {
            return $characters[0];
        }

        // The last-resort byte fallback is only for malformed input, since
        // Joomla's translated weekday names normally contain valid UTF-8.
        return substr($name, 0, 2);
    }

    /**
     * Read a bounded nonnegative integer from a legacy Joomla module option.
     *
     * Accepts integers and decimal strings with optional leading zeros.
     * Booleans, arrays, fractional values, partial numeric strings and
     * integer overflow fall back to the configured default.
     *
     * @param mixed $value Raw module configuration option.
     * @param int $default Safe fallback for malformed values.
     * @param int $maximum Upper bound for the option.
     * @return int Nonnegative option value clamped to the maximum.
     */
    private static function boundedOption(mixed $value, int $default, int $maximum): int
    {
        if (!is_int($value) && (!is_string($value) || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return $default;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? $default : min($maximum, $number);
    }

    /**
     * Load only calendars visible to the active Joomla identity.
     *
     * Applies selected calendar ID filters and both Joomla view-level
     * restrictions, with a bounded database result. Invalid configured
     * selections never fall back to an unrestricted query. Malformed
     * database rows are removed before they reach module rendering.
     * Query or connection failures are logged privately and yield an
     * empty module list rather than crashing the Joomla site page.
     *
     * @param Registry $params Calendar selection module settings.
     * @param CMSApplicationInterface $app Joomla application providing identity.
     * @param DatabaseInterface $db Database used for calendar retrieval.
     * @return array<int,object> Authorised and valid calendar records.
     */
    public function getCalendars(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $db
    ): array {
        try {
            $query = $db->createQuery()
                ->select('*')
                ->from($db->quoteName('#__sportsmanagement_gcalendar'));
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement GCalendar query setup failed: ' . $exception->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
            );

            return [];
        }

        $rawCalendarIds = $params->get('calendarids', []);
        // Only a genuinely empty selection means all visible calendars.
        // A whitespace-only or malformed configured selection must not
        // silently expand into an unrestricted calendar query.
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

        try {
            $query->order($db->quoteName('name') . ' ASC');
            // Match the JSON feed bound even when all permitted calendars
            // are requested: never remove the 50-calendar database limit.
            $db->setQuery($query, 0, self::MAX_CALENDARS);
            $rows = $db->loadObjectList();
        } catch (\Throwable $exception) {
            Log::add(
                'SportsManagement GCalendar query execution failed: ' . $exception->getMessage(),
                Log::ERROR,
                'com_sportsmanagement'
            );

            return [];
        }

        if (!is_array($rows)) {
            return [];
        }

        $calendars = [];

        foreach ($rows as $row) {
            if (!is_object($row)
                || $this->normaliseCalendarIds([$row->id ?? null]) === []) {
                continue;
            }

            $calendars[] = $row;
        }

        return $calendars;
    }

    /**
     * Parse a bounded set of positive calendar IDs from module selections.
     *
     * Accepts integers and comma-, semicolon- or whitespace-delimited
     * decimal strings, including zero-padded legacy IDs. Rejects malformed
     * values and overflow, deduplicates IDs and caps the result at 50.
     *
     * @param mixed $configured Raw Joomla calendar selection.
     * @return array<int,int> Unique positive calendar IDs.
     */
    private function normaliseCalendarIds(mixed $configured): array
    {
        $values = is_array($configured) ? $configured : [$configured];
        $ids = [];

        foreach (array_slice($values, 0, self::MAX_ID_INPUTS) as $value) {
            if ((!is_string($value) && !is_int($value))
                || (is_string($value) && strlen($value) > self::MAX_ID_STRING_LENGTH)) {
                continue;
            }

            $parts = is_int($value) ? [$value] : (preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: []);

            foreach ($parts as $part) {
                if (is_int($part)) {
                    if ($part > 0) {
                        $ids[$part] = $part;
                    }

                    if (count($ids) >= self::MAX_CALENDARS) {
                        return array_values($ids);
                    }

                    continue;
                }

                if (!preg_match('/^[0-9]+$/D', $part)) {
                    continue;
                }

                $digits = ltrim($part, '0');

                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id !== false) {
                    $ids[$id] = $id;
                }

                if (count($ids) >= self::MAX_CALENDARS) {
                    return array_values($ids);
                }
            }
        }

        return array_values($ids);
    }

}
