<?php
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Runtime;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

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

    public function getDayNames(): array
    {
        return $this->dayNames;
    }

    public function setDayNames(array $names): void
    {
        $this->dayNames = $names;
    }

    public function getMonthNames(): array
    {
        return $this->monthNames;
    }

    public function setMonthNames(array $names): void
    {
        $this->monthNames = $names;
    }

    public function getStartDay(): int
    {
        return $this->startDay;
    }

    public function setStartDay($day): void
    {
        $this->startDay = (int) $day;
    }

    public function getStartMonth(): int
    {
        return $this->startMonth;
    }

    public function setStartMonth($month): void
    {
        $this->startMonth = (int) $month;
    }

    public function getMonthView($month, $year): array
    {
        return $this->getMonthHTML($month, $year);
    }

    public function getMonthHTML($m, $y, $showYear = 1): array
    {
        $app = Factory::getApplication();
        $doc = $app->getDocument();
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
            $nextYear = $this->getCalendarLink($next[0], $next[1]);
            $prevYear = $this->getCalendarLink($prev[0], $prev[1]);
        } else {
            $prevMonth = '';
            $nextMonth = '';
            $prevYear = '';
            $nextYear = '';
        }

        unset($prevMonth, $nextMonth, $prevYear, $nextYear);

        $todaylink = '';
        $language = $app->getLanguage();
        $language->load('mod_sportsmanagement_calendar');
        $header = $monthName . (((int) $showYear > 0) ? ' ' . $year : '');

        $s .= '<table id="jlctableCalendar-' . $this->modid . '" class="jlcCalendar">' . "\n";
        $s .= "   <tr>\n";
        $s .= '      <td align="center" class="jlcCalendarHeader jlcheaderArrow">'
            . '<a class="jlcheaderArrow" title="'
            . $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_PREVYEAR')
            . '" id="jlcprevYear-' . $this->modid
            . '" href="javascript:void(0)" onclick="jlcnewDate(' . $month . ',' . ($year - 1) . ',' . $this->modid
            . ');">&lt;&lt;</a></td>' . "\n";
        $s .= '      <td align="center" class="jlcCalendarHeader jlcheaderArrow">'
            . '<a class="jlcheaderArrow" title="'
            . $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_PREVMONTH')
            . '" id="jlcprevMonth-' . $this->modid
            . '" href="javascript:void(0)" onclick="jlcnewDate(' . $prev[0] . ',' . $prev[1] . ',' . $this->modid
            . ');">&lt;</a></td>' . "\n";
        $s .= '<td align="center" class="jlcCalendarHeader jlcheaderDate" colspan="3">' . $header . "</td>\n";
        $s .= '<td align="center" class="jlcCalendarHeader jlcheaderArrow">'
            . '<a class="jlcheaderArrow" title="'
            . $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_NEXTMONTH')
            . '" id="jlcnextMonth-' . $this->modid
            . '" href="javascript:void(0)" onclick="jlcnewDate(' . $next[0] . ',' . $next[1] . ',' . $this->modid
            . ');" >&gt;</a></td>' . "\n";
        $s .= '<td align="center" class="jlcCalendarHeader jlcheaderArrow">'
            . '<a class="jlcheaderArrow" title="'
            . $language->_('MOD_SPORTSMANAGEMENT_CALENDAR_NEXTYEAR')
            . '" id="jlcnextYear-' . $this->modid
            . '" href="javascript:void(0)" onclick="jlcnewDate(' . $month . ',' . ($year + 1) . ',' . $this->modid
            . ');" >&gt;&gt;</a></td>' . "\n";
        $s .= "</tr>\n";

        $s .= "<tr>\n";
        for ($i = 0; $i < 7; $i++) {
            $s .= '<td class="jlcdayName">' . $this->dayNames[($this->startDay + $i) % 7] . "</td>\n";
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
                    $link = $this->getDateLink($d, $month, $year);
                    $click = $this->getDateClick($d, $month, $year);
                    $modalrel = '';

                    if ($link && $class === 'highlight jlcCalendarDay jlcCalendarToday ') {
                        $todaylink = $click;
                        $s .= $class . 'jlcCalendarTodayLink' . $tdEnd
                            . '<a class="hasTip jlcCalendarToday jlcmodal' . $this->modid . '"'
                            . ' data-bs-toggle="modal" data-bs-target="#myModal' . $this->modid . '"'
                            . ' href="' . $link . '" onclick="' . $click . '"'
                            . $modalrel . ' >' . $divday . '</a>';
                    } else {
                        $s .= ($link === '')
                            ? $class . $tdEnd . $divday
                            : 'jlcCalendarDay' . $tdEnd
                                . '<a class="jlcCalendarDay hasTip jlcmodal' . $this->modid
                                . '" data-bs-toggle="modal" data-bs-target="#myModal' . $this->modid
                                . '" href="' . $link . '" onclick="' . $click . '"'
                                . $modalrel . ' >' . $divday . '</a>';
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

        if ($todaylink !== '' && $this->ajax === 0 && $this->lightbox === 1 && $this->lightbox_on_pageload === 1) {
            $doc->getWebAssetManager()->addInlineScript(
                'document.addEventListener("DOMContentLoaded", function () {' . $todaylink . '});'
            );
        }

        return [
            'calendar' => $s,
            'list' => $this->matches_output($m, $y),
            'teamslist' => $this->output_teamlist(),
        ];
    }

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
