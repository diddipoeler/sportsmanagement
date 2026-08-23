<?php
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
            $value = trim((string) ($location->{$property} ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $city = trim((string) ($location->location ?? $location->city ?? ''));
        $zipCode = trim((string) ($location->zipcode ?? ''));

        if ($city !== '') {
            $parts[] = trim($zipCode . ' ' . $city);
        } elseif ($zipCode !== '') {
            $parts[] = $zipCode;
        }

        $countryCode = strtoupper(trim((string) ($location->country ?? '')));
        if ($countryCode !== '') {
            $countryName = self::countryName($db, $countryCode);
            $parts[] = $countryName !== '' ? $countryName : $countryCode;
        }

        return implode(', ', array_filter($parts, static fn (string $part): bool => $part !== ''));
    }

    private static function countryName(DatabaseInterface $db, string $countryCode): string
    {
        try {
            $query = $db->getQuery(true)
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
