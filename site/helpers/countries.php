<?php
/**
* @version		$Id: countries.php 5205 2010-09-24 08:00:00Z
* @package		Joomleague
* @copyright	Copyright (C) 2008 Julien Vonthron. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla Tracks is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.utilities.arrayhelper' );

class Countries
{
	function Countries() {
		$lang = JFactory::getLanguage();
		$extension = "com_joomleague_countries";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
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
	public static function getCountries()
	{

	}

	public static function getCountryOptions($value_tag='value', $text_tag='text')
	{
		$db = Jfactory::getDBO();
    $lang = JFactory::getLanguage();
		$extension = "com_joomleague_countries";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = "SELECT alpha3,name from #__joomleague_countries";
		$db->setQuery($query);
		$countries = $db->loadAssocList();
    
		
		$options=array();
		foreach ($countries AS $k )
		{
			$options[]=JHTML::_('select.option',$k['alpha3'],JText::_($k['name']),$value_tag,$text_tag);
		}
		
		//Now Sort the countries
		$options=Countries::sortCountryArray($options,"text");
		return $options;
	}

	public static function convertIso2to3($iso_code_2)
	{
	$db = Jfactory::getDBO();
	  
		$query = "SELECT alpha3 from #__joomleague_countries
    where alpha2 like '".$iso_code_2."'";
		$db->setQuery($query);
		$res = $db->loadResult();
		if ($res)
		{
			return $res;
		}
		return null;
	}

	public static function convertIso3to2($iso_code_3)
	{
	$db = Jfactory::getDBO();
	  $query = "SELECT alpha2 from #__joomleague_countries
    where alpha3 like '".$iso_code_3."'";
		$db->setQuery($query);
		$res = $db->loadResult();
		if ($res)
		{
			return $res;
		}
	
		
		return null;
	}

	public static function getIso3Flag($iso_code_3)
	{
		$iso2=Countries::convertIso3to2($iso_code_3);
		if ($iso2)
		{
			$path=JURI::root().'media/com_joomleague/flags/'.strtolower($iso2).'.png';
			return $path;
		}
		return null;
	}

	/**
	 * example: echo Countries::getCountryFlag($country);
	 *
	 * @param string: an iso3 country code, e.g AUT
	 * @param string: additional html attributes for the img tag
	 * @return string: html code for the flag image
	 */
	public static function getCountryFlag($countrycode,$attributes='')
	{
		$src=Countries::getIso3Flag($countrycode);
		if (!$src){return '';}
		$html='<img src="'.$src.'" alt="'.Countries::getCountryName($countrycode).'" ';
		$html .= 'title="'.Countries::getCountryName($countrycode).'" '.$attributes.' />';
		return $html;
	}

  /**
   * @param string: an iso3 country code, e.g AUT
   * @return string: a country name
   */
	public static function getCountryName($iso3)
	{
	$db = Jfactory::getDBO();
		$lang = JFactory::getLanguage();
		$extension = "com_joomleague_countries";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = "SELECT name from #__joomleague_countries
    where alpha3 like '".$iso3."'";
		$db->setQuery($query);
		$res = $db->loadResult();
		
		//$countries=Countries::getCountries();
		if( $res )
		//return JText::_($countries[$iso3]['name']);
		return JText::_($res);
	}

  /**
   * @param string: an iso3 country code, e.g AUT
   * @return string: a country name, short form
   */
	public static function getShortCountryName($iso3)
	{
		$lang = JFactory::getLanguage();
		$extension = "com_joomleague_countries";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		$full=self::getCountryName($iso3);
		if (empty($full)){return false;}
		$parts=explode(',', $full);
		return JText::_($parts[0]);
	}

  /**
   * @param array:
   * @return array:
   */	
	function sortCountryArray($array,$index)
	{
	$sort=array() ;
	$result=array() ;
	
	for ($i=0; isset($array[$i]); $i++)
	$sort[$i]= $array[$i]->{$index};
	
	natcasesort($sort) ;
	
	foreach($sort as $k=>$v)	
	$result[]=$array[$k] ;
	
	return $result;
	}
	
	
/*
Turkish Address-Way:
John Deere Makinalari Limited Sirketi

Centrum Is Merkezi Aydinevler Sanayi Cad. No.3 Kat 4
Küçükyali / Maltepe / Istanbul 34854
Türkiye
*/
	public static function convertAddressString(	$name='',
									$address='',
									$state='',
									$zipcode='',
									$location='',
									$country='',
									$addressString='COM_JOOMLEAGUE_ADDRESS_FORM')
	{
		$resultString='';

		if ((!empty($address)) ||
			 (!empty($state))	||
			 (!empty($zipcode)) ||
			 (!empty($location))
		  )
		{
			$countryFlag = Countries::getCountryFlag($country);
			$countryName = Countries::getCountryName($country);
			$dummy=Countries::removeEmptyFields($name, $address, $state, $zipcode, $location, $countryFlag, $countryName, JText::_($addressString));
			$dummy=str_replace('%NAME%',$name,$dummy);
			$dummy=str_replace('%ADDRESS%',$address,$dummy);
			$dummy=str_replace('%STATE%',$state,$dummy);
			$dummy=str_replace('%ZIPCODE%',$zipcode,$dummy);
			$dummy=str_replace('%LOCATION%',$location,$dummy);
			$dummy=str_replace('%FLAG%',$countryFlag,$dummy);
			$dummy=str_replace('%COUNTRY%',$countryName,$dummy);
			$resultString .= $dummy;
		}
		$resultString .= '&nbsp;';

		return $resultString;
	}
	
/*
captain77
check if address fields not filled then remove that
*/

	public static function removeEmptyFields(	$name='',
									$address='',
									$state='',
									$zipcode='',
									$location='',
									$flag='',
									$country='',
									$address)
	{
	  if (empty($name)) $address = Countries::checkAddressString('%NAME%', '', $address);
	  if (empty($address)) $address = Countries::checkAddressString('%ADDRESS%', '', $address);
	  if (empty($state)) $address = Countries::checkAddressString('%STATE%', '', $address);
	  if (empty($zipcode)) $address = Countries::checkAddressString('%ZIPCODE%', '', $address);
	  if (empty($location)) $address = Countries::checkAddressString('%LOCATION%', '', $address);
	  if (empty($flag)) $address = Countries::checkAddressString('%FLAG%', '', $address);
	  if (empty($country)) $address = Countries::checkAddressString('%COUNTRY%', '', $address);
	  
		return $address;
	}

	public static function checkAddressString($find, $replace, $string)
	{
	
	$pos = strpos($string, $find);
	if ($pos === false) {
	} else {
	  $startpos = $pos + strlen($find);
	  if (empty($replace)) {
	    $nextpos = strpos($string, '%', $startpos);
	    if ($nextpos === false) {
	      if ($startpos == strlen($string)) {
	        $dummy = substr($string, 0, $pos);
	        $nextpos = strrpos($dummy, '%');
	        if ($nextpos === false) {
	        } else {
	        	$string = substr($dummy, 0, $nextpos+1);
					}
	      }
	    } else {
	      $dummy = $string;
	    	$string = substr($dummy, 0, $pos);
	    	$string .= substr($dummy, $nextpos);
			}
	  } else {
	  	$string=str_replace($find,$replace,$string);
		}
	}
	
	return $string;
	}
}
?>