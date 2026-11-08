<?php
/**
 * Joomla 5/6 helper for the SportsManagement calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Registry\Registry;

final class CalendarHelper
{
    private static bool $assetsRegistered = false;
    private static bool $runtimeBooted = false;

    /**
     * Validate the selected SportsManagement calendar layout name.
     * Allows only a short alphanumeric layout identifier with hyphens
     * and underscores, preventing directory traversal through module settings.
     * @param string $layout Raw layout selection.
     * @return string Safe calendar layout, or default_jsm.
     */
    public static function normaliseLayout(string $layout): string
    {
        $layout = trim($layout);

        // A legacy module parameter must not supply a path or traversal segment.
        if ($layout === '' || strlen($layout) > 100 || !preg_match('/^[A-Za-z0-9_-]+$/D', $layout)) {
            return 'default_jsm';
        }

        return match ($layout) {
            'default_arrobefr', 'default_tuicalendar' => 'default_jsm',
            default => $layout,
        };
    }

    /**
     * Calendar views and AJAX requests use four-digit dates; avoid producing
     * invalid years when a legacy parameter or query string is out of range.
     *
     * @param int $year Requested calendar year from module settings or input.
     * @return int Year bounded to the supported range from 1970 through 9999.
     */
    public static function normaliseYear(int $year): int
    {
        return min(9999, max(1970, $year));
    }

    /**
     * Prepare the native calendar module's data and layout configuration.
     * Bootstraps component dependencies, reads the selected calendar view,
     * normalises date and module settings, registers page assets, and
     * returns events or layout configuration for template rendering.
     * Translated weekday labels are truncated by Unicode characters even
     * when the mbstring extension is not installed.
     * @param Registry $params Calendar module options.
     * @param object $module Active Joomla module instance.
     * @param CMSApplicationInterface $app Joomla site application.
     * @return array Calendar data and rendering configuration.
     */
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $this->bootstrapRuntime();

        $layout = self::normaliseLayout((string) $params->get(
            'which_layout',
            $params->get('layout', 'default_jsm')
        ));
        $params->set('which_layout', $layout);
        $params->set('layout', $layout);

        $input = $app->getInput();
        $ajaxModuleId = max(0, $input->getInt('ajaxmodid', 0));
        $ajax = $input->getInt('ajaxCalMod', 0) === 1
            && ($ajaxModuleId === 0 || $ajaxModuleId === (int) ($module->id ?? 0))
            ? 1
            : 0;

        $rawStartDate = $params->get('cal_start_date');
        $startDate = null;

        // Old module configurations can contain invalid dates or even arrays.
        // An invalid optional date should fall back to the current month,
        // not throw and prevent the rest of the Joomla site from rendering.
        if (is_string($rawStartDate) && trim($rawStartDate) !== '') {
            try {
                $startDate = new Date($rawStartDate);
            } catch (\Throwable) {
                $startDate = null;
            }
        }

        if ($startDate === null) {
            $year = $input->getInt('year', (int) date('Y'));
            $month = $input->getInt('month', (int) date('m'));
            $day = $input->getInt('day', 0);
        } else {
            $year = $input->getInt('year', (int) $startDate->format('Y'));
            $month = $input->getInt('month', (int) $startDate->format('m'));
            $day = $ajax ? 0 : $input->getInt('day', (int) $startDate->format('d'));
        }

        $year = self::normaliseYear($year);
        $month = max(1, min(12, $month));
        $day = max(0, min(31, $day));

        // Day zero denotes the monthly overview; invalid dates must not be rendered.
        if ($day !== 0 && !checkdate($month, $day, $year)) {
            $day = 0;
        }
        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $this->registerAssets(
                $document->getWebAssetManager(),
                (string) ($module->module ?? 'mod_sportsmanagement_calendar'),
                $layout
            );
        }

        $lightbox = (int) $params->get('lightbox', 1) === 1 ? 1 : 0;
        $rawInjectContainer = $params->get('inject_container', 'sportsmanagement');
        $injectContainer = (int) $params->get('inject', 0) === 1
            && is_string($rawInjectContainer)
            && strlen($rawInjectContainer) <= 100
            && preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/D', $rawInjectContainer)
                ? $rawInjectContainer
                : '';
        $calendarData = $this->showCal($params, $year, $month, max(0, (int) ($module->id ?? 0)), $ajax, $app);
        $matches = \JSMCalendar::$matches;
        $offset = (string) $app->get('offset', 'UTC');

        return [
            'ajax' => $ajax,
            'ajaxmod' => $ajaxModuleId,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'document' => $document,
            'lightbox' => $lightbox,
            'inject_container' => $injectContainer,
            'selected_team' => max(0, $input->getInt('jlcteam', 0)),
            'calendar' => $calendarData,
            'matches' => $matches,
            'tui_events' => self::buildTuiEvents($matches, $offset),
            'arrobe_events' => self::buildArrobeEvents($matches, $offset),
        ];
    }

    /**
     * Build the legacy monthly calendar data for a specific module.
     * Initialises the shared calendar class, applies translated display
     * settings and produces a month grid for the selected year and month.
     * @param Registry $params Calendar display settings.
     * @param int $year Requested calendar year.
     * @param int $month Requested calendar month.
     * @param int $moduleId Joomla module identifier.
     * @param int $ajax Whether the content is requested through AJAX.
     * @param CMSApplicationInterface|null $app Optional site app.
     * @return array Calendar grid and associated options.
     */
    public function showCal(
        Registry $params,
        int $year,
        int $month,
        int $moduleId,
        int $ajax = 0,
        ?CMSApplicationInterface $app = null
    ): array {
        $this->bootstrapRuntime();
        $app ??= $this->siteApplication();
        $app->getLanguage()->load('mod_sportsmanagement_calendar');

        $calendar = new \JSMCalendar();
        $dayNameLength = min(20, max(1, (int) $params->get('cal_length_days', 2)));

        // Truncate by characters rather than bytes for translated weekday labels.
        $shortDayName = static function (string $name) use ($dayNameLength): string {
            if (function_exists('mb_substr')) {
                return mb_substr($name, 0, $dayNameLength, 'UTF-8');
            }

            // Without mbstring, split into Unicode code points so weekday
            // abbreviations do not contain partial UTF-8 byte sequences.
            if (preg_match_all('/./us', $name, $characters) !== false) {
                return implode('', array_slice($characters[0], 0, $dayNameLength));
            }

            return substr($name, 0, $dayNameLength);
        };

        $calendar->dayNames = [
            $shortDayName(Text::_('SUN')),
            $shortDayName(Text::_('MON')),
            $shortDayName(Text::_('TUE')),
            $shortDayName(Text::_('WED')),
            $shortDayName(Text::_('THU')),
            $shortDayName(Text::_('FRI')),
            $shortDayName(Text::_('SAT')),
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

        $calendar->startDay = min(6, max(0, (int) $params->get('cal_start_day', 0)));
        $calendar->lightbox = (int) $params->get('lightbox', 1) === 1 ? 1 : 0;
        $calendar->lightbox_on_pageload = (int) $params->get('lightbox_on_pageload', 0) === 1 ? 1 : 0;
        $usedTeams = $params->get('usedteams', '');
        $usedClubs = $params->get('usedclubs', '');
        $calendar->usedteams = is_string($usedTeams) || is_array($usedTeams) ? $usedTeams : '';
        $calendar->usedclubs = is_string($usedClubs) || is_array($usedClubs) ? $usedClubs : '';
        // Never pass negative module IDs or non-boolean AJAX flags into the legacy runtime.
        $calendar->modid = max(0, $moduleId);
        $calendar->ajax = $ajax === 1 ? 1 : 0;

        \JSMCalendar::$prefix = (string) $params->get('custom_prefix', '');
        \JSMCalendar::$params = $params;
        \JSMCalendar::getMatches($month, $year);

        $counter = [];
        $offset = (string) $app->get('offset', 'UTC');

        foreach (\JSMCalendar::$matches as $row) {
            if (!isset($row['date'])) {
                continue;
            }

            $created = self::dateFromValue($row['date'], $offset);

            if (!$created) {
                continue;
            }

            $createdDate = $created->format('Ymd');

            if (!isset($counter[$createdDate])) {
                $counter[$createdDate] = [
                    'tiptitle' => $created->format('l, d.m.Y'),
                    'count' => 0,
                ];
            }

            $counter[$createdDate]['count']++;
        }

        $single = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCH');
        $plural = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHES');
        $dayLabel = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_MATCHTHISDAY');
        foreach ($counter as $createdDate => $value) {
            $title = $value['tiptitle'] . ' :: ' . $value['count'] . ' ';
            $title .= $value['count'] > 1 ? $plural : $single;
            $title .= ' ' . $dayLabel;

            \JSMCalendar::$linklist[$createdDate] = [
                'title' => str_replace(' :: ', ': ', $title),
                'hasAction' => true,
            ];
        }

        return $calendar->getMonthView($month, $year);
    }

    /**
     * Module-instance-safe calendar fragment refresh.
     *
     * Endpoint: index.php?option=com_ajax&module=sportsmanagement_calendar&method=refresh&format=raw
     */
    public function refreshAjax(): string
    {
        $app = $this->siteApplication();
        $module = $this->requestedModule($app->getInput()->getInt('module_id', 0));

        if ($module === null) {
            return '';
        }

        $params = new Registry();
        $params->loadString((string) ($module->params ?? ''));
        $data = array_merge(
            ['module' => $module, 'params' => $params],
            $this->getData($params, $module, $app)
        );

        extract($data, EXTR_SKIP);

        ob_start();
        require ModuleHelper::getLayoutPath('mod_sportsmanagement_calendar', 'default_jsm');
        $html = (string) ob_get_clean();

        $startMarker = '<!--jlccalendar-' . (int) $module->id . ' start-->';
        $endMarker = '<!--jlccalendar-' . (int) $module->id . ' end-->';
        $start = strpos($html, $startMarker);
        $end = strpos($html, $endMarker);

        if ($start === false || $end === false || $end <= $start) {
            return '';
        }

        $start += strlen($startMarker);

        return trim(substr($html, $start, $end - $start));
    }

    /**
     * Read-only event feed kept for existing calendar integrations.
     *
     * Endpoint: index.php?option=com_ajax&module=sportsmanagement_calendar&method=get&format=raw
     */
    public function getAjax(): string
    {
        $this->bootstrapRuntime();
        require_once dirname(__DIR__, 2) . '/connectors/sportsmanagement_j5.php';

        $app = $this->siteApplication();
        $input = $app->getInput();
        $module = $this->requestedModule($input->getInt('module_id', 0));

        if ($module === null) {
            return '';
        }

        $viewName = $input->getCmd('viewname', 'range');
        $year = self::normaliseYear($input->getInt('formvalueyear', (int) date('Y')));
        $month = max(1, min(12, $input->getInt('formvaluemonth', (int) date('m'))));
        $day = max(1, min(31, $input->getInt('formvalueday', 1)));

        switch ($viewName) {
            case 'day':
                if (!checkdate($month, $day, $year)) {
                    return '';
                }

                $start = $end = sprintf('%04d-%02d-%02d', $year, $month, $day);
                break;

            case 'month':
                $first = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
                $start = $first->format('Y-m-d');
                $end = $first->modify('last day of this month')->format('Y-m-d');
                break;

            default:
                $start = self::normaliseDate($input->getString('daterangevon', ''));
                $end = self::normaliseDate($input->getString('daterangebis', ''));

                if ($start === null || $end === null) {
                    return '';
                }
                break;
        }

        if ($start > $end) {
            return '';
        }

        $moduleParams = new Registry();
        $moduleParams->loadString((string) ($module->params ?? ''));
        $moduleParams->set('prefix', (string) $moduleParams->get('custom_prefix', ''));

        \SportsmanagementConnector::$params = $moduleParams;
        \SportsmanagementConnector::$xparams = $moduleParams;
        \SportsmanagementConnector::$prefix = (string) $moduleParams->get('prefix', '');

        $startDateTime = $start . ' 00:00:00';
        $endDateTime = $end . ' 23:59:59';
        $caldates = [
            'start' => $startDateTime,
            'end' => $endDateTime,
            'starttimestamp' => self::timestamp($startDateTime),
            'endtimestamp' => self::timestamp($endDateTime),
            'roundstart' => $start,
            'roundend' => $end,
        ];

        $rows = \SportsmanagementConnector::loadMatches($caldates);
        $formatted = [];
        $rows = \SportsmanagementConnector::formatMatches($rows, $formatted);
        $offset = (string) $app->get('offset', 'UTC');
        $events = $viewName === 'arrobefr'
            ? self::buildArrobeEvents($rows, $offset)
            : self::buildTuiEvents($rows, $offset);

        return self::encodeEventList($events);
    }

    /**
     * Resolve a published calendar module by its numeric Joomla ID.
     * Rejects invalid identifiers and returns only a matching module
     * instance, avoiding reuse of another module's private parameters.
     * @param int $moduleId Module ID from a calendar AJAX request.
     * @return object|null Matching enabled module or null.
     */
    private function requestedModule(int $moduleId): ?object
    {
        if ($moduleId <= 0) {
            return null;
        }

        // Joomla compares stored module IDs as strings when resolving instances.
        $module = ModuleHelper::getModuleById((string) $moduleId);

        if (
            !is_object($module)
            || (int) ($module->id ?? 0) !== $moduleId
            || (string) ($module->module ?? '') !== 'mod_sportsmanagement_calendar'
        ) {
            return null;
        }

        return $module;
    }

    /**
     * Retrieve the Joomla site application for calendar endpoints.
     * Uses the SportsManagement native resolver to avoid deprecated
     * global application access during module AJAX requests.
     * @return CMSApplicationInterface Current frontend application.
     */
    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Load the calendar's shared compatibility services once per request.
     * Initialises necessary component definitions and legacy calendar
     * classes without repeating bootstrap work for multiple modules.
     * @return void
     */
    private function bootstrapRuntime(): void
    {
        if (self::$runtimeBooted) {
            return;
        }

        self::$runtimeBooted = true;

        if (!\defined('JSM_PATH')) {
            \define('JSM_PATH', 'components/com_sportsmanagement');
        }

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');

        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
            \define(
                'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',
                $componentParams->get('show_debug_info', 0)
            );
        }

        if (!\defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
            \define(
                'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',
                $componentParams->get('cfg_which_database')
            );
        }

        $base = dirname(__DIR__, 2);
        require_once $base . '/calendarClass.php';
        require_once $base . '/calendarFunctions.php';
        require_once $base . '/connectors/calendarRuntime_j5.php';
    }

    /**
     * Register the scripts and styles required by a calendar layout.
     * Selects resources for the supported Arrobe, TUI or traditional
     * calendar variants and avoids duplicate registration on one page.
     * @param WebAssetManager $assets Joomla document asset registry.
     * @param string $moduleName Joomla module directory name.
     * @param string $layout Validated calendar layout name.
     * @return void
     */
    private function registerAssets(WebAssetManager $assets, string $moduleName, string $layout): void
    {
        if ($layout === 'default_arrobefr') {
            $assets->useScript('jquery');
            return;
        }

        if ($layout === 'default_tuicalendar') {
            return;
        }

        if (self::$assetsRegistered) {
            return;
        }

        $assetBase = 'modules/' . $moduleName . '/assets';
        $assets->registerAndUseScript(
            $moduleName . '.calendar',
            $assetBase . '/js/' . $moduleName . '.js',
            [],
            ['defer' => true]
        );
        $assets->registerAndUseStyle(
            $moduleName . '.calendar',
            $assetBase . '/css/' . $moduleName . '.css'
        );
        $assets->useScript('bootstrap.modal');

        self::$assetsRegistered = true;
    }

    /**
     * Convert calendar rows into the TUI Calendar event structure.
     * Parses row dates in the site timezone, discards invalid entries
     * and assembles titles, locations and stable IDs for browser rendering.
     * @param array $rows Calendar events or fixture rows.
     * @param string $offset Configured timezone identifier.
     * @return array<int,array> TUI calendar events.
     */
    private static function buildTuiEvents(array $rows, string $offset): array
    {
        $events = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $date = self::dateFromRow($row, $offset);

            if (!$date) {
                continue;
            }

            $events[] = [
                'id' => self::eventId($row, $index),
                'calendarId' => '1',
                'category' => 'time',
                'dueDateClass' => '',
                'isReadOnly' => true,
                'isAllDay' => false,
                'goingDuration' => 30,
                'comingDuration' => 30,
                'color' => '#ffffff',
                'bgColor' => '#69BB2D',
                'dragBgColor' => '#69BB2D',
                'borderColor' => '#69BB2D',
                'customStyle' => 'cursor: default;',
                'isPending' => false,
                'isFocused' => false,
                'isPrivate' => false,
                'isVisible' => true,
                'location' => self::eventLocation($row),
                'attendees' => [],
                'recurrenceRule' => '',
                'title' => self::eventTitle($row),
                'start' => $date->format('Y-m-d\\TH:i:sP'),
                'end' => $date->format('Y-m-d\\TH:i:sP'),
            ];
        }

        return $events;
    }

    /**
     * Prepare calendar events for the legacy Arrobe display.
     * Converts accepted date rows to UNIX timestamps and maps their
     * labels and locations into Arrobe's JavaScript event representation.
     * @param array $rows Raw SportsManagement calendar rows.
     * @param string $offset Joomla site timezone.
     * @return array<int,array> Calendar events for the Arrobe widget.
     */
    private static function buildArrobeEvents(array $rows, string $offset): array
    {
        $events = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $date = self::dateFromRow($row, $offset);

            if (!$date) {
                continue;
            }

            $timestamp = $date->getTimestamp();
            $events[] = [
                'start' => $timestamp,
                'end' => $timestamp + 6300,
                'title' => self::eventTitle($row),
                'content' => self::eventLocation($row),
                'category' => (string) ($row['leaguename'] ?? $row['headingtitle'] ?? ''),
            ];
        }

        return $events;
    }

    /**
     * Create a stable event identifier for a calendar row.
     * Combines the event type, project and match code with its position
     * so different calendar sources have distinct browser-side entries.
     * @param array $row Event data from the selected connector.
     * @param int|string $index Index or key in the calendar result.
     * @return string Stable event identifier.
     */
    private static function eventId(array $row, int|string $index): string
    {
        return implode('-', array_filter([
            (string) ($row['type'] ?? 'event'),
            (string) ($row['project_id'] ?? '0'),
            (string) ($row['matchcode'] ?? $index),
            (string) $index,
        ], static fn (string $part): bool => $part !== ''));
    }

    /**
     * Format a readable event title from a connector row.
     * Uses event-specific wording for Joomla events and birthdays,
     * or the participant names for match fixtures.
     * @param array $row Source row containing type and event fields.
     * @return string Display title for the calendar event.
     */
    private static function eventTitle(array $row): string
    {
        return match ((string) ($row['type'] ?? '')) {
            'jevents' => trim((string) ($row['title'] ?? '')),
            'jlb' => trim((string) ($row['name'] ?? '') . ' ' . (string) ($row['age'] ?? '')),
            default => trim(
                (string) ($row['homename'] ?? '') . ' - '
                . (string) ($row['awayname'] ?? '') . ' '
                . (string) ($row['result'] ?? '')
            ),
        };
    }

    /**
     * Format the calendar location from event metadata.
     * Uses the explicit location for Joomla events and league/country
     * information for SportsManagement fixture records.
     * @param array $row Source calendar record.
     * @return string Human-readable location description.
     */
    private static function eventLocation(array $row): string
    {
        if ((string) ($row['type'] ?? '') === 'jevents') {
            return trim((string) ($row['location'] ?? ''));
        }

        return trim(
            (string) ($row['leaguecountry'] ?? '') . ' '
            . (string) ($row['leaguename'] ?? '')
        );
    }

    /**
     * Resolve a row's event date in the configured timezone.
     * Prefers a positive timestamp and falls back to the stored date
     * field; malformed values return null rather than an invented date.
     * @param array $row Calendar event record.
     * @param string $offset Joomla timezone identifier.
     * @return Date|null Valid Joomla date or null.
     */
    private static function dateFromRow(array $row, string $offset): ?Date
    {
        if (isset($row['timestamp']) && (int) $row['timestamp'] > 0) {
            return self::dateFromValue((int) $row['timestamp'], $offset);
        }

        return isset($row['date']) ? self::dateFromValue($row['date'], $offset) : null;
    }

    /**
     * Serialize a list of calendar entries for legacy JavaScript.
     * Encodes each event with JSON escaping and joins the encoded
     * records in the comma-separated format expected by the widget.
     * @param array<int,array> $events Prepared calendar event payloads.
     * @return string Comma-separated JSON event objects.
     */
    private static function encodeEventList(array $events): string
    {
        return implode(',', array_map(
            static fn (array $event): string => json_encode(
                $event,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ),
            $events
        ));
    }

    /**
     * Parse a textual date into a UNIX timestamp for the calendar.
     * Returns zero on an invalid date string so callers can ignore
     * unusable values rather than treating them as valid fixtures.
     * @param string $value Calendar date string.
     * @return int UNIX timestamp or zero when parsing fails.
     */
    private static function timestamp(string $value): int
    {
        $timestamp = strtotime($value);

        return $timestamp === false ? 0 : $timestamp;
    }

    /**
     * Construct a Joomla Date from a timestamp or date-like value.
     * Applies the site timezone, handles numeric UNIX timestamps
     * and reports invalid date input as null.
     * @param mixed $value Timestamp or supported date string.
     * @param string $offset Joomla site timezone.
     * @return Date|null Calendar date if parsing succeeds.
     */
    private static function dateFromValue(mixed $value, string $offset): ?Date
    {
        try {
            if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                $date = new Date('@' . (int) $value);
                $date->setTimezone(new \DateTimeZone($offset ?: 'UTC'));

                return $date;
            }

            return new Date((string) $value, $offset ?: 'UTC');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Validate calendar input dates without accepting PHP date rollover.
     * Requires an exact YYYY-MM-DD representation and verifies the
     * parsed components to reject impossible calendar days.
     * @param string $date Raw selected date from a Joomla request.
     * @return string|null Valid ISO date or null.
     */
    private static function normaliseDate(string $date): ?string
    {
        $date = trim($date);

        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date)) {
            return null;
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        // PHP normalizes e.g. February 30 to March 2. Calendar requests
        // must not silently load matches for a different day.
        return $parsed instanceof \DateTimeImmutable && $parsed->format('Y-m-d') === $date
            ? $date
            : null;
    }
}
