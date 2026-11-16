<?php
/**
 * FullCalendar event utility helpers retained for Joomla 5/6 compatibility.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// Date parsing explicitly uses UTC below; never override Joomla's process timezone.

class Event
{
    public const ALL_DAY_REGEX = '/^\d{4}-\d\d-\d\d$/';

    public string $title;
    public bool $allDay;
    public \DateTime $start;
    public ?\DateTime $end;
    public array $properties = [];

    /**
     * Initialise a FullCalendar event from a payload, including its title, date range and custom properties.
     * Infers all-day events from date-only start/end values unless an explicit flag is set, and parses timed events using the requested timezone.
     * @param array $array Event properties including title, start and optional end/allDay.
     * @param \DateTimeZone|null $timezone Optional timezone for timed events.
     * @return void
     */
    public function __construct(array $array, ?\DateTimeZone $timezone = null)
    {
        $this->title = (string) ($array['title'] ?? '');

        if (array_key_exists('allDay', $array)) {
            $this->allDay = (bool) $array['allDay'];
        } else {
            $this->allDay = preg_match(self::ALL_DAY_REGEX, (string) ($array['start'] ?? '')) === 1
                && (!isset($array['end']) || preg_match(self::ALL_DAY_REGEX, (string) $array['end']) === 1);
        }

        if ($this->allDay) {
            $timezone = null;
        }

        $this->start = parseDateTime((string) ($array['start'] ?? ''), $timezone);
        $this->end = isset($array['end']) ? parseDateTime((string) $array['end'], $timezone) : null;

        foreach ($array as $name => $value) {
            if (!in_array($name, ['title', 'allDay', 'start', 'end'], true)) {
                $this->properties[$name] = $value;
            }
        }
    }

    /**
     * Check whether an event overlaps the selected half-open calendar day range.
     * Compares stripped UTC dates so a single-day event must fall within the range and a multi-day event may span its boundaries.
     * @param \DateTime $rangeStart Inclusive start of the day range.
     * @param \DateTime $rangeEnd Exclusive end of the day range.
     * @return bool True if this event falls inside or overlaps the range.
     */
    public function isWithinDayRange(\DateTime $rangeStart, \DateTime $rangeEnd): bool
    {
        $eventStart = stripTime($this->start);
        $eventEnd = $this->end !== null ? stripTime($this->end) : null;

        if ($eventEnd === null) {
            return $eventStart < $rangeEnd && $eventStart >= $rangeStart;
        }

        return $eventStart < $rangeEnd && $eventEnd > $rangeStart;
    }

    /**
     * Serialise this event into the array format expected by FullCalendar.
     * Includes arbitrary event properties and outputs all-day dates in Y-m-d format or timed dates in ISO-8601 format.
     * @return array Event fields for JSON encoding.
     */
    public function toArray(): array
    {
        $array = $this->properties;
        $array['title'] = $this->title;
        $format = $this->allDay ? 'Y-m-d' : 'c';
        $array['start'] = $this->start->format($format);

        if ($this->end !== null) {
            $array['end'] = $this->end->format($format);
        }

        return $array;
    }
}

/**
 * Parse an event date/time without changing Joomla's global process timezone.
 * Uses UTC when no timezone is provided and applies a caller-supplied timezone to the resulting DateTime instance.
 * @param string $string Date or timestamp to parse.
 * @param \DateTimeZone|null $timezone Optional target timezone.
 * @return \DateTime Parsed event timestamp.
 * @throws \Exception If the date/time value is invalid.
 */
function parseDateTime(string $string, ?\DateTimeZone $timezone = null): \DateTime
{
    $date = new \DateTime($string, $timezone ?? new \DateTimeZone('UTC'));

    if ($timezone !== null) {
        $date->setTimezone($timezone);
    }

    return $date;
}

/**
 * Create an independent UTC-midnight date from a timestamp.
 * Discards the time-of-day to make event overlap checks compare whole calendar days while leaving the source object untouched.
 * @param \DateTime $datetime Timestamp whose date should be preserved.
 * @return \DateTime New UTC-midnight date object.
 */
function stripTime(\DateTime $datetime): \DateTime
{
    return new \DateTime($datetime->format('Y-m-d'), new \DateTimeZone('UTC'));
}
