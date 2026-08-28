<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Builds translated country presentation data without loading the legacy JSMCountries helper.
 */
final class CountryOptionsHelper
{
    public static function getOptions(DatabaseInterface $db): array
    {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('alpha3'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadAssocList() ?: [] as $country) {
            $options[] = HTMLHelper::_(
                'select.option',
                (string) $country['alpha3'],
                Text::_((string) $country['name'])
            );
        }

        usort(
            $options,
            static fn ($left, $right): int => strnatcasecmp((string) $left->text, (string) $right->text)
        );

        return $options;
    }

    public static function iso2To3(DatabaseInterface $db, string $iso2, string $fallback = 'DEU'): string
    {
        $iso2 = strtoupper(trim($iso2));

        if ($iso2 === '') {
            return $fallback;
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('alpha3'))
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha2') . ' = ' . $db->quote($iso2));
        $db->setQuery($query, 0, 1);
        $alpha3 = strtoupper(trim((string) $db->loadResult()));

        return $alpha3 !== '' ? $alpha3 : $fallback;
    }

    public static function getFlag(DatabaseInterface $db, string $countryCode): string
    {
        $countryCode = strtoupper(trim($countryCode));

        if ($countryCode === '') {
            return '';
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('alpha2'),
                $db->quoteName('name'),
                $db->quoteName('picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha3') . ' = ' . $db->quote($countryCode));
        $db->setQuery($query, 0, 1);
        $country = $db->loadObject();

        if (!$country) {
            return '';
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $alpha2 = strtolower(trim((string) ($country->alpha2 ?? '')));
        $label = Text::_((string) ($country->name ?? $countryCode));

        if ((int) $params->get('cfg_flags_css', 0) === 1) {
            $cssCode = match ($countryCode) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $alpha2,
            };

            return $cssCode !== ''
                ? '<span class="fi fi-' . self::escape($cssCode) . '" title="' . self::escape($label) . '"></span>'
                : '';
        }

        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : ltrim((string) ($country->picture ?? $params->get('ph_flags', '')), '/');

        return $path !== ''
            ? '<img src="' . self::escape(Uri::root() . ltrim($path, '/'))
                . '" alt="' . self::escape($label) . '" title="' . self::escape($label) . '" />'
            : '';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
