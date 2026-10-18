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
    public static function build(DatabaseInterface $db, object $location): string
    {
        $parts = [];

        foreach (['address', 'state'] as $property) {
            $value = self::scalarText($location->{$property} ?? '');
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $city = self::scalarText($location->location ?? $location->city ?? '');
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
     * Legacy location records can contain malformed nested values. PHP 8+
     * should not emit "Array to string conversion" warnings on a site page.
     */
    private static function scalarText(mixed $value): string
    {
        return is_string($value) || is_int($value) || is_float($value)
            ? trim((string) $value)
            : '';
    }

    private static function countryName(DatabaseInterface $db, string $countryCode): string
    {
        try {
            $query = $db->createQuery()
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha3') . ' = ' . $db->quote($countryCode));
            $db->setQuery($query, 0, 1);
            $name = trim((string) $db->loadResult());

            return $name !== '' ? Text::_($name) : '';
        } catch (\Throwable) {
            return '';
        }
    }
}
