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

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Registry\Registry;

final class CalendarHelper
{
    private static bool $assetsRegistered = false;
    private static bool $runtimeBooted = false;

    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $this->bootstrapRuntime();

        $input = $app->getInput();
        $ajaxModuleId = $input->getInt('ajaxmodid', 0);
        $ajax = $input->getInt('ajaxCalMod', 0) === 1
            && ($ajaxModuleId === 0 || $ajaxModuleId === (int) ($module->id ?? 0))
            ? 1
            : 0;

        if (!$params->get('cal_start_date')) {
            $year = $input->getInt('year', (int) date('Y'));
            $month = $input->getInt('month', (int) date('m'));
            $day = $input->getInt('day', 0);
        } else {
            $startDate = new Date((string) $params->get('cal_start_date'));
            $year = $input->getInt('year', (int) $startDate->format('Y'));
            $month = $input->getInt('month', (int) $startDate->format('m'));
            $day = $ajax ? 0 : $input->getInt('day', (int) $startDate->format('d'));
        }

        $year = max(1970, $year);
        $month = max(1, min(12, $month));
        $document = $app->getDocument();
        $this->registerAssets(
            $document->getWebAssetManager(),
            (string) $module->module,
            (string) $params->get('which_layout', 'default_jsm')
        );

        $lightbox = (int) $params->get('lightbox', 1);
        $injectContainer = (int) $params->get('inject', 0) === 1
            ? (string) $params->get('inject_container', 'sportsmanagement')
            : '';
        $calendarData = $this->showCal($params, $year, $month, (int) $module->id, $ajax, $app);
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
            'selected_team' => $input->getInt('jlcteam', 0),
            'calendar' => $calendarData,
            'matches' => $matches,
            'tui_events' => self::buildTuiEvents($matches, $offset),
            'arrobe_events' => self::buildArrobeEvents($matches, $offset),
        ];
    }

    public function showCal(
        Registry $params,
        int $year,
        int $month,
        int $moduleId,
        int $ajax = 0,
        ?CMSApplicationInterface $app = null
    ): array {
        $this->bootstrapRuntime();
        $app ??= Factory::getContainer()->get(SiteApplication::class);
        $app->getLanguage()->load('mod_sportsmanagement_calendar');

        $calendar = new \JSMCalendar();
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
        $calendar->modid = $moduleId;
        $calendar->ajax = $ajax;

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
        $app = Factory::getContainer()->get(SiteApplication::class);
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

        $app = Factory::getContainer()->get(SiteApplication::class);
        $input = $app->getInput();
        $module = $this->requestedModule($input->getInt('module_id', 0));

        if ($module === null) {
            return '';
        }

        $viewName = $input->getCmd('viewname', 'range');
        $year = max(1970, $input->getInt('formvalueyear', (int) date('Y')));
        $month = max(1, min(12, $input->getInt('formvaluemonth', (int) date('m'))));
        $day = max(1, min(31, $input->getInt('formvalueday', 1)));

        switch ($viewName) {
            case 'day':
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

        $moduleParams = new Registry();
        $moduleParams->loadString((string) ($module->params ?? ''));
        $moduleParams->set('prefix', (string) $moduleParams->get('custom_prefix', ''));

        \SportsmanagementConnector::$params = $moduleParams;
        \SportsmanagementConnector::$xparams = $moduleParams;
        \SportsmanagementConnector::$prefix = (string) $moduleParams->get('prefix', '');

        $caldates = [
            'start' => $start . ' 00:00:00',
            'end' => $end . ' 23:59:59',
            'starttimestamp' => \sportsmanagementHelper::getTimestamp($start . ' 00:00:00'),
            'endtimestamp' => \sportsmanagementHelper::getTimestamp($end . ' 23:59:59'),
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

    private function requestedModule(int $moduleId): ?object
    {
        if ($moduleId <= 0) {
            return null;
        }

        $module = ModuleHelper::getModuleById($moduleId);

        if (
            !is_object($module)
            || (int) ($module->id ?? 0) !== $moduleId
            || (string) ($module->module ?? '') !== 'mod_sportsmanagement_calendar'
        ) {
            return null;
        }

        return $module;
    }

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

        $legacyClasses = [
            'sportsmanagementHelper' => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php',
            'sportsmanagementHelperRoute' => JPATH_SITE . '/components/com_sportsmanagement/helpers/route.php',
        ];

        foreach ($legacyClasses as $class => $path) {
            if (!class_exists($class) && is_file($path)) {
                require_once $path;
            }
        }

        $base = dirname(__DIR__, 2);
        require_once $base . '/calendarClass.php';
        require_once $base . '/calendarFunctions.php';
        require_once $base . '/connectors/calendarRuntime_j5.php';
    }

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

    private static function eventId(array $row, int|string $index): string
    {
        return implode('-', array_filter([
            (string) ($row['type'] ?? 'event'),
            (string) ($row['project_id'] ?? '0'),
            (string) ($row['matchcode'] ?? $index),
            (string) $index,
        ], static fn (string $part): bool => $part !== ''));
    }

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

    private static function dateFromRow(array $row, string $offset): ?Date
    {
        if (isset($row['timestamp']) && (int) $row['timestamp'] > 0) {
            return self::dateFromValue((int) $row['timestamp'], $offset);
        }

        return isset($row['date']) ? self::dateFromValue($row['date'], $offset) : null;
    }

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

    private static function normaliseDate(string $date): ?string
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', trim($date));

        return $parsed instanceof \DateTimeImmutable ? $parsed->format('Y-m-d') : null;
    }
}
