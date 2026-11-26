<?php
/**
 * File-level attribution and license; original notices are preserved below.
 *
 * @version    1.0
 * @author    Roman Ožana (Cz)
 * @copyright    Roman Ožana (Cz)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
//
// DATE : 01.08.2006  #
// Short description :                                                         #
//
// Internet Calendaring Specification Parser                              #
// (http://www.ietf.org/rfc/rfc2445.txt)                                   #
//
// Author info :                                                               #
//
// ROMAN OŽANA (c) 2006                                                    #
// ICQ (99950132)                                                  #
// WWW (www.nabito.net)                                            #
// E-mail (admin@nabito.net)                                          #
//
// Country:                                                                    #
//
// CZECH REPUBLIC                                                          #
//
// Licence:                                                                    #
//
// IF YOU WANT USE THIS CODE PLEASE CONTACT AUTHOR, Thank You              #
//
// it was written in SCITE   #
//
/**
 * This class Parse iCal standard. Is prepare to iCal feature version. Now is testing with apple iCal standard 2.0.
 *
 * @author    Roman Ožana (Cz)
 * @copyright Roman Ožana (Cz)
 * @link      www.nabito.net
 * @example
 *     $ical = new ical();
 *     $ical->parse('./calendar.ics');
 *     echo "<pre>";
 *     $ical->get_all_data();
 *  echo "</pre>";
 * @version   1.0
 * @internal  get sort todo list
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

class ical
{
    /** @var string|null Source calendar file. */
    public $file = null;

    /** @var string Text in file. */
    public $file_text = '';

    /** @var array Parsed iCalendar data. */
    public $cal = [];

    /** @var int Number of events. */
    public $event_count = -1;

    /** @var int Number of ToDos. */
    public $todo_count = 0;

    /** @var string|null Last parsed key for folded values. */
    public $last_key = null;

    /**
     * Return the historical event counter, which starts at -1 before the first VEVENT.
     *
     * @return int
     */
    public function get_event_count()
    {
        return $this->event_count;
    }

    /**
     * Return the number of VTODO components encountered in the current calendar.
     *
     * @return int
     */
    public function get_todo_count()
    {
        return $this->todo_count;
    }

    /**
     * Parse an iCalendar file into the historical calendar and event arrays.
     *
     * Resets all parser state on every invocation, validates the VCALENDAR
     * envelope and unfolds RFC 5545 lines beginning with a space or tab
     * before splitting properties. This preserves colons and separators in
     * multi-line descriptions without creating spurious calendar fields.
     * Accepts a UTF-8 byte order mark and case-insensitive component markers.
     * Returns the legacy error string for a missing calendar opening marker.
     *
     * @param string $uri Path or supported stream identifier of an ICS file.
     * @return array|string Parsed properties or a legacy invalid-calendar message.
     */
    public function parse($uri)
    {
        $this->cal = [];
        $this->event_count = -1;
        $this->todo_count = 0;
        $this->last_key = null;

        // Read the complete calendar first. Keep file_text as text for legacy callers.
        $this->file_text = $this->read_file($uri);
        $physicalLines = preg_split('/\r\n|\n|\r/', (string) $this->file_text) ?: [];
        $lines = [];

        // RFC 5545 folding: a CRLF followed by one space or tab is not a
        // new property. Join it before the colon separator is inspected.
        foreach ($physicalLines as $physicalLine) {
            if ($physicalLine !== '' && ($physicalLine[0] === ' ' || $physicalLine[0] === "\t")
                && $lines !== []) {
                $last = count($lines) - 1;
                $lines[$last] .= substr($physicalLine, 1);
                continue;
            }

            $lines[] = $physicalLine;
        }

        // A valid iCalendar stream starts with BEGIN:VCALENDAR.
        if (isset($lines[0]) && str_starts_with($lines[0], "\xEF\xBB\xBF")) {
            $lines[0] = substr($lines[0], 3);
        }

        if ($lines === [] || strcasecmp(trim($lines[0]), 'BEGIN:VCALENDAR') !== 0) {
            return 'error not VCALENDAR';
        }

        $type = 'VCALENDAR';

        foreach ($lines as $text) {
            $text = trim((string) $text);

            if ($text === '') {
                continue;
            }

            // get Key and Value VCALENDAR:Begin -> Key = VCALENDAR, Value = begin
            [$key, $value] = $this->retun_key_value($text);

            switch (strtoupper($text)) {
                case 'BEGIN:VTODO':
                    $this->todo_count++;
                    $type = 'VTODO';
                    break;

                case 'BEGIN:VEVENT':
                    $this->event_count++;
                    $type = 'VEVENT';
                    break;

                case 'BEGIN:VCALENDAR':
                case 'BEGIN:DAYLIGHT':
                case 'BEGIN:VTIMEZONE':
                case 'BEGIN:STANDARD':
                    $type = strtoupper($value);
                    $this->last_key = null;
                    break;

                case 'END:VTODO':
                case 'END:VEVENT':
                case 'END:VCALENDAR':
                case 'END:DAYLIGHT':
                case 'END:VTIMEZONE':
                case 'END:STANDARD':
                    $type = 'VCALENDAR';
                    break;

                default:
                    $this->add_to_array($type, $key, $value);
                    break;
            }
        }

        return $this->cal;
    }

    /**
     * Read the complete raw iCalendar source for the legacy import parser.
     *
     * Retains the original source identifier for callers and normalises
     * Mozilla's legacy split property separators; invalid source identifiers and read failures return empty text without emitting PHP warnings. Standard folded lines
     * are unfolded by parse() before tokenisation.
     *
     * @param string $file ICS source path or stream accepted by PHP.
     * @return string Raw calendar text or empty text after a read failure.
     */
    public function read_file($file)
    {
        $this->file = $file;
        if (!is_string($file) || trim($file) === '' || str_contains($file, "\0")) {
            return '';
        }

        try {
            $fileText = @file_get_contents($file);
        } catch (\Throwable) {
            return '';
        }

        if ($fileText === false) {
            return '';
        }

        // Mozilla Calendar may fold a property separator onto the next line.
        $fileText = preg_replace('/[\r\n]{1,} ([:;])/', '$1', $fileText) ?? $fileText;

        return $fileText;
    }

    /**
     * Split one unfolded iCalendar content line at its first colon.
     *
     * Permits empty values such as DESCRIPTION: and preserves subsequent
     * colons in URLs and text. A line without a valid key returns false so
     * the legacy continuation handler can decide whether to append it.
     *
     * @param string $text Unfolded iCalendar content line.
     * @return array{0:string|false,1:string} Property name and raw value.
     */
    public function retun_key_value($text)
    {
        $separator = strpos((string) $text, ':');

        if ($separator === false || $separator === 0) {
            return [false, (string) $text];
        }

        return [substr((string) $text, 0, $separator), substr((string) $text, $separator + 1)];
    }

    /**
     * Add to $this->ical array one value and key. Type is VTODO, VEVENT, VCALENDAR ... .
     *
     * @param string $type
     * @param string|false $key
     * @param mixed $value
     */
    public function add_to_array($type, $key, $value)
    {
        if ($key === false) {
            $key = $this->last_key;

            if ($key === null) {
                return;
            }

            switch ($type) {
                case 'VEVENT':
                    $value = ($this->cal[$type][$this->event_count][$key] ?? '') . $value;
                    break;

                case 'VTODO':
                    $value = ($this->cal[$type][$this->todo_count][$key] ?? '') . $value;
                    break;
            }
        }

        if ($key === 'DTSTAMP' || $key === 'LAST-MODIFIED' || $key === 'CREATED') {
            $value = $this->ical_date_to_unix($value);
        }

        if ($key === 'RRULE') {
            $value = $this->ical_rrule($value);
        }

        if (stristr((string) $key, 'DTSTART') || stristr((string) $key, 'DTEND')) {
            [$key, $value] = $this->ical_dt_date($key, $value);
        }

        switch ($type) {
            case 'VTODO':
                $this->cal[$type][$this->todo_count][$key] = $value;
                break;

            case 'VEVENT':
                $this->cal[$type][$this->event_count][$key] = $value;
                break;

            default:
                $this->cal[$type][$key] = $value;
                break;
        }

        $this->last_key = $key;
    }

    /**
     * Convert a complete iCalendar DATE or DATE-TIME into a Unix timestamp.
     *
     * Accepts YYYYMMDD and YYYYMMDDTHHMMSS with an optional UTC suffix,
     * verifying calendar dates and time bounds before converting to a timestamp.
     * UTC values ending in Z use gmmktime(); floating local dates retain the PHP timezone.
     * Rejects trailing junk and rollover dates instead of returning an
     * unrelated timestamp. Preserves the legacy pre-1971 year fallback
     * for compatibility with historical imports.
     *
     * @param string $ical_date Raw iCalendar DATE or DATE-TIME property.
     * @return int|false Parsed Unix timestamp or false for invalid values.
     */
    public function ical_date_to_unix($ical_date)
    {
        if (!is_string($ical_date)
            || preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})(?:T([0-9]{2})([0-9]{2})([0-9]{2})Z?)?$/D', $ical_date, $date) !== 1) {
            return false;
        }

        $year = (int) $date[1];
        $month = (int) $date[2];
        $day = (int) $date[3];
        $hour = (int) ($date[4] ?? 0);
        $minute = (int) ($date[5] ?? 0);
        $second = (int) ($date[6] ?? 0);

        if (!checkdate($month, $day, $year) || $hour > 23 || $minute > 59 || $second > 59) {
            return false;
        }

        // Preserve the year correction of the original calendar parser.
        if ($year <= 1970) {
            $year = 1971;
        }

        return str_ends_with($ical_date, 'Z')
            ? gmmktime($hour, $minute, $second, $month, $day, $year)
            : mktime($hour, $minute, $second, $month, $day, $year);
    }

    /**
     * Parse RRULE and return array.
     *
     * @param string $value
     *
     * @return array
     */
    public function ical_rrule($value)
    {
        $result = [];

        foreach (explode(';', (string) $value) as $line) {
            $rcontent = explode('=', $line, 2);

            if (count($rcontent) === 2) {
                $result[$rcontent[0]] = $rcontent[1];
            }
        }

        return $result;
    }

    /**
     * Return unix date from iCal date format
     *
     * @param string $key
     * @param string $value
     *
     * @return array
     */
    public function ical_dt_date($key, $value)
    {
        $value = $this->ical_date_to_unix($value);
        $temp = explode(';', (string) $key, 2);

        if (!isset($temp[1]) || $temp[1] === '') {
            return [$key, $value];
        }

        $key = $temp[0];
        $timezone = explode('=', $temp[1], 2);
        $returnValue = ['unixtime' => $value];

        if (count($timezone) === 2) {
            $returnValue[$timezone[0]] = $timezone[1];
        }

        return [$key, $returnValue];
    }

    /**
     * Return sorted eventlist as array or false if calendar is empty.
     *
     * @return array|false
     */
    public function get_sort_event_list()
    {
        $temp = $this->get_event_list();

        if ($temp === []) {
            return false;
        }

        usort($temp, [$this, 'ical_dtstart_compare']);

        return $temp;
    }

    /**
     * Return eventlist array (not sorted eventlist array).
     *
     * @return array
     */
    public function get_event_list()
    {
        return $this->cal['VEVENT'] ?? [];
    }

    /**
     * Compare two unix timestamps.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public function ical_dtstart_compare($a, $b)
    {
        $aStart = $a['DTSTART']['unixtime'] ?? $a['DTSTART'] ?? 0;
        $bStart = $b['DTSTART']['unixtime'] ?? $b['DTSTART'] ?? 0;

        return $aStart <=> $bStart;
    }

    /**
     * Return todo array (not sorted todo array).
     *
     * @return array
     */
    public function get_todo_list()
    {
        return $this->cal['VTODO'] ?? [];
    }

    /**
     * Return base calendar data.
     *
     * @return array
     */
    public function get_calender_data()
    {
        return $this->cal['VCALENDAR'] ?? [];
    }

    /**
     * Return array with all data.
     *
     * @return array
     */
    public function get_all_data()
    {
        return $this->cal;
    }
}
