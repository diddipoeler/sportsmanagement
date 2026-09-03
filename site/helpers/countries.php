<?php
/**
 * SportsManagement country helper compatibility layer for Joomla 5/6.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!defined('JSM_PATH')) {
    define('JSM_PATH', 'components/com_sportsmanagement');
}

require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';

$maxImportTime = 480;
if ((int) ini_get('max_execution_time') < $maxImportTime) {
    @set_time_limit($maxImportTime);
}

class JSMCountries
{
    public static function getCountries()
    {
    }

    public static function getCountry($countrycode = '')
    {
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__sportsmanagement_countries')
            ->where('alpha3 LIKE ' . $db->quote((string) $countrycode));
        $db->setQuery($query);

        return $db->loadObject();
    }

    public static function getCountryOptions($value_tag = 'value', $text_tag = 'text', $useflag = 0)
    {
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true)
            ->select('alpha3,name')
            ->from('#__sportsmanagement_countries');
        $db->setQuery($query);
        $countries = $db->loadAssocList();

        $options = [];
        foreach ($countries as $country) {
            $options[] = HTMLHelper::_(
                'select.option',
                $country['alpha3'],
                Text::_($country['name']),
                $value_tag,
                $text_tag
            );
        }

        return self::sortCountryArray($options, 'text');
    }

    public static function sortCountryArray($array, $index)
    {
        $sort = [];
        $result = [];

        for ($i = 0; isset($array[$i]); $i++) {
            $sort[$i] = $array[$i]->{$index};
        }

        natcasesort($sort);

        foreach ($sort as $key => $value) {
            $result[] = $array[$key];
        }

        return $result;
    }

    public static function convertIso2to3($iso_code_2)
    {
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true)
            ->select('alpha3')
            ->from('#__sportsmanagement_countries')
            ->where('alpha2 LIKE ' . $db->quote((string) $iso_code_2));
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ?: null;
    }

    public static function getShortCountryName($iso3)
    {
        $full = self::getCountryName($iso3);
        if (empty($full)) {
            return false;
        }

        $parts = explode(',', $full);

        return Text::_($parts[0]);
    }

    public static function getCountryalpha3fifa($fifa = '')
    {
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true)
            ->select('alpha3')
            ->from('#__sportsmanagement_countries')
            ->where('fifa LIKE ' . $db->quote((string) $fifa));
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ?: null;
    }

    public static function getCountryName($iso3)
    {
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true)
            ->select('name')
            ->from('#__sportsmanagement_countries')
            ->where('alpha3 LIKE ' . $db->quote((string) $iso3));
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ? Text::_($result) : null;
    }

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

    public static function getCountryFlag($countrycode, $attributes = '', $picture = false, $flag_map = false)
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $cssflags = (int) $params->get('cfg_flags_css', 0);
        $db = sportsmanagementHelper::getDBConnection();
        $iso2 = self::convertIso3to2($countrycode);
        $src = self::getIso2Flag($iso2);

        if ($picture) {
            $query = $db->getQuery(true)
                ->select('picture')
                ->from('#__sportsmanagement_countries')
                ->where('alpha3 LIKE ' . $db->quote((string) $countrycode));
            $db->setQuery($query);

            return $db->loadResult();
        }

        if ($flag_map) {
            $query = $db->getQuery(true)
                ->select('flag_maps')
                ->from('#__sportsmanagement_countries')
                ->where('alpha3 LIKE ' . $db->quote((string) $countrycode));
            $db->setQuery($query);

            return $db->loadResult();
        }

        if (!$src) {
            $query = $db->getQuery(true)
                ->select('picture')
                ->from('#__sportsmanagement_countries')
                ->where('alpha3 LIKE ' . $db->quote((string) $countrycode));
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

    public static function getIso3Flag($iso_code_3)
    {
        $iso2 = self::convertIso3to2($iso_code_3);

        return $iso2
            ? 'images/com_sportsmanagement/database/flags/' . strtolower($iso2) . '.png'
            : null;
    }

    public static function getIso2Flag($iso_code_2)
    {
        return $iso_code_2
            ? 'images/com_sportsmanagement/database/flags/' . strtolower((string) $iso_code_2) . '.png'
            : null;
    }

    public static function convertIso3to2($iso_code_3)
    {
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true)
            ->select('alpha2')
            ->from('#__sportsmanagement_countries')
            ->where('alpha3 LIKE ' . $db->quote((string) $iso_code_3));
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ?: null;
    }

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

    public function Countries()
    {
    }
}
