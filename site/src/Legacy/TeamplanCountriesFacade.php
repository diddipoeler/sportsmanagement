<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Narrow country/flag facade for the historical teamplan templates.
 */
final class TeamplanCountriesFacade
{
    public static function getCountryFlag($countryCode, $attributes = '', $picture = false, $flagMap = false): string
    {
        $countryCode = strtoupper((string) $countryCode);
        if ($countryCode === '') {
            return '';
        }

        $country = self::getCountry($countryCode);
        $picturePath = (string) ($country->picture ?? '');

        if ($picture) {
            return $picturePath;
        }
        if ($flagMap) {
            return (string) ($country->flag_maps ?? '');
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $iso2 = strtolower((string) ($country->alpha2 ?? ''));
        $src = $iso2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $iso2 . '.png'
            : $picturePath;

        if ($src === '') {
            $src = (string) $params->get('ph_flags', '');
        }

        if (!(bool) $params->get('cfg_flags_css', 0)) {
            $name = Text::_((string) ($country->name ?? ''));
            return '<img src="' . Uri::root() . $src . '" alt="' . $name . '" title="' . $name . '" '
                . (string) $attributes . ' />';
        }

        $cssCode = match ($countryCode) {
            'WAL' => 'gb-wls',
            'SCO' => 'gb-sct',
            'GBR' => 'gb-eng',
            default => $iso2,
        };

        return $cssCode !== '' ? '<span class="fi fi-' . strtolower($cssCode) . '"></span>' : '';
    }

    private static function getCountry(string $countryCode): ?object
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('alpha2'),
                $db->quoteName('alpha3'),
                $db->quoteName('name'),
                $db->quoteName('picture'),
                $db->quoteName('flag_maps'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha3') . ' = ' . $db->quote($countryCode));
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }
}
