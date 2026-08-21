<?php
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
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
        $ajax = $input->post->getInt('ajaxCalMod', 0);
        $ajaxModuleId = $input->post->getInt('ajaxmodid', 0);

        if (!$params->get('cal_start_date')) {
            $year = $input->getInt('year', (int) date('Y'));
            $month = $input->getInt('month', (int) date('m'));
            $day = $input->getInt('day', 0);
        } else {
            $startDate = new Date((string) $params->get('cal_start_date'));
            $year = $input->getInt('year', (int) $startDate->format('Y'));
            $month = $input->getInt('month', (int) $startDate->format('m'));
            $day = $ajax ? '' : $input->getInt('day', (int) $startDate->format('d'));
        }

        $year = max(1970, $year);
        $month = max(1, min(12, $month));
        $document = $app->getDocument();
        $this->registerAssets($document->getWebAssetManager(), (string) $module->module);

        $lightbox = (int) $params->get('lightbox', 1);
        $injectContainer = (int) $params->get('inject', 0) === 1
            ? (string) $params->get('inject_container', 'sportsmanagement')
            : '';

        return [
            'ajax' => $ajax,
            'ajaxmod' => $ajaxModuleId,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'document' => $document,
            'lightbox' => $lightbox,
            'inject_container' => $injectContainer,
            'calendar' => $this->showCal($params, $year, $month, (int) $module->id, $ajax, $app),
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
        $app ??= Factory::getApplication();
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

            \JSMCalendar::$linklist[$createdDate]['click'] = 'jlCalmod_showhide(\'jlCalList-' . $moduleId
                . '\', \'jlcal_' . $value['createdYear'] . '-' . $value['createdMonth'] . '-' . $value['createdDay'] . '-' . $moduleId
                . '\', \'' . addslashes(str_replace(' :: ', ': ', $title)) . '\', ' . $inject . ', ' . $moduleId . ');';
            \JSMCalendar::$linklist[$createdDate]['link'] = 'javascript:void(0)" title="'
                . htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        }

        return $calendar->getMonthView($month, $year);
    }

    /**
     * com_ajax entry point: index.php?option=com_ajax&module=sportsmanagement_calendar&method=get&format=raw
     */
    public function getAjax(): string
    {
        $this->bootstrapRuntime();
        require_once dirname(__DIR__, 2) . '/connectors/sportsmanagement_j5.php';

        $app = Factory::getApplication();
        $input = $app->getInput();
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

        $module = ModuleHelper::getModule('mod_sportsmanagement_calendar');
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
        $events = [];

        foreach ($rows as $row) {
            $timestamp = (int) ($row['timestamp'] ?? 0);
            $time = $timestamp > 0 ? date('Y-m-d\\TH:i:s', $timestamp) : '';

            if ($viewName === 'arrobefr') {
                $events[] = [
                    'start' => $timestamp,
                    'end' => $timestamp + 6300,
                    'title' => trim(($row['homename'] ?? '') . ' - ' . ($row['awayname'] ?? '') . ' ' . ($row['result'] ?? '')),
                    'content' => trim(($row['leaguecountry'] ?? '') . ' ' . ($row['leaguename'] ?? '')),
                    'category' => (string) ($row['leaguename'] ?? ''),
                ];
                continue;
            }

            $events[] = [
                'id' => (string) ($row['matchcode'] ?? ''),
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
                'location' => trim(($row['leaguecountry'] ?? '') . ' ' . ($row['leaguename'] ?? '')),
                'attendees' => [],
                'recurrenceRule' => '',
                'title' => trim(($row['homename'] ?? '') . ' - ' . ($row['awayname'] ?? '') . ' ' . ($row['result'] ?? '')),
                'start' => $time,
                'end' => $time,
            ];
        }

        return implode(',', array_map(
            static fn (array $event): string => json_encode(
                $event,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ),
            $events
        ));
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
            'JSMCountries' => JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php',
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

    private function registerAssets(WebAssetManager $assets, string $moduleName): void
    {
        if (self::$assetsRegistered) {
            return;
        }

        $assetBase = 'modules/' . $moduleName . '/assets';
        $assets->useScript('jquery');
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
