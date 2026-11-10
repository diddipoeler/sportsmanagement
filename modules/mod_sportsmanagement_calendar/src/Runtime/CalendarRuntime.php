<?php
/**
 * Native Joomla 5/6 runtime for the SportsManagement calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Runtime;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Date\Date;
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

    /**
     * Register a strictly validated project team for the calendar filter.
     *
     * Accepts positive IDs and legacy decimal strings with leading zeros,
     * while rejecting partial numbers, nested values and integer overflow.
     * Duplicate numeric representations select the same stored team.
     *
     * @param mixed $id Raw project-team identifier from a connector.
     * @param string $name Display name of the team.
     * @param string $pic Optional team image URL.
     * @return void
     */
    public static function addTeam($id, string $name = '', string $pic = ''): void
    {
        if (is_string($id)) {
            $id = trim($id);

            if ($id === '' || strlen($id) > 64 || !ctype_digit($id)) {
                return;
            }

            $id = ltrim($id, '0');

            if ($id === '') {
                return;
            }
        } elseif (!is_int($id)) {
            return;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($id === false || isset(self::$teams[$id])) {
            return;
        }

        $team = new \stdClass();
        $team->value = $id;
        $team->name = $name;
        $team->picture = $pic;
        self::$teams[$id] = $team;
        self::$teamslist[] = $team;
    }

    /**
     * Convert connector text to UTF-8 without failing on malformed imports.
     *
     * Legacy records may contain nested values or unsupported charset names.
     * Non-stringable inputs become empty text; failed iconv/mbstring
     * conversions preserve the original string instead of throwing on PHP 8.
     *
     * @param mixed $text Text supplied by a legacy calendar connector.
     * @param string $fromenc Original character encoding.
     * @param string $toenc Desired output encoding.
     * @return string Converted, unchanged or empty connector text.
     */
    public static function jl_utf8_convert($text, string $fromenc = 'iso-8859-1', string $toenc = 'UTF-8'): string
    {
        if (!is_scalar($text) && !$text instanceof \Stringable) {
            return '';
        }

        $text = (string) $text;
        $convert = class_exists('SportsmanagementConnector', false)
            && isset(\SportsmanagementConnector::$params)
            ? (int) \SportsmanagementConnector::$params->get('convert', 0)
            : 0;

        if ($convert === 0 || strcasecmp($fromenc, $toenc) === 0) {
            return $text;
        }

        if (function_exists('iconv')) {
            try {
                $converted = @iconv($fromenc, $toenc . '//TRANSLIT', $text);

                if ($converted !== false) {
                    return $converted;
                }
            } catch (\Throwable) {
                // Invalid charsets and extension errors must not break the calendar.
            }
        }

        if (function_exists('mb_convert_encoding')) {
            try {
                return mb_convert_encoding($text, $toenc, $fromenc);
            } catch (\Throwable) {
                // Preserve the original text when legacy conversion fails.
            }
        }

        return $text;
    }

    /**
     * Compare two calendar values in ascending order.
     * Retains the named comparison callback used by older connector integrations.
     *
     * @param mixed $a First sortable calendar value.
     * @param mixed $b Second sortable calendar value.
     * @return int Negative, zero or positive ordering result.
     */
    public static function asc($a, $b): int
    {
        return $a <=> $b;
    }

    /**
     * Compare two calendar values in descending order.
     * Reverses the normal spaceship comparison for descending legacy calendar sorts.
     *
     * @param mixed $a First sortable calendar value.
     * @param mixed $b Second sortable calendar value.
     * @return int Reverse-order comparator result.
     */
    public static function desc($a, $b): int
    {
        return $b <=> $a;
    }

    /**
     * Load and chronologically sort the matches available in a calendar month.
     * Creates a timezone-aware month window with a four-digit year, resets
     * shared connector caches, and fetches SportsManagement, optional JEvents
     * and optional LiveScore records before ordering by calendar date.
     *
     * @param int|string $month Requested calendar month.
     * @param int|string $year Requested calendar year.
     * @return array<int,array> Date-sorted connector match records.
     */
    public static function getMatches($month, $year): array
    {
        $month = max(1, min(12, (int) $month));
        // Joomla calendar rendering supports four-digit years; clamp legacy
        // query inputs consistently with CalendarHelper::normaliseYear().
        $year = min(9999, max(1970, (int) $year));
        $app = self::siteApplication();
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

    /**
     * Sort calendar connector records by a selected array field.
     * Uses PHP's usort with ascending or descending direction and supports comparing whole entries when no field is supplied.
     *
     * @param array $array Calendar rows to sort.
     * @param string $comparefunction Sort direction; desc reverses the comparison.
     * @param string $property Optional associative key used for the comparison.
     * @return array Sorted calendar records.
     */
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

    /**
     * Retrieve event-day metadata for a particular grid date.
     * Builds the YYYYMMDD lookup key and returns the matching calendar link/title metadata only when the stored payload is an array.
     *
     * @param int $day Day of the requested month.
     * @param int $month Requested month.
     * @param int $year Requested year.
     * @return array Calendar link, title and action metadata, or an empty array.
     */
    public function getDateMeta($day, $month, $year): array
    {
        $date = sprintf('%04d%02d%02d', (int) $year, (int) $month, (int) $day);
        $meta = self::$linklist[$date] ?? [];

        return is_array($meta) ? $meta : [];
    }

    /**
     * Build a calendar navigation URL while preserving unrelated query parameters.
     * Reads the current Joomla URI, removes stale day/month/year parameters and adds the requested calendar month and year using RFC3986 encoding.
     *
     * @param int $month Destination month.
     * @param int $year Destination calendar year.
     * @return string Current site URL with updated calendar query parameters.
     */
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

    /**
     * Build formatted monthly match-list data for the calendar's presentation layer.
     * Groups connector matches by day and project/match code, emits title and table descriptors and distinguishes today's matches from other dates.
     *
     * @param int|string $month Displayed calendar month.
     * @param int|string $year Displayed calendar year.
     * @return array<int,array> Match-list row and heading descriptors.
     */
    public function matches_output($month, $year): array
    {
        $app = self::siteApplication();
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

    /**
     * Build the selectable team options used to filter calendar matches.
     * Sorts the unique teams collected by the calendar connectors and prepends the translated all-teams option.
     *
     * @return array<int,object> Joomla select options for the team filter.
     */
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

    /**
     * Order connector objects by one property or by their whole values.
     * Keeps the historical array sort helper while accepting only the asc and desc direction semantics.
     *
     * @param array $array Calendar objects or values to sort.
     * @param string $comparefunction Direction; desc selects reverse order.
     * @param string $property Optional object property used as the sort key.
     * @return array Sorted objects or values.
     */
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

    /**
     * Resolve the active frontend Joomla application for calendar requests.
     * Uses the component's site-application resolver and rejects administrator or unsupported clients before reading configuration and language data.
     *
     * @return CMSApplicationInterface Verified Joomla site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Calendar runtime requires the Joomla site application.', 500);
        }

        return $app;
    }
}
