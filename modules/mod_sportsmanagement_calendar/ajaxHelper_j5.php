<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;

final class ModSportsmanagementCalendarHelper
{
    public static function getAjax(): string
    {
        require_once __DIR__ . '/connectors/sportsmanagement_j5.php';

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

        SportsmanagementConnector::$params = $moduleParams;
        SportsmanagementConnector::$xparams = $moduleParams;
        SportsmanagementConnector::$prefix = (string) $moduleParams->get('prefix', '');

        $caldates = [
            'start' => $start . ' 00:00:00',
            'end' => $end . ' 23:59:59',
            'starttimestamp' => sportsmanagementHelper::getTimestamp($start . ' 00:00:00'),
            'endtimestamp' => sportsmanagementHelper::getTimestamp($end . ' 23:59:59'),
            'roundstart' => $start,
            'roundend' => $end,
        ];

        $rows = SportsmanagementConnector::loadMatches($caldates);
        $formatted = [];
        $rows = SportsmanagementConnector::formatMatches($rows, $formatted);
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

        // Existing calendar templates expect a comma-separated list of JavaScript objects.
        // Encode every object as strict JSON so values cannot break out into executable code.
        return implode(',', array_map(
            static fn (array $event): string => json_encode(
                $event,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ),
            $events
        ));
    }

    private static function normaliseDate(string $date): ?string
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', trim($date));

        return $parsed instanceof \DateTimeImmutable ? $parsed->format('Y-m-d') : null;
    }
}
