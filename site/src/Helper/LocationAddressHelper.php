<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Build a plain location address using the selected SportsManagement database. */
final class LocationAddressHelper
{
    /**
     * Assemble a display-ready location address using a Joomla database.
     *
     * Reads address, state, postal code and city from legacy location
     * records, ignores nested or invalid scalar values and resolves the
     * three-letter country code to its translated country name when known.
     * Uses the alternate city field when the primary legacy field is invalid.
     *
     * @param DatabaseInterface $db Active SportsManagement database.
     * @param object $location Location record from the component.
     * @return string Comma-separated address with available fields.
     */
    public static function build(DatabaseInterface $db, object $location): string
    {
        $parts = [];

        foreach (['address', 'state'] as $property) {
            $value = self::scalarText($location->{$property} ?? '');
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $city = self::scalarText($location->location ?? null);

        // A corrupt legacy location value must not conceal a valid city
        // stored in the alternate database column.
        if ($city === '') {
            $city = self::scalarText($location->city ?? null);
        }
        $zipCode = self::scalarText($location->zipcode ?? '');

        if ($city !== '') {
            $parts[] = trim($zipCode . ' ' . $city);
        } elseif ($zipCode !== '') {
            $parts[] = $zipCode;
        }

        $countryCode = strtoupper(self::scalarText($location->country ?? ''));
        if ($countryCode !== '') {
            $countryName = self::countryName($db, $countryCode);
            $parts[] = $countryName !== '' ? $countryName : $countryCode;
        }

        return implode(', ', array_filter($parts, static fn (string $part): bool => $part !== ''));
    }

    /**
     * Normalise a legacy location field to printable scalar text.
     *
     * Accepts strings, integers and floats, discarding arrays and objects
     * so that PHP 8+ does not emit conversion warnings in frontend views.
     * Discard non-finite floats and normalise control bytes in imported text.
     *
     * @param mixed $value Raw address property.
     * @return string Trimmed text, or an empty string for invalid values.
     */
    private static function scalarText(mixed $value): string
    {
        if (is_float($value) && !is_finite($value)) {
            return '';
        }

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return '';
        }

        // Imports can contain NUL, line breaks or other control bytes.
        // Preserve readable address text without injecting new display lines.
        $text = preg_replace('/[\\x00-\\x1F\\x7F]+/', ' ', (string) $value);

        return trim($text ?? '');
    }

    /**
     * Look up a translated country name for an ISO alpha-3 country code.
     *
     * Queries the selected SportsManagement countries table and translates
     * the returned name through Joomla. Missing tables or database errors
     * yield an empty value, leaving the original code as a safe fallback.
     * Rejects non-ISO alpha-3 codes before executing a database query and
     * ignores non-string database results without PHP 8 conversion warnings.
     * Normalise control bytes in translated country labels as well.
     *
     * @param DatabaseInterface $db Database used by the current component.
     * @param string $countryCode Uppercase three-letter country code.
     * @return string Translated country name, or an empty string if unknown.
     */
    private static function countryName(DatabaseInterface $db, string $countryCode): string
    {
        if (preg_match('/^[A-Z]{3}$/D', $countryCode) !== 1) {
            return '';
        }

        try {
            $query = $db->createQuery()
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha3') . ' = ' . $db->quote($countryCode));
            $db->setQuery($query, 0, 1);
            $rawName = $db->loadResult();
            $name = is_string($rawName) ? self::scalarText($rawName) : '';

            // Country names and translated labels can also originate from
            // imported legacy data. Reuse the same printable-text rules as
            // street and city fields before adding them to the address.
            return $name !== '' ? self::scalarText(Text::_($name)) : '';
        } catch (\Throwable) {
            return '';
        }
    }
}
