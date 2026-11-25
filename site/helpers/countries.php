<?php
/**
 * SportsManagement country helper compatibility layer for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

if (!class_exists(SportsManagementDatabaseResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(SportsManagementDatabaseResolver::class)) {
    throw new \RuntimeException('SportsManagement database resolver could not be loaded.', 500);
}

$maxImportTime = 480;
if ((int) ini_get('max_execution_time') < $maxImportTime) {
    @set_time_limit($maxImportTime);
}

class JSMCountries
{
    /**
     * Resolve the configured SportsManagement country database through Joomla's database service.
     * Uses the component database resolver so country lookups respect the selected connection.
     * @return DatabaseInterface Effective connection for country queries.
     */
    private static function database(): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(self::joomlaDatabase());
    }

    /**
     * Obtain Joomla's managed database service and verify its type.
     * Raises a runtime error rather than using a deprecated Joomla factory fallback.
     * @return DatabaseInterface The active Joomla database service.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement country database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Preserve the historical country-list entry point for legacy callers.
     * This compatibility placeholder does not currently query or return country records.
     * @return void No value is currently returned.
     */
    public static function getCountries()
    {
    }

    /**
     * Load a country record by its three-letter ISO code.
     * Uses a bound database parameter to avoid interpolating caller input into SQL.
     * @param string $countrycode Three-letter ISO country code.
     * @return object|null Matching country record, if available.
     */
    public static function getCountry($countrycode = '')
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha3') . ' = :countryAlpha3')
            ->bind(':countryAlpha3', $countrycode, ParameterType::STRING);
        $db->setQuery($query);

        return $db->loadObject();
    }

    /**
     * Build translated select-list options for available countries.
     * Loads ISO codes and country names, maps them to Joomla HTML options, and sorts by label.
     * Skips malformed or duplicate country records and respects custom
     * option property names used by historical layouts.
     * @param string $value_tag Option value property name.
     * @param string $text_tag Option label property name.
     * @param int $useflag Historical flag-display option (currently unused).
     * @return array Country selection option objects sorted by translated name.
     */
    public static function getCountryOptions($value_tag = 'value', $text_tag = 'text', $useflag = 0)
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select('alpha3,name')
            ->from('#__sportsmanagement_countries');
        $db->setQuery($query);
        $countries = $db->loadAssocList();

        if (!is_iterable($countries)) {
            return [];
        }

        $options = [];
        $seen = [];

        foreach ($countries as $country) {
            $rawCode = is_array($country) ? ($country['alpha3'] ?? null) : null;
            $code = is_string($rawCode) ? strtoupper(trim($rawCode)) : '';

            if (preg_match('/^[A-Z]{3}$/D', $code) !== 1 || isset($seen[$code])) {
                continue;
            }

            $seen[$code] = true;
            $name = $country['name'] ?? null;
            $label = is_string($name) && trim($name) !== '' ? $name : $code;
            $options[] = HTMLHelper::_('select.option', $code, Text::_($label), $value_tag, $text_tag);
        }

        return self::sortCountryArray($options, $text_tag);
    }

    /**
     * Sort a list of country option objects by a selected property.
     * Uses natural case-insensitive ordering while preserving complete option objects.
     * Skips malformed option objects and supports sparse input arrays without
     * stopping at the first missing numeric index.
     * @param array $array Country option objects to order.
     * @param string $index Property containing the text used for sorting.
     * @return array Reordered option objects.
     */
    public static function sortCountryArray($array, $index)
    {
        $sort = [];
        $result = [];

        if (!is_array($array) || !is_string($index) || $index === '') {
            return [];
        }

        foreach ($array as $key => $option) {
            if (is_object($option) && is_string($option->{$index} ?? null)) {
                $sort[$key] = $option->{$index};
            }
        }

        natcasesort($sort);

        foreach ($sort as $key => $value) {
            $result[] = $array[$key];
        }

        return $result;
    }

    /**
     * Look up a three-letter ISO country code from a two-letter code.
     * Reads the countries table with a bound input value and returns null for missing entries.
     * @param string $iso_code_2 Two-letter ISO code.
     * @return string|null Three-letter ISO code if found.
     */
    public static function convertIso2to3($iso_code_2)
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select($db->quoteName('alpha3'))
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha2') . ' = :countryAlpha2')
            ->bind(':countryAlpha2', $iso_code_2, ParameterType::STRING);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ?: null;
    }

    /**
     * Return the translated first segment of a country's display name.
     * Looks up the ISO-3 country name and removes text after the first comma.
     * @param string $iso3 Three-letter ISO country code.
     * @return string|false Short country name or false if unknown.
     */
    public static function getShortCountryName($iso3)
    {
        $full = self::getCountryName($iso3);
        if (empty($full)) {
            return false;
        }

        $parts = explode(',', $full);

        return Text::_($parts[0]);
    }

    /**
     * Resolve the SportsManagement ISO-3 country code for a FIFA code.
     * Queries the configured country database using a bound FIFA identifier.
     * @param string $fifa FIFA country code.
     * @return string|null ISO-3 country code when present.
     */
    public static function getCountryalpha3fifa($fifa = '')
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select($db->quoteName('alpha3'))
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('fifa') . ' = :countryFifa')
            ->bind(':countryFifa', $fifa, ParameterType::STRING);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ?: null;
    }

    /**
     * Load and translate a country's name using its ISO-3 code.
     * Returns null for unknown countries and uses the Joomla translation service for labels.
     * @param string $iso3 Three-letter ISO country code.
     * @return string|null Translated country name, if found.
     */
    public static function getCountryName($iso3)
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha3') . ' = :countryNameAlpha3')
            ->bind(':countryNameAlpha3', $iso3, ParameterType::STRING);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ? Text::_($result) : null;
    }

    /**
     * Render legacy structured address markup from a translated template.
     * Substitutes club, street, region, postal code, locality, country and flag placeholders.
     * @param string $name Club name.
     * @param string $address Street address.
     * @param string $state Region or state.
     * @param string $zipcode Postal code.
     * @param string $location Town or locality.
     * @param string $country ISO-3 country code.
     * @param string $addressString Joomla language key for the address template.
     * @return string Generated legacy address HTML.
     */
    public static function convertAddressString(
        $name = '',
        $address = '',
        $state = '',
        $zipcode = '',
        $location = '',
        $country = '',
        $addressString = 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM'
    ) {
        $resultString = '';

        if (!empty($address) || !empty($state) || !empty($zipcode) || !empty($location)) {
            $countryFlag = self::getCountryFlag($country);
            $countryName = self::getCountryName($country);
            $dummy = Text::_($addressString);
            $dummy = str_replace('%NAME%', '<span itemprop="name">' . $name . '</span>', $dummy);
            $dummy = str_replace('%ADDRESS%', '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><span itemprop="streetAddress">' . $address . '</span>', $dummy);
            $dummy = str_replace('%STATE%', '<span itemprop="addressRegion">' . $state . '</span>', $dummy);
            $dummy = str_replace('%ZIPCODE%', '<span itemprop="postalCode">' . $zipcode . '</span>', $dummy);
            $dummy = str_replace('%LOCATION%', '<span itemprop="addressLocality">' . $location . '</span>', $dummy);
            $dummy = str_replace('%FLAG%', $countryFlag, $dummy);
            $dummy = str_replace('%COUNTRY%', $countryName, $dummy);
            $resultString .= $dummy;
        }

        $resultString .= '</div>&nbsp;';

        return $resultString;
    }

    /**
     * Create a country flag representation or return a stored picture path.
     * Resolves ISO-2 icons, database image fallbacks and CSS flag classes according to component settings.
     * @param string $countrycode ISO-3 country code.
     * @param string $attributes Legacy additional image HTML attributes.
     * @param bool $picture Return the stored country image instead of HTML.
     * @param bool $flag_map Return the map image path instead of HTML.
     * @return string|null Country flag HTML or selected image path.
     */
    public static function getCountryFlag($countrycode, $attributes = '', $picture = false, $flag_map = false)
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $cssflags = (int) $params->get('cfg_flags_css', 0);
        $db = self::database();
        $iso2 = self::convertIso3to2($countrycode);
        $src = self::getIso2Flag($iso2);

        if ($picture) {
            $query = $db->createQuery()
                ->select($db->quoteName('picture'))
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha3') . ' = :flagPictureAlpha3')
                ->bind(':flagPictureAlpha3', $countrycode, ParameterType::STRING);
            $db->setQuery($query);

            return $db->loadResult();
        }

        if ($flag_map) {
            $query = $db->createQuery()
                ->select($db->quoteName('flag_maps'))
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha3') . ' = :flagMapAlpha3')
                ->bind(':flagMapAlpha3', $countrycode, ParameterType::STRING);
            $db->setQuery($query);

            return $db->loadResult();
        }

        if (!$src) {
            $query = $db->createQuery()
                ->select($db->quoteName('picture'))
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha3') . ' = :fallbackFlagAlpha3')
                ->bind(':fallbackFlagAlpha3', $countrycode, ParameterType::STRING);
            $db->setQuery($query);
            $src = $db->loadResult();
        }

        if (!$src) {
            $src = (string) $params->get('ph_flags', '');
        }

        if ($cssflags === 0) {
            $countryName = self::getCountryName($countrycode);

            return '<img src="' . Uri::root() . $src . '" alt="' . $countryName . '" '
                . 'title="' . $countryName . '" ' . $attributes . ' />';
        }

        $flagCode = match ((string) $countrycode) {
            'WAL' => 'gb-wls',
            'SCO' => 'gb-sct',
            'GBR' => 'gb-eng',
            default => (string) $iso2,
        };

        return '<span class="fi fi-' . strtolower($flagCode) . '"></span>';
    }

    /**
     * Build the local flag image path for a three-letter ISO code.
     * Resolves the corresponding ISO-2 code first, returning null for unknown countries.
     * @param string $iso_code_3 Three-letter ISO country code.
     * @return string|null Local flag image path when available.
     */
    public static function getIso3Flag($iso_code_3)
    {
        $iso2 = self::convertIso3to2($iso_code_3);

        return $iso2
            ? 'images/com_sportsmanagement/database/flags/' . strtolower($iso2) . '.png'
            : null;
    }

    /**
     * Build the conventional country flag image path from a two-letter ISO code.
     * Accepts only a two-letter country identifier, ignoring malformed or
     * non-string values so they cannot become arbitrary media paths.
     * @param string $iso_code_2 Two-letter ISO country code.
     * @return string|null Relative flag path or null.
     */
    public static function getIso2Flag($iso_code_2)
    {
        return is_string($iso_code_2) && preg_match('/^[A-Za-z]{2}$/D', $iso_code_2) === 1
            ? 'images/com_sportsmanagement/database/flags/' . strtolower($iso_code_2) . '.png'
            : null;
    }

    /**
     * Resolve the two-letter ISO code belonging to a three-letter country code.
     * Uses a bound lookup on the configured countries table.
     * @param string $iso_code_3 Three-letter ISO country code.
     * @return string|null Two-letter ISO code if known.
     */
    public static function convertIso3to2($iso_code_3)
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select($db->quoteName('alpha2'))
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha3') . ' = :countryIso3')
            ->bind(':countryIso3', $iso_code_3, ParameterType::STRING);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ?: null;
    }

    /**
     * Remove placeholders for missing address fields from a legacy address string.
     * Checks club and location values and delegates placeholder removal to checkAddressString.
     * @param string $name Club name.
     * @param string $address Street address or template under construction.
     * @param string $state Region.
     * @param string $zipcode Postal code.
     * @param string $location Locality.
     * @param string $flag Flag markup.
     * @param string $country Country name.
     * @param string $addressString Legacy address language key.
     * @return string Address text after removing placeholders for absent values.
     */
    public static function removeEmptyFields(
        $name = '',
        $address = '',
        $state = '',
        $zipcode = '',
        $location = '',
        $flag = '',
        $country = '',
        $addressString = 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM'
    ) {
        if (empty($name)) {
            $address = self::checkAddressString('%NAME%', '', $address);
        }
        if (empty($address)) {
            $address = self::checkAddressString('%ADDRESS%', '', $address);
        }
        if (empty($state)) {
            $address = self::checkAddressString('%STATE%', '', $address);
        }
        if (empty($zipcode)) {
            $address = self::checkAddressString('%ZIPCODE%', '', $address);
        }
        if (empty($location)) {
            $address = self::checkAddressString('%LOCATION%', '', $address);
        }
        if (empty($flag)) {
            $address = self::checkAddressString('%FLAG%', '', $address);
        }
        if (empty($country)) {
            $address = self::checkAddressString('%COUNTRY%', '', $address);
        }

        return $address;
    }

    /**
     * Replace or remove one marker within a legacy address template.
     * Retains the surrounding percent-delimited placeholder layout when clearing a value.
     * @param string $find Placeholder to find.
     * @param string $replace Replacement text; empty removes the marker segment.
     * @param string $string Source template.
     * @return string Updated address template.
     */
    public static function checkAddressString($find, $replace, $string)
    {
        $pos = strpos($string, $find);
        if ($pos === false) {
            return $string;
        }

        $startpos = $pos + strlen($find);
        if (empty($replace)) {
            $nextpos = strpos($string, '%', $startpos);
            if ($nextpos === false) {
                if ($startpos === strlen($string)) {
                    $dummy = substr($string, 0, $pos);
                    $nextpos = strrpos($dummy, '%');
                    if ($nextpos !== false) {
                        $string = substr($dummy, 0, $nextpos + 1);
                    }
                }
            } else {
                $dummy = $string;
                $string = substr($dummy, 0, $pos) . substr($dummy, $nextpos);
            }
        } else {
            $string = str_replace($find, $replace, $string);
        }

        return $string;
    }

    /**
     * Retain the historical constructor-shaped Countries method.
     * This legacy no-op exists only for compatibility with older country helper calls.
     * @return void This method does not perform any action.
     */
    public function Countries()
    {
    }
}
