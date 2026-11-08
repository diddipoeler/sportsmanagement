<?php
/**
 * Native Joomla 5/6 calendar month renderer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Runtime;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;

/**
 * Native Joomla 5/6 calendar month renderer.
 *
 * Based on the historical PHP Calendar Class used by SportsManagement.
 */
class CalendarRenderer
{
    public int $startDay = 0;
    public int $startMonth = 1;
    public array $dayNames = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
    public array $monthNames = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    ];
    public array $daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    public $modid = '';
    public int $ajax = 0;
    public int $lightbox = 0;
    public int $lightbox_on_pageload = 0;
    public $usedteams = '';
    public $usedclubs = '';

    /**
     * Read the abbreviated weekday labels configured for calendar columns.
     * Returns the seven translated or overridden day names used to render the month header.
     *
     * @return array<int,string> Abbreviated weekday labels.
     */
    public function getDayNames(): array
    {
        return $this->dayNames;
    }

    /**
     * Replace the weekday names used by the calendar renderer.
     * Accepts the translated weekday label array from the module settings without changing the configured calendar start day.
     *
     * @param array<int,string> $names Weekday labels indexed in Sunday-to-Saturday order.
     * @return void
     */
    public function setDayNames(array $names): void
    {
        $this->dayNames = $names;
    }

    /**
     * Read the translated month labels used in calendar navigation.
     * Exposes the configured month names for the monthly grid and list heading.
     *
     * @return array<int,string> Twelve month display names.
     */
    public function getMonthNames(): array
    {
        return $this->monthNames;
    }

    /**
     * Replace the month labels used in month and match headings.
     * Stores the translated January-to-December list supplied by Joomla's module configuration.
     *
     * @param array<int,string> $names Localised month names ordered from January.
     * @return void
     */
    public function setMonthNames(array $names): void
    {
        $this->monthNames = $names;
    }

    /**
     * Return the configured first weekday of the calendar grid.
     * Exposes the Sunday-based start-day index used when calculating column positions.
     *
     * @return int Index of the first displayed weekday.
     */
    public function getStartDay(): int
    {
        return $this->startDay;
    }

    /**
     * Set the configured first weekday of the monthly calendar.
     * Converts a legacy module parameter to the integer index used to offset weekday headings.
     *
     * @param mixed $day Module value representing the calendar's first weekday.
     * @return void
     */
    public function setStartDay($day): void
    {
        $this->startDay = (int) $day;
    }

    /**
     * Return the configured default calendar month.
     * Supplies the initial month chosen by the module when no explicit request overrides it.
     *
     * @return int Default month number.
     */
    public function getStartMonth(): int
    {
        return $this->startMonth;
    }

    /**
     * Set the default month used by legacy calendar layouts.
     * Converts the saved module month preference into the renderer's integer month field.
     *
     * @param mixed $month Legacy module value containing a month number.
     * @return void
     */
    public function setStartMonth($month): void
    {
        $this->startMonth = (int) $month;
    }

    /**
     * Build the complete month view payload for a legacy calendar caller.
     * Delegates grid, match list and team option rendering to getMonthHTML without changing their established return keys.
     *
     * @param int|string $month Month to display.
     * @param int|string $year Year to display.
     * @return array{calendar:string,list:array,teamslist:array} Calendar rendering payload.
     */
    public function getMonthView($month, $year): array
    {
        return $this->getMonthHTML($month, $year);
    }

    /**
     * Render the month grid, navigational links and associated match/team lists.
     * Normalises month and year, resolves the Joomla site application, creates escaped navigation attributes and renders event-day links only for actionable dates.
     *
     * @param int|string $m Requested month.
     * @param int|string $y Requested year.
     * @param int|bool $showYear Whether the year is displayed and navigation enabled.
     * @return array{calendar:string,list:array,teamslist:array} Month HTML and list data.
     */
    public function getMonthHTML($m, $y, $showYear = 1): array
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Calendar renderer requires the Joomla site application.', 500);
        }

        $s = '';

        [$month, $year] = $this->adjustDate((int) $m, (int) $y);

        $daysInMonth = $this->getDaysInMonth($month, $year);
        $date = getdate(mktime(12, 0, 0, $month, 1, $year));
        $daysInLastMonth = $this->getDaysInMonth($month - 1, $year);
        $first = $date['wday'];
        $monthName = $this->monthNames[$month - 1];

        $prev = $this->adjustDate($month - 1, $year);
        $next = $this->adjustDate($month + 1, $year);

        if ((int) $showYear === 1) {
            $prevMonth = $this->getCalendarLink($prev[0], $prev[1]);
            $nextMonth = $this->getCalendarLink($next[0], $next[1]);
            $nextYear = $this->getCalendarLink($month, $year + 1);
            $prevYear = $this->getCalendarLink($month, $year - 1);
        } else {
            $prevMonth = '#';
            $nextMonth = '#';
            $prevYear = '#';
            $nextYear = '#';
        }

        $language = $app->getLanguage();
        $language->load('mod_sportsmanagement_calendar');
        $header = $monthName . (((int) $showYear > 0) ? ' ' . $year : '');
        $moduleId = (int) $this->modid;

        $navLink = static function (
            string $href,
            string $title,
            string $id,
            int $targetMonth,
            int $targetYear,
            int $moduleId,
            string $label
        ): string {
            return '<a class="jlcheaderArrow" title="'
                . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
                . '" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" href="'
                . htmlspecialchars($href, ENT_QUOTES, 'UTF-8')
                . '" data-jsm-calendar-nav data-calendar-month="' . $targetMonth
                . '" data-calendar-year="' . $targetYear
                . '" data-module-id="' . $moduleId . '">' . $label . '</a>';
        };

        $s .= '<table id="jlctableCalendar-' . $moduleId . '" class="jlcCalendar">' . "\n";
        $s .= "   <tr>\n";
        $s .= '      <td class="jlcCalendarHeader jlcheaderArrow text-center">'
            . $navLink(
                $prevYear,
                $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_PREVYEAR'),
                'jlcprevYear-' . $moduleId,
                $month,
                $year - 1,
                $moduleId,
                '&lt;&lt;'
            )
            . '</td>' . "\n";
        $s .= '      <td class="jlcCalendarHeader jlcheaderArrow text-center">'
            . $navLink(
                $prevMonth,
                $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_PREVMONTH'),
                'jlcprevMonth-' . $moduleId,
                $prev[0],
                $prev[1],
                $moduleId,
                '&lt;'
            )
            . '</td>' . "\n";
        $s .= '<td class="jlcCalendarHeader jlcheaderDate text-center" colspan="3">'
            . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . "</td>\n";
        $s .= '<td class="jlcCalendarHeader jlcheaderArrow text-center">'
            . $navLink(
                $nextMonth,
                $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_NEXTMONTH'),
                'jlcnextMonth-' . $moduleId,
                $next[0],
                $next[1],
                $moduleId,
                '&gt;'
            )
            . '</td>' . "\n";
        $s .= '<td class="jlcCalendarHeader jlcheaderArrow text-center">'
            . $navLink(
                $nextYear,
                $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_NEXTYEAR'),
                'jlcnextYear-' . $moduleId,
                $month,
                $year + 1,
                $moduleId,
                '&gt;&gt;'
            )
            . '</td>' . "\n";
        $s .= "</tr>\n";

        $s .= "<tr>\n";
        for ($i = 0; $i < 7; $i++) {
            $s .= '<td class="jlcdayName">' . htmlspecialchars(
                (string) $this->dayNames[($this->startDay + $i) % 7],
                ENT_QUOTES,
                'UTF-8'
            ) . "</td>\n";
        }
        $s .= "</tr>\n";

        $d = $this->startDay + 1 - $first;
        while ($d > 1) {
            $d -= 7;
        }

        $today = getdate(time());

        while ($d <= $daysInMonth) {
            $s .= "<tr>\n";

            for ($i = 0; $i < 7; $i++) {
                $class = (
                    $year === (int) $today['year']
                    && $month === (int) $today['mon']
                    && $d === (int) $today['mday']
                ) ? 'highlight jlcCalendarDay jlcCalendarToday ' : 'jlcCalendarDay ';

                $s .= '<td class="';
                $tdEnd = '">';

                if ($d > 0 && $d <= $daysInMonth) {
                    $divday = ($d > 9) ? $d : '0' . $d;
                    $dateMeta = $this->getDateMeta($d, $month, $year);
                    $hasDateAction = (bool) ($dateMeta['hasAction'] ?? false);
                    $sourceId = sprintf('jlcal_%04d-%02d-%02d-%d', $year, $month, $d, $moduleId);
                    $dateTitle = trim((string) ($dateMeta['title'] ?? ''));
                    $titleAttribute = $dateTitle !== ''
                        ? ' title="' . htmlspecialchars($dateTitle, ENT_QUOTES, 'UTF-8') . '"'
                        : '';
                    $autoOpen = $hasDateAction
                        && $class === 'highlight jlcCalendarDay jlcCalendarToday '
                        && $this->ajax === 0
                        && $this->lightbox === 1
                        && $this->lightbox_on_pageload === 1;
                    $dateAttributes = ' href="#" data-jsm-calendar-day'
                        . ' data-source-id="' . htmlspecialchars($sourceId, ENT_QUOTES, 'UTF-8') . '"'
                        . ' data-module-id="' . $moduleId . '"'
                        . ($autoOpen ? ' data-jsm-calendar-autoopen="1"' : '')
                        . $titleAttribute;

                    if ($hasDateAction && $class === 'highlight jlcCalendarDay jlcCalendarToday ') {
                        $s .= $class . 'jlcCalendarTodayLink' . $tdEnd
                            . '<a class="hasTip jlcCalendarToday jlcmodal' . $moduleId . '"'
                            . ' data-bs-toggle="modal" data-bs-target="#myModal' . $moduleId . '"'
                            . $dateAttributes . '>' . $divday . '</a>';
                    } else {
                        $s .= !$hasDateAction
                            ? $class . $tdEnd . $divday
                            : 'jlcCalendarDay' . $tdEnd
                                . '<a class="jlcCalendarDay hasTip jlcmodal' . $moduleId
                                . '" data-bs-toggle="modal" data-bs-target="#myModal' . $moduleId . '"'
                                . $dateAttributes . '>' . $divday . '</a>';
                    }
                } else {
                    if ($d <= 0) {
                        $do = $daysInLastMonth + $d;
                    } else {
                        $do = '0' . ($d - $daysInMonth);
                    }

                    $s .= 'jlcCalendarDay jlcCalendarDayEmpty ' . $tdEnd . $do;
                }

                $s .= "</td>\n";
                $d++;
            }

            $s .= "</tr>\n";
        }

        $s .= "</table>\n";

        return [
            'calendar' => $s,
            'list' => $this->matches_output($m, $y),
            'teamslist' => $this->output_teamlist(),
        ];
    }

    /**
     * Normalise out-of-range month numbers across their corresponding years.
     * Carries month underflow or overflow into the preceding or following year, preserving valid month values from one through twelve.
     *
     * @param int $month Month number that may be outside the normal range.
     * @param int $year Year to adjust when the month crosses a boundary.
     * @return array{0:int,1:int} Corrected month and year.
     */
    public function adjustDate($month, $year): array
    {
        $month = (int) $month;
        $year = (int) $year;

        while ($month > 12) {
            $month -= 12;
            $year++;
        }

        while ($month <= 0) {
            $month += 12;
            $year--;
        }

        return [$month, $year];
    }

    /**
     * Calculate the number of days for a month in the given Gregorian year.
     * Uses configured month lengths and the leap-year rule for February; invalid month numbers return zero.
     *
     * @param int $month Requested month number.
     * @param int $year Calendar year used for leap-year evaluation.
     * @return int Number of days in the month, or zero for invalid months.
     */
    public function getDaysInMonth($month, $year): int
    {
        $month = (int) $month;
        $year = (int) $year;

        if ($month < 1 || $month > 12) {
            return 0;
        }

        $days = $this->daysInMonth[$month - 1];

        if ($month === 2 && ($year % 4 === 0) && ($year % 100 !== 0 || $year % 400 === 0)) {
            $days = 29;
        }

        return $days;
    }
}
