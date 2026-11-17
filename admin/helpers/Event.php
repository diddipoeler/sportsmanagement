<?php
/**
 * SportsManagement calendar event value object for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

/**
 * Legacy iCalendar event value object.
 *
 * Unknown iCalendar properties are intentionally retained as dynamic properties
 * for backwards compatibility with the parser. PHP 8.2+ otherwise emits a
 * deprecation notice when those properties are populated.
 */
#[\AllowDynamicProperties]
class Event
{
    // phpcs:disable Generic.Arrays.DisallowLongArraySyntax

    public const HTML_TEMPLATE = '<p>%s: %s</p>';

    /**
     * https://www.kanzaki.com/docs/ical/summary.html
     *
     * @var $summary
     */
    public $summary;

    /**
     * https://www.kanzaki.com/docs/ical/dtstart.html
     *
     * @var $dtstart
     */
    public $dtstart;

    /**
     * https://www.kanzaki.com/docs/ical/dtend.html
     *
     * @var $dtend
     */
    public $dtend;

    /**
     * https://www.kanzaki.com/docs/ical/duration.html
     *
     * @var $duration
     */
    public $duration;

    /**
     * https://www.kanzaki.com/docs/ical/dtstamp.html
     *
     * @var $dtstamp
     */
    public $dtstamp;

    /**
     * https://www.kanzaki.com/docs/ical/uid.html
     *
     * @var $uid
     */
    public $uid;

    /**
     * https://www.kanzaki.com/docs/ical/created.html
     *
     * @var $created
     */
    public $created;

    /**
     * https://www.kanzaki.com/docs/ical/lastModified.html
     *
     * @var $lastmodified
     */
    public $lastmodified;

    /**
     * https://www.kanzaki.com/docs/ical/description.html
     *
     * @var $description
     */
    public $description;

    /**
     * https://www.kanzaki.com/docs/ical/location.html
     *
     * @var $location
     */
    public $location;

    /**
     * https://www.kanzaki.com/docs/ical/sequence.html
     *
     * @var $sequence
     */
    public $sequence;

    /**
     * https://www.kanzaki.com/docs/ical/status.html
     *
     * @var $status
     */
    public $status;

    /**
     * https://www.kanzaki.com/docs/ical/transp.html
     *
     * @var $transp
     */
    public $transp;

    /**
     * https://www.kanzaki.com/docs/ical/organizer.html
     *
     * @var $organizer
     */
    public $organizer;

    /**
     * https://www.kanzaki.com/docs/ical/attendee.html
     *
     * @var $attendee
     */
    public $attendee;

    /**
     * Initialise the iCalendar event from its legacy parser properties.
     *
     * Converts event keys into historical PHP property names and normalises
     * strings and repeated values before storing them. Unknown properties
     * remain supported by the legacy calendar parser on PHP 8.2 and later.
     *
     * @param array<string,mixed> $data Incoming iCalendar properties.
     * @return void
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            $variable = self::snakeCase($key);
            $this->{$variable} = self::prepareData($value);
        }
    }

    /**
     * Normalise an iCalendar value, including nested attendee lists.
     *
     * Converts escaped newline sequences and trims strings recursively.
     * Static invocation is intentional: PHP 8 does not allow calling an
     * instance method statically from the event constructor.
     *
     * @param mixed $value One parsed property or nested list of properties.
     * @return mixed Prepared value with its original scalar/array shape.
     */
    protected static function prepareData($value)
    {
        if (is_string($value)) {
            return stripslashes(trim(str_replace('\\n', "\n", $value)));
        }

        if (is_array($value)) {
            return array_map(static fn (mixed $item): mixed => self::prepareData($item), $value);
        }

        return $value;
    }

    /**
     * Render populated calendar properties with a trusted HTML template.
     *
     * Avoids PHP warnings for absent timezone fields and joins repeated
     * attendee values into a readable list. Escapes event text before
     * insertion into the HTML template to prevent calendar-content markup
     * and attributes from executing in the administrator interface.
     *
     * @param string $html Trusted sprintf template with two placeholders.
     * @return string Escaped HTML for nonempty event properties.
     */
    public function printData($html = self::HTML_TEMPLATE)
    {
        $data = [
            'SUMMARY'       => $this->summary ?? null,
            'DTSTART'       => $this->dtstart ?? null,
            'DTEND'         => $this->dtend ?? null,
            'DTSTART_TZ'    => $this->dtstart_tz ?? null,
            'DTEND_TZ'      => $this->dtend_tz ?? null,
            'DURATION'      => $this->duration ?? null,
            'DTSTAMP'       => $this->dtstamp ?? null,
            'UID'           => $this->uid ?? null,
            'CREATED'       => $this->created ?? null,
            'LAST-MODIFIED' => $this->lastmodified ?? null,
            'DESCRIPTION'   => $this->description ?? null,
            'LOCATION'      => $this->location ?? null,
            'SEQUENCE'      => $this->sequence ?? null,
            'STATUS'        => $this->status ?? null,
            'TRANSP'        => $this->transp ?? null,
            'ORGANISER'     => $this->organizer ?? null,
            'ATTENDEE(S)'   => $this->attendee ?? null,
        ];

        $output = '';

        foreach ($data as $label => $value) {
            if (is_array($value)) {
                $values = array_map(
                    static fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '',
                    $value
                );
                $value = implode(', ', array_filter($values, static fn (string $item): bool => $item !== ''));
            }

            if (!is_scalar($value) || (string) $value === '') {
                continue;
            }

            $output .= sprintf(
                $html,
                htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
        }

        return $output;
    }

    /**
     * Convert an iCalendar field name to the legacy snake_case property.
     *
     * Inserts word separators before uppercase sections, replaces the
     * calendar field separator and lowercases the property identifier.
     *
     * @param string $input Original calendar property name.
     * @param string $glue Separator placed between word segments.
     * @param string $separator Calendar separator to replace.
     * @return string Lowercase PHP property identifier.
     */
    protected static function snakeCase($input, $glue = '_', $separator = '-')
    {
        $input = preg_split('/(?<=[a-z])(?=[A-Z])/x', $input);
        $input = implode($glue, $input);
        $input = str_replace($separator, $glue, $input);

        return strtolower($input);
    }
}
