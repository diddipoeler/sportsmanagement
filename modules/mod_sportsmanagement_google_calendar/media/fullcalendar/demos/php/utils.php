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

date_default_timezone_set('UTC');

class Event
{
    public const ALL_DAY_REGEX = '/^\d{4}-\d\d-\d\d$/';

    public string $title;
    public bool $allDay;
    public \DateTime $start;
    public ?\DateTime $end;
    public array $properties = [];

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

    public function isWithinDayRange(\DateTime $rangeStart, \DateTime $rangeEnd): bool
    {
        $eventStart = stripTime($this->start);
        $eventEnd = $this->end !== null ? stripTime($this->end) : null;

        if ($eventEnd === null) {
            return $eventStart < $rangeEnd && $eventStart >= $rangeStart;
        }

        return $eventStart < $rangeEnd && $eventEnd > $rangeStart;
    }

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

function parseDateTime(string $string, ?\DateTimeZone $timezone = null): \DateTime
{
    $date = new \DateTime($string, $timezone ?? new \DateTimeZone('UTC'));

    if ($timezone !== null) {
        $date->setTimezone($timezone);
    }

    return $date;
}

function stripTime(\DateTime $datetime): \DateTime
{
    return new \DateTime($datetime->format('Y-m-d'), new \DateTimeZone('UTC'));
}
