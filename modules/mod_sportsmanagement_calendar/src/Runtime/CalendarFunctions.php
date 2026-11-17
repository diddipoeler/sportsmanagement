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
    /**
     * Render a legacy monthly calendar without relying on removed Joomla helpers.
     * Validates the requested month and year, lays out up to six complete
     * weeks and includes backward-compatible navigation data attributes.
     * Navigation stays within the supported 1970–9999 year range, avoiding
     * unusable links at the beginning and end of the calendar period.
     * Boundary clamping does not change the previous month's day count
     * used to populate the inactive cells in the leading calendar week.
     *
     * @param mixed $year Requested calendar year; invalid values use the current year.
     * @param mixed $month Requested calendar month; invalid values use the current month.
     * @return void Writes the complete legacy calendar HTML.
     */
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
        $previousNavigation = $previousMonth;
        $nextNavigation = $nextMonth;

        // Navigation must not generate years outside the supported 1970–9999
        // range: following those links would reset the calendar to today.
        if ((int) $previousNavigation->format('Y') < 1970) {
            $previousNavigation = $firstDay;
        }

        if ((int) $nextNavigation->format('Y') > 9999) {
            $nextNavigation = $firstDay;
        }

        // The previous month's day count must remain the actual preceding
        // month even when navigation is clamped at January 1970.
        $totalDaysOfPreviousMonth = (int) $previousMonth->format('t');
        ?>
        <main class="calendar-contain">
            <section class="title-bar">
                <a href="javascript:void(0);"
                   class="title-bar__prev"
                   data-calendar-year="<?php echo (int) $previousNavigation->format('Y'); ?>"
                   data-calendar-month="<?php echo (int) $previousNavigation->format('m'); ?>"></a>
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
                   data-calendar-year="<?php echo (int) $nextNavigation->format('Y'); ?>"
                   data-calendar-month="<?php echo (int) $nextNavigation->format('m'); ?>"></a>
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

    /**
     * Create the month selector for the alternative legacy calendar layout.
     * Generates twelve month options, marks a valid requested month as selected and avoids casting non-scalar filter input into a calendar month.
     *
     * @param mixed $selected Requested calendar month.
     * @return string Month option HTML.
     */
    public static function monthList($selected = ''): string
    {
        $selected = (is_int($selected) || is_string($selected))
            && preg_match('/^[0-9]+$/D', trim((string) $selected)) === 1
            ? (int) $selected
            : 0;
        $options = '';

        for ($month = 1; $month <= 12; $month++) {
            $value = sprintf('%02d', $month);
            $isSelected = $month === $selected ? ' selected' : '';
            $label = (new \DateTimeImmutable(sprintf('2000-%02d-01', $month)))->format('F');
            $options .= '<option value="' . $value . '"' . $isSelected . '>' . $label . '</option>';
        }

        return $options;
    }

    /**
     * Create a bounded year selector around the requested calendar year.
     * Accepts years between 1970 and 9999, otherwise uses the current
     * year. Renders at most five neighbouring years on either side without
     * emitting year values outside the supported calendar range.
     *
     * @param mixed $selected Requested display year.
     * @return string Year option HTML.
     */
    public static function yearList($selected = ''): string
    {
        $year = is_int($selected) || is_string($selected) ? trim((string) $selected) : '';
        $selected = preg_match('/^[0-9]{4}$/D', $year) === 1
            && (int) $year >= 1970 && (int) $year <= 9999
            ? (int) $year
            : (int) date('Y');
        $options = '';

        for ($year = max(1970, $selected - 5); $year <= min(9999, $selected + 5); $year++) {
            $isSelected = $year === $selected ? ' selected' : '';
            $options .= '<option value="' . $year . '"' . $isSelected . '>' . $year . '</option>';
        }

        return $options;
    }

    /**
     * Format the historical event sidebar heading for a given date.
     * Accepts date strings and integer or numeric-string Unix timestamps,
     * accepts leading-zero timestamp strings and falls back to today for
     * unsupported, overflowing or unparseable values,
     * and never casts arrays or objects to strings.
     *
     * @param mixed $date Optional date supplied by a calendar override.
     * @return string Escaped static heading markup with weekday and date.
     */
    public static function events($date = ''): string
    {
        $value = is_string($date) || is_int($date) ? trim((string) $date) : '';

        // Unix timestamps are valid legacy calendar inputs. strtotime() does
        // not interpret plain timestamp integers as epoch seconds.
        if (is_int($date)) {
            $timestamp = $date;
        } elseif ($value === '' || strlen($value) > 128) {
            $timestamp = time();
        } elseif (preg_match('/^[+-]?[0-9]+$/D', $value) === 1) {
            // Legacy overrides may pass Unix seconds as a string. Validate
            // the integer range rather than handing bare digits to strtotime.
            $negative = str_starts_with($value, '-');
            $signed = $negative || str_starts_with($value, '+');
            $digits = ltrim($signed ? substr($value, 1) : $value, '0');
            $digits = $digits === '' ? '0' : $digits;
            $limit = $negative ? substr((string) PHP_INT_MIN, 1) : (string) PHP_INT_MAX;

            if (strlen($digits) > strlen($limit)
                || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) > 0)) {
                $timestamp = time();
            } else {
                // Numeric strings may carry leading zeroes; FILTER_VALIDATE_INT
                // rejects them even when their timestamp is in range.
                $timestamp = (int) $value;
            }
        } else {
            $parsed = strtotime($value);
            $timestamp = $parsed === false ? time() : $parsed;
        }

        return '<h2 class="sidebar__heading">'
            . date('l', $timestamp)
            . '<br>'
            . date('F d', $timestamp)
            . '</h2>';
    }
}
