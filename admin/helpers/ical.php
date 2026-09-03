<?php
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
     * Vraci pocet udalosti v kalendari
     *
     * @return int
     */
    public function get_event_count()
    {
        return $this->event_count;
    }

    /**
     * Vraci pocet ToDo uloh
     *
     * @return int
     */
    public function get_todo_count()
    {
        return $this->todo_count;
    }

    /**
     * Prekladac kalendare
     *
     * @param string $uri
     *
     * @return array|string
     */
    public function parse($uri)
    {
        $this->cal = [];
        $this->event_count = -1;
        $this->todo_count = 0;
        $this->last_key = null;

        // Read the complete calendar first. Keep file_text as text for legacy callers.
        $this->file_text = $this->read_file($uri);
        $lines = preg_split('/\R/', (string) $this->file_text) ?: [];

        // A valid iCalendar stream starts with BEGIN:VCALENDAR.
        if ($lines === [] || stripos(trim((string) $lines[0]), 'BEGIN:VCALENDAR') === false) {
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

            switch ($text) {
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
                    $type = $value;
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
     * Read text file, icalender text file
     *
     * @param string $file
     *
     * @return string
     */
    public function read_file($file)
    {
        $this->file = $file;
        $fileText = file_get_contents($file);

        if ($fileText === false) {
            return '';
        }

        // Mozilla Calendar may fold a property separator onto the next line.
        $fileText = preg_replace('/[\r\n]{1,} ([:;])/', '$1', $fileText) ?? $fileText;

        return $fileText;
    }

    /**
     * Parse text "XXXX:value text some with : " and return array($key = "XXXX", $value="value");
     *
     * @param string $text
     *
     * @return array
     */
    public function retun_key_value($text)
    {
        preg_match('/([^:]+):([\w\W]+)/', $text, $matches);

        if (empty($matches)) {
            return [false, $text];
        }

        return array_splice($matches, 1, 2);
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
     * Return Unix time from ical date time format (YYYYMMDD[T]HHMMSS[Z] or YYYYMMDD[T]HHMMSS)
     *
     * @param string $ical_date
     *
     * @return int|false
     */
    public function ical_date_to_unix($ical_date)
    {
        $ical_date = str_replace(['T', 'Z'], '', (string) $ical_date);
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{0,2})([0-9]{0,2})([0-9]{0,2})/', $ical_date, $date);

        if (count($date) < 7) {
            return false;
        }

        // UNIX timestamps can't deal with pre 1970 dates in the original parser.
        if ((int) $date[1] <= 1970) {
            $date[1] = 1971;
        }

        return mktime(
            (int) $date[4],
            (int) $date[5],
            (int) $date[6],
            (int) $date[2],
            (int) $date[3],
            (int) $date[1]
        );
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
