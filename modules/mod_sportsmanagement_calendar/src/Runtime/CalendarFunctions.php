<?php
/**
 * Joomla 5/6 compatibility rendering helpers for alternate SportsManagement calendar layouts.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Runtime;

\defined('_JEXEC') or die;

/**
 * Compatibility rendering helpers for the historical alternate calendar UI.
 *
 * The active Joomla 5/6 module layout uses CalendarRenderer; these methods are
 * retained for stored overrides or third-party calls that still use the older
 * function surface.
 */
final class CalendarFunctions
{
    public static function render($year = '', $month = ''): void
    {
        $year = filter_var(
            $year,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1970, 'max_range' => 9999]]
        ) ?: (int) date('Y');
        $month = filter_var(
            $month,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 12]]
        ) ?: (int) date('m');

        $firstDay = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $currentMonthFirstDay = (int) $firstDay->format('N');
        $totalDaysOfMonth = (int) $firstDay->format('t');
        $totalDaysOfMonthDisplay = $currentMonthFirstDay === 1
            ? $totalDaysOfMonth
            : $totalDaysOfMonth + ($currentMonthFirstDay - 1);
        $boxDisplay = $totalDaysOfMonthDisplay <= 35 ? 35 : 42;

        $previousMonth = $firstDay->modify('-1 month');
        $nextMonth = $firstDay->modify('+1 month');
        $totalDaysOfPreviousMonth = (int) $previousMonth->format('t');
        ?>
        <main class="calendar-contain">
            <section class="title-bar">
                <a href="javascript:void(0);"
                   class="title-bar__prev"
                   data-calendar-year="<?php echo (int) $previousMonth->format('Y'); ?>"
                   data-calendar-month="<?php echo (int) $previousMonth->format('m'); ?>"></a>
                <div class="title-bar__month">
                    <select class="month-dropdown">
                        <?php echo self::monthList($month); ?>
                    </select>
                </div>
                <div class="title-bar__year">
                    <select class="year-dropdown">
                        <?php echo self::yearList($year); ?>
                    </select>
                </div>
                <a href="javascript:void(0);"
                   class="title-bar__next"
                   data-calendar-year="<?php echo (int) $nextMonth->format('Y'); ?>"
                   data-calendar-month="<?php echo (int) $nextMonth->format('m'); ?>"></a>
            </section>

            <aside class="calendar__sidebar" id="event_list">
                <?php echo self::events(); ?>
            </aside>

            <section class="calendar__days">
                <section class="calendar__top-bar">
                    <span class="top-bar__days">Mon</span>
                    <span class="top-bar__days">Tue</span>
                    <span class="top-bar__days">Wed</span>
                    <span class="top-bar__days">Thu</span>
                    <span class="top-bar__days">Fri</span>
                    <span class="top-bar__days">Sat</span>
                    <span class="top-bar__days">Sun</span>
                </section>
                <?php
                $dayCount = 1;
                $eventNum = 0;
                $today = date('Y-m-d');

                echo '<section class="calendar__week">';

                for ($box = 1; $box <= $boxDisplay; $box++) {
                    if (($box >= $currentMonthFirstDay || $currentMonthFirstDay === 1) && $box <= $totalDaysOfMonthDisplay) {
                        $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $dayCount);
                        $classes = 'calendar__day no-event';
                        $taskClass = 'calendar__task';

                        if ($currentDate === $today) {
                            $classes = 'calendar__day today';
                            $taskClass .= ' calendar__task--today';
                        } elseif ($eventNum > 0) {
                            $classes = 'calendar__day event';
                        }

                        echo '<div class="' . $classes . '" data-calendar-date="'
                            . htmlspecialchars($currentDate, ENT_QUOTES, 'UTF-8') . '">';
                        echo '<span class="calendar__date">' . $dayCount . '</span>';
                        echo '<span class="' . $taskClass . '">' . $eventNum . ' Events</span>';
                        echo '</div>';
                        $dayCount++;
                    } else {
                        if ($box < $currentMonthFirstDay) {
                            $inactiveCalendarDay = (($totalDaysOfPreviousMonth - $currentMonthFirstDay) + 1) + $box;
                            $inactiveLabel = 'expired';
                        } else {
                            $inactiveCalendarDay = $box - $totalDaysOfMonthDisplay;
                            $inactiveLabel = 'upcoming';
                        }

                        echo '<div class="calendar__day inactive">';
                        echo '<span class="calendar__date">' . $inactiveCalendarDay . '</span>';
                        echo '<span class="calendar__task">' . $inactiveLabel . '</span>';
                        echo '</div>';
                    }

                    if ($box % 7 === 0 && $box !== $boxDisplay) {
                        echo '</section><section class="calendar__week">';
                    }
                }

                echo '</section>';
                ?>
            </section>
        </main>
        <?php
    }

    public static function monthList($selected = ''): string
    {
        $selected = (int) $selected;
        $options = '';

        for ($month = 1; $month <= 12; $month++) {
            $value = sprintf('%02d', $month);
            $isSelected = $month === $selected ? ' selected' : '';
            $label = (new \DateTimeImmutable(sprintf('2000-%02d-01', $month)))->format('F');
            $options .= '<option value="' . $value . '"' . $isSelected . '>' . $label . '</option>';
        }

        return $options;
    }

    public static function yearList($selected = ''): string
    {
        $selected = (int) ($selected ?: date('Y'));
        $options = '';

        for ($year = $selected - 5; $year <= $selected + 5; $year++) {
            $isSelected = $year === $selected ? ' selected' : '';
            $options .= '<option value="' . $year . '"' . $isSelected . '>' . $year . '</option>';
        }

        return $options;
    }

    public static function events($date = ''): string
    {
        $value = trim((string) $date);
        $timestamp = $value !== '' ? strtotime($value) : time();

        if ($timestamp === false) {
            $timestamp = time();
        }

        return '<h2 class="sidebar__heading">'
            . date('l', $timestamp)
            . '<br>'
            . date('F d', $timestamp)
            . '</h2>';
    }
}
