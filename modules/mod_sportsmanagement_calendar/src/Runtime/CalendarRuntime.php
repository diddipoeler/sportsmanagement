<?php
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Runtime;

\defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!class_exists('PHPCalendar', false)) {
    require_once dirname(__DIR__, 2) . '/calendarClass.php';
}

/**
 * Native Joomla 5/6 runtime for the SportsManagement calendar module.
 *
 * The legacy JSMCalendar class name is retained through a compatibility alias
 * in connectors/calendarRuntime_j5.php because the existing connector classes
 * still extend that historical name.
 */
class CalendarRuntime extends \PHPCalendar
{
    public static array $linklist = [];
    public static $prefix = '';
    public static $params;
    public static array $matches = [];
    public static array $teams = [];
    public static array $teamslist = [];

    public static function addTeam($id, string $name = '', string $pic = ''): void
    {
        $id = (int) $id;

        if ($id <= 0 || isset(self::$teams[$id])) {
            return;
        }

        $team = new \stdClass();
        $team->value = $id;
        $team->name = $name;
        $team->picture = $pic;
        self::$teams[$id] = $team;
        self::$teamslist[] = $team;
    }

    public static function jl_utf8_convert($text, string $fromenc = 'iso-8859-1', string $toenc = 'UTF-8')
    {
        $text = (string) $text;
        $convert = class_exists('SportsmanagementConnector', false)
            && isset(\SportsmanagementConnector::$params)
            ? (int) \SportsmanagementConnector::$params->get('convert', 0)
            : 0;

        if ($convert === 0 || strcasecmp($fromenc, $toenc) === 0) {
            return $text;
        }

        if (function_exists('iconv')) {
            $converted = @iconv($fromenc, $toenc . '//TRANSLIT', $text);

            if ($converted !== false) {
                return $converted;
            }
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, $toenc, $fromenc);
        }

