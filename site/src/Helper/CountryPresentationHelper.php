<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Country presentation helpers for native site views.
 */
final class CountryPresentationHelper
{
    private static array $countries = [];

    public static function name(string $countryCode): string
    {
        $country = self::country($countryCode);

        return $country ? Text::_((string) $country->name) : '';
    }

    public static function flag(string $countryCode, string $attributes = ''): string
    {
        $country = self::country($countryCode);
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $countryName = $country ? Text::_((string) $country->name) : '';
        $iso2 = strtolower((string) ($country->alpha2 ?? ''));

        if ((int) $params->get('cfg_flags_css', 0) === 1) {
            $cssCode = match (strtoupper($countryCode)) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $iso2,
            };

            return $cssCode !== '' ? '<span class="fi fi-' . self::escape($cssCode) . '"></span>' : '';
        }

        $src = $iso2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $iso2 . '.png'
            : (string) ($country->picture ?? '');

        if ($src === '') {
            $src = (string) $params->get('ph_flags', '');
        }

        if ($src === '') {
            return '';
        }

        $extra = trim($attributes);

        return '<img src="' . self::escape(Uri::root() . ltrim($src, '/'))
            . '" alt="' . self::escape($countryName)
            . '" title="' . self::escape($countryName) . '"'
            . ($extra !== '' ? ' ' . $extra : '') . ' />';
    }

    public static function options(DatabaseInterface $db): array
    {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('alpha3'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'));
        $db->setQuery($query);

        $options = [];
        foreach ($db->loadObjectList() ?: [] as $country) {
            $code = strtoupper(trim((string) ($country->alpha3 ?? '')));
            if ($code === '') {
                continue;
            }

            $options[] = HTMLHelper::_(
                'select.option',
                $code,
                Text::_((string) ($country->name ?? $code))
            );
        }

        usort(
            $options,
            static fn (object $a, object $b): int => strnatcasecmp(
                (string) ($a->text ?? ''),
                (string) ($b->text ?? '')
            )
        );

        return $options;
    }

    public static function address(
        string $name = '',
        string $address = '',
        string $state = '',
        string $zipcode = '',
        string $location = '',
        string $country = '',
        string $format = 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM'
    ): string {
        if ($address === '' && $state === '' && $zipcode === '' && $location === '') {
            return '';
        }

        $output = Text::_($format);
        $output = str_replace('%NAME%', '<span itemprop="name">' . self::escape($name) . '</span>', $output);
        $output = str_replace(
            '%ADDRESS%',
            '<div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress"><span itemprop="streetAddress">'
                . self::escape($address) . '</span>',
            $output
        );
        $output = str_replace('%STATE%', '<span itemprop="addressRegion">' . self::escape($state) . '</span>', $output);
        $output = str_replace('%ZIPCODE%', '<span itemprop="postalCode">' . self::escape($zipcode) . '</span>', $output);
        $output = str_replace('%LOCATION%', '<span itemprop="addressLocality">' . self::escape($location) . '</span>', $output);
        $output = str_replace('%FLAG%', self::flag($country), $output);
        $output = str_replace('%COUNTRY%', self::escape(self::name($country)), $output);

        return $output . '</div>&nbsp;';
    }

    private static function country(string $countryCode): ?object
    {
        $countryCode = strtoupper(trim($countryCode));

        if ($countryCode === '') {
            return null;
        }

        $databaseSelector = Factory::getApplication()->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        $cacheKey = $databaseSelector . ':' . $countryCode;

        if (array_key_exists($cacheKey, self::$countries)) {
            return self::$countries[$cacheKey];
        }

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('alpha2'),
                $db->quoteName('alpha3'),
                $db->quoteName('name'),
                $db->quoteName('picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where(
                '(' . $db->quoteName('alpha3') . ' = ' . $db->quote($countryCode)
                . ' OR ' . $db->quoteName('fifa') . ' = ' . $db->quote($countryCode) . ')'
            );

        $db->setQuery($query, 0, 1);
        self::$countries[$cacheKey] = $db->loadObject() ?: null;

        return self::$countries[$cacheKey];
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
