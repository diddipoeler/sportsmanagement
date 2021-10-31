<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       countries.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.utilities.arrayhelper');

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';

$maxImportTime = 480;
if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

/**
 * JSMCountries
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JSMCountries
{

	/**
	 * JSMCountries::getCountries()
	 *
	 * @return void
	 */
	public static function getCountries()
	{

	}

	public static function getCountry($countrycode = '')
	{
	$db = sportsmanagementHelper::getDBConnection();
	$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__sportsmanagement_countries');
		$query->where('alpha3 LIKE \'' . $countrycode . '\'');
		$db->setQuery($query);
		$res = $db->loadObject();
		return $res;
		
	}
	
	// Hints:
	// http://en.wikipedia.org/wiki/List_of_FIFA_country_codes
	// http://en.wikipedia.org/wiki/Comparison_of_IOC,_FIFA,_and_ISO_3166_country_codes
	// http://en.wikipedia.org/wiki/Category:Country_codes
	// http://en.wikipedia.org/wiki/ISO_3166-1
	// http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
	// http://en.wikipedia.org/wiki/ISO_3166-1_alpha-3
	// http://en.wikipedia.org/wiki/ISO_3166-1_numeric
	//

	/**
	 * JSMCountries::getCountryOptions()
	 *
	 * @param   string  $value_tag
	 * @param   string  $text_tag
	 *
	 * @return
	 */
	public static function getCountryOptions($value_tag = 'value', $text_tag = 'text', $useflag = 0)
	{
		$app = Factory::getApplication();
		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('alpha3,name');
		// From table
		$query->from('#__sportsmanagement_countries');
		$db->setQuery($query);
		$countries = $db->loadAssocList();

		$options = array();
		foreach ($countries AS $k)
		{
			$options[] = HTMLHelper::_('select.option', $k['alpha3'], Text::_($k['name']), $value_tag, $text_tag);
		}

		//Now Sort the countries
		$options = self::sortCountryArray($options, "text");

		return $options;
	}

	/**
	 * @param   array:
	 *
	 * @return array:
	 */
	static function sortCountryArray($array, $index)
	{
		$sort   = array();
		$result = array();

		for ($i = 0; isset($array[$i]); $i++)
		{
			$sort[$i] = $array[$i]->{$index};
		}

		natcasesort($sort);

		foreach ($sort as $k => $v)
		{
			$result[] = $array[$k];
		}

		return $result;
	}

	/**
	 * JSMCountries::convertIso2to3()
	 *
	 * @param   mixed  $iso_code_2
	 *
	 * @return
	 */
	public static function convertIso2to3($iso_code_2)
	{
		$app = Factory::getApplication();
		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('alpha3');
		// From table
		$query->from('#__sportsmanagement_countries');
		$query->where('alpha2 LIKE \'' . $iso_code_2 . '\'');


		$db->setQuery($query);
		$res = $db->loadResult();
		if ($res)
		{
			return $res;
		}

		return null;
	}

	/**
	 * @param   string: an iso3 country code, e.g AUT
	 *
	 * @return string: a country name, short form
	 */
	public static function getShortCountryName($iso3)
	{

		$full = self::getCountryName($iso3);
		if (empty($full))
		{
			return false;
		}
		$parts = explode(',', $full);

		return Text::_($parts[0]);
	}

	/**
	 * @param   string: an iso3 country code, e.g AUT
	 *
	 * @return string: a country name
	 */
	public static function getCountryName($iso3)
	{
		$app = Factory::getApplication();
		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('name');
		// From table
		$query->from('#__sportsmanagement_countries');
		$query->where('alpha3 LIKE \'' . $iso3 . '\'');


		$db->setQuery($query);
		$res = $db->loadResult();

		if ($res)
		{
			return Text::_($res);
		}
	}

	/**
	 * JSMCountries::convertAddressString()
	 *
	 * @param   string  $name
	 * @param   string  $address
	 * @param   string  $state
	 * @param   string  $zipcode
	 * @param   string  $location
	 * @param   string  $country
	 * @param   string  $addressString
	 *
	 * @return
	 */
	public static function convertAddressString($name = '', $address = '', $state = '', $zipcode = '', $location = '', $country = '', $addressString = 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM')
	{
		$resultString = '';

		if ((!empty($address))
			|| (!empty($state))
			|| (!empty($zipcode))
			|| (!empty($location))
		)
		{
			$countryFlag  = self::getCountryFlag($country);
			$countryName  = self::getCountryName($country);
			$dummy        = Text::_($addressString);
			$dummy        = str_replace('%NAME%', '<span itemprop="name">'.$name.'</span>', $dummy);
			$dummy        = str_replace('%ADDRESS%', '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><span itemprop="streetAddress">'.$address.'</span>', $dummy);
			$dummy        = str_replace('%STATE%', '<span itemprop="addressRegion">'.$state.'</span>', $dummy);
			$dummy        = str_replace('%ZIPCODE%', '<span itemprop="postalCode">'.$zipcode.'</span>', $dummy);
			$dummy        = str_replace('%LOCATION%', '<span itemprop="addressLocality">'.$location.'</span>', $dummy);
			$dummy        = str_replace('%FLAG%', $countryFlag, $dummy);
			$dummy        = str_replace('%COUNTRY%', $countryName, $dummy);
			$resultString .= $dummy;
		}
		$resultString .= '</div>&nbsp;';

		return $resultString;
	}

	/**
	 * example: echo JSMCountries::getCountryFlag($country);
	 *
	 * @param   string: an iso3 country code, e.g AUT
	 * @param   string: additional html attributes for the img tag
	 *
	 * @return string: html code for the flag image
	 */
	public static function getCountryFlag($countrycode, $attributes = '')
	{


		$app = Factory::getApplication();
		// JInput object
		$jinput   = $app->input;
		$option   = $jinput->getCmd('option');
		$params   = ComponentHelper::getParams('com_sportsmanagement');
		$cssflags = $params->get('cfg_flags_css');

		// Get a db connection.
		$db = sportsmanagementHelper::getDBConnection();

		$iso2 = self::convertIso3to2($countrycode);
		//$src = self::getIso2Flag($countrycode);
		$src = self::getIso3Flag($countrycode);
		if (!$src)
		{
			// Create a new query object.
			$query = $db->getQuery(true);

			$query->select('picture');
			// From table
			$query->from('#__sportsmanagement_countries');
			$query->where('alpha3 LIKE \'' . $countrycode . '\'');
			$db->setQuery($query);
			$src = $db->loadResult();
		}

		if (!$src)
		{
			$src = ComponentHelper::getParams($option)->get('ph_flags', '');
		}
		else
		{
			$src = $src;
		}

		if ($cssflags == 0)
		{
			$html = '<img src="' . Uri::root() . $src . '" alt="' . self::getCountryName($countrycode) . '" ';
			$html .= 'title="' . self::getCountryName($countrycode) . '" ' . $attributes . ' />';
		}
		else
		{
			switch ($countrycode) {
			case "WAL":  $countrycode = "gb-wls"; break;
			case "SCO":  $countrycode = "gb-sct"; break;
			case "GBR":  $countrycode = "gb-eng"; break;
			default: $countrycode = $iso2; break;
			}
			$countrycode = strtolower($countrycode);
			$html        = '<span class="flag-icon flag-icon-' . $countrycode . '"></span>';
		}

		return $html;
	}

	/**
	 * JSMCountries::getIso3Flag()
	 *
	 * @param   mixed  $iso_code_3
	 *
	 * @return
	 */
	public static function getIso3Flag($iso_code_3)
	{
		$iso2 = self::convertIso3to2($iso_code_3);
		if ($iso2)
		{
			$path = 'images/com_sportsmanagement/database/flags/' . strtolower($iso2) . '.png';

			return $path;
		}

		return null;
	}

	/**
	 * JSMCountries::getIso2Flag()
	 *
	 * @param   mixed  $iso_code_2
	 *
	 * @return
	 */
	public static function getIso2Flag($iso_code_2)
	{
		if ($iso_code_2)
		{
			$path = 'images/com_sportsmanagement/database/flags/' . strtolower($iso_code_2) . '.png';

			return $path;
		}

		return null;
	}

	/**
	 * JSMCountries::convertIso3to2()
	 *
	 * @param   mixed  $iso_code_3
	 *
	 * @return
	 */
	public static function convertIso3to2($iso_code_3)
	{
		$app = Factory::getApplication();
		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select('alpha2');
		// From table
		$query->from('#__sportsmanagement_countries');
		$query->where('alpha3 LIKE \'' . $iso_code_3 . '\'');

		$db->setQuery($query);

		$res = $db->loadResult();
		if ($res)
		{
			return $res;
		}


		return null;
	}

	/**
	 * JSMCountries::removeEmptyFields()
	 *
	 * @param   string  $name
	 * @param   string  $address
	 * @param   string  $state
	 * @param   string  $zipcode
	 * @param   string  $location
	 * @param   string  $flag
	 * @param   string  $country
	 * @param   mixed   $address
	 *
	 * @return
	 */
	public static function removeEmptyFields($name = '', $address = '', $state = '', $zipcode = '', $location = '', $flag = '', $country = '', $addressString = 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM')
	{

		if (empty($name))
		{
			$address = self::checkAddressString('%NAME%', '', $address);
		}
		if (empty($address))
		{
			$address = self::checkAddressString('%ADDRESS%', '', $address);
		}
		if (empty($state))
		{
			$address = self::checkAddressString('%STATE%', '', $address);
		}
		if (empty($zipcode))
		{
			$address = self::checkAddressString('%ZIPCODE%', '', $address);
		}
		if (empty($location))
		{
			$address = self::checkAddressString('%LOCATION%', '', $address);
		}
		if (empty($flag))
		{
			$address = self::checkAddressString('%FLAG%', '', $address);
		}
		if (empty($country))
		{
			$address = self::checkAddressString('%COUNTRY%', '', $address);
		}

		return $address;
	}

	/**
	 * JSMCountries::checkAddressString()
	 *
	 * @param   mixed  $find
	 * @param   mixed  $replace
	 * @param   mixed  $string
	 *
	 * @return
	 */
	public static function checkAddressString($find, $replace, $string)
	{

		$pos = strpos($string, $find);
		if ($pos === false)
		{

		}
		else
		{
			$startpos = $pos + strlen($find);
			if (empty($replace))
			{
				$nextpos = strpos($string, '%', $startpos);
				if ($nextpos === false)
				{
					if ($startpos == strlen($string))
					{
						$dummy   = substr($string, 0, $pos);
						$nextpos = strrpos($dummy, '%');
						if ($nextpos === false)
						{

						}
						else
						{
							$string = substr($dummy, 0, $nextpos + 1);
						}
					}
				}
				else
				{
					$dummy  = $string;
					$string = substr($dummy, 0, $pos);
					$string .= substr($dummy, $nextpos);
				}
			}
			else
			{
				$string = str_replace($find, $replace, $string);
			}
		}

		return $string;
	}

	/**
	 * JSMCountries::Countries()
	 *
	 * @return void
	 */
	function Countries()
	{

	}

}

?>