        return $text;
    }

    public static function asc($a, $b): int
    {
        return $a <=> $b;
    }

    public static function desc($a, $b): int
    {
        return $b <=> $a;
    }

    public static function getMatches($month, $year): array
    {
        $month = max(1, min(12, (int) $month));
        $year = max(1970, (int) $year);
        $app = Factory::getApplication();
        $timezoneName = trim((string) self::$params->get('time_zone', $app->get('offset', 'UTC')));

        try {
            $timezone = new \DateTimeZone($timezoneName !== '' ? $timezoneName : 'UTC');
        } catch (\Throwable) {
            $timezone = new \DateTimeZone('UTC');
        }

        $firstDay = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $year, $month), $timezone);
        $lastDay = $firstDay->modify('last day of this month')->setTime(23, 59, 59);

        self::$matches = [];
        self::$teams = [];
        self::$teamslist = [];
        self::$linklist = [];

        $caldates = [
            'start' => $firstDay->format('Y-m-d H:i:s'),
            'end' => $lastDay->format('Y-m-d H:i:s'),
            'starttimestamp' => $firstDay->getTimestamp(),
            'endtimestamp' => $lastDay->getTimestamp(),
            'roundstart' => $firstDay->format('Y-m-d'),
            'roundend' => $lastDay->format('Y-m-d'),
        ];

        if ((int) self::$params->get('jevents', 0) === 1) {
            require_once dirname(__DIR__, 2) . '/connectors/jevents_j5.php';
            \JEventsConnector::getEntries($caldates, self::$params, self::$matches);
        }

        require_once dirname(__DIR__, 2) . '/connectors/sportsmanagement_j5.php';
        self::$params->set('prefix', self::$prefix);
        \SportsmanagementConnector::getEntries($caldates, self::$params, self::$matches);

        if ((string) self::$params->get('livescore', '') !== '') {
            require_once dirname(__DIR__, 2) . '/connectors/livescore_j5.php';
            self::$params->set('prefix', (string) self::$params->get('prefix_livescore', ''));
            $livescore = new \LivescoreConnector();
            $livescore->appendMatches($caldates, self::$params, self::$matches);
        }

        self::$matches = self::sortArray(self::$matches, 'asc', 'date');

        return self::$matches;
    }

    public static function sortArray(array $array, string $comparefunction, string $property = ''): array
    {
        $descending = strtolower($comparefunction) === 'desc';
        usort(
            $array,
            static function ($left, $right) use ($property, $descending): int {
                $a = $property !== '' ? ($left[$property] ?? null) : $left;
                $b = $property !== '' ? ($right[$property] ?? null) : $right;
                $result = $a <=> $b;

                return $descending ? -$result : $result;
            }
        );

        return $array;
    }

    public function getDateMeta($day, $month, $year): array
    {
        $date = sprintf('%04d%02d%02d', (int) $year, (int) $month, (int) $day);
        $meta = self::$linklist[$date] ?? [];

        return is_array($meta) ? $meta : [];
    }

    public function getCalendarLink($month, $year): string
    {
        $uri = Uri::getInstance();
        $query = $uri->getQuery(true);
        unset($query['month'], $query['year'], $query['day']);
        $query['month'] = (int) $month;
        $query['year'] = (int) $year;

        $base = Uri::current();
        $encoded = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $encoded === '' ? $base : $base . '?' . $encoded;
    }

    public function matches_output($month, $year): array
    {
        $app = Factory::getApplication();
        $offset = (string) $app->get('offset', 'UTC');
        $language = $app->getLanguage();
        $language->load('mod_sportsmanagement_calendar');

        $single = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCH');
        $plural = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHES');
        $none = Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_NOMATCHES');
        $matches = self::$matches;
        $today = (new Date('now', $offset))->format('Y-m-d');
        $total = count($matches);
        $totalGames = ($total > 0 ? (string) $total : $none) . ' '
            . ($total > 1 ? $plural : $single) . ' '
            . Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_VALUEMATCHESMONTH') . ' '
            . ($this->monthNames[(int) $month - 1] ?? '') . ' ' . (int) $year;

        $format = [
            ['tag' => 'span', 'divid' => 'oldjlCalListTitle-' . $this->modid, 'class' => 'jlcal_hiddenmatches', 'text' => $totalGames],
            ['tag' => 'span', 'divid' => 'jlCalListTitle-' . $this->modid, 'class' => 'jlCalListTitle', 'text' => $totalGames],
            ['tag' => 'span', 'divid' => 'jlCalListDayTitle-' . $this->modid, 'class' => 'jlCalListTitle', 'text' => ''],
        ];

        $currentDate = '';
        $currentProjectMatch = '';
        $counter = 0;

        foreach ($matches as $index => $row) {
            if (!isset($row['date'])) {
                continue;
            }

            $date = CalendarDate::fromValue($row['date'], $offset);

            if (!$date) {
                continue;
            }

            $dateKey = $date->format('Y-m-d');
            $projectMatch = ($row['project_id'] ?? '') . '_' . ($row['matchcode'] ?? '') . '_' . ($row['type'] ?? '');

            if ($currentDate !== $dateKey) {
                $counter = 0;
                $currentDate = $dateKey;
                $format[] = ['tag' => 'div', 'divid' => 'jlcal_' . $dateKey . '-' . $this->modid, 'class' => 'jlcal_hiddenmatches'];
                $format[] = ['tag' => 'table', 'divid' => 'jlcal_' . $dateKey . '-' . $this->modid, 'class' => 'jlcal_result_table'];
            }

            if ($currentProjectMatch !== $projectMatch) {
                $format[] = ['tag' => 'headingrow', 'text' => $row['headingtitle'] ?? ''];
            }

            $currentProjectMatch = $projectMatch;
            $format[] = $row;
            $counter++;

            $next = $matches[$index + 1] ?? null;
            $nextDateKey = null;

            if (is_array($next) && isset($next['date'])) {
                $nextDate = CalendarDate::fromValue($next['date'], $offset);
                $nextDateKey = $nextDate?->format('Y-m-d');
            }

            if ($nextDateKey !== $dateKey) {
                $currentProjectMatch = '';
                $format[] = ['tag' => 'tableend'];
                $format[] = ['tag' => 'divend'];
                $title = $counter . ' ' . ($counter > 1 ? $plural : $single) . ' ';
                $title .= $today === $dateKey
                    ? Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_TODAY')
                    : Text::_('MOD_SPORTSMANAGEMENT_CALENDAR_AT');
                $title .= ' ' . $date->format('d') . '. '
                    . ($this->monthNames[(int) $month - 1] ?? '') . ' ' . (int) $year;
                $format[] = [
                    'tag' => 'span',
                    'divid' => 'jlcaltitte_' . $dateKey . '-' . $this->modid,
                    'class' => 'jlcal_hiddenmatches',
                    'text' => $title,
                ];
            }
        }

        return $format;
    }

    public function output_teamlist(): array
    {
        if (self::$teams === []) {
            return [];
        }

        $teams = self::sortObject(self::$teamslist, 'asc', 'name');
        $options = [HTMLHelper::_('select.option', 0, Text::_((string) self::$params->get('teamslist_option')))];

        foreach ($teams as $team) {
            $options[] = HTMLHelper::_('select.option', $team->value, Text::_((string) $team->name));
        }

        return $options;
    }

    public static function sortObject(array $array, string $comparefunction, string $property = ''): array
    {
        $descending = strtolower($comparefunction) === 'desc';
        usort(
            $array,
            static function ($left, $right) use ($property, $descending): int {
                $a = $property !== '' ? ($left->{$property} ?? null) : $left;
                $b = $property !== '' ? ($right->{$property} ?? null) : $right;
                $result = $a <=> $b;

                return $descending ? -$result : $result;
            }
        );

        return $array;
    }
}
