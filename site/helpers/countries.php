<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.utilities.arrayhelper' );

if (! defined('JSM_PATH'))
{
	DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php'); 

/**
 * JSMCountries
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JSMCountries
{
	/**
	 * JSMCountries::Countries()
	 * 
	 * @return void
	 */
	function Countries() 
    {
//      $lang = JFactory::getLanguage();
//		$extension = "com_sportsmanagement_countries";
//		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
//		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
//		||	$lang->load($extension, $source, null, false, false)
//		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
//		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
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
	 * JSMCountries::getCountries()
	 * 
	 * @return void
	 */
	public static function getCountries()
	{

	}

	/**
	 * JSMCountries::getCountryOptions()
	 * 
	 * @param string $value_tag
	 * @param string $text_tag
	 * @return
	 */
	public static function getCountryOptions($value_tag='value', $text_tag='text')
	{
		$app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // welche tabelle soll genutzt werden
//$params = JComponentHelper::getParams( 'com_sportsmanagement' );
//$database_table	= $params->get( 'cfg_which_database_table' );

        // Get a db connection.
$db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
 
// Create a new query object.
$query = $db->getQuery(true);
        // Select some fields
		$query->select('alpha3,name');
        // From table
		$query->from('#__sportsmanagement_countries');
        //$query->from('#__SPORTSMANAGEMENT_countries');
        // Reset the query using our newly populated query object.
		$db->setQuery($query);
		$countries = $db->loadAssocList();
		
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($countries,true).'</pre>'),'');
        
		$options=array();
		foreach ($countries AS $k )
		{
			$options[]=JHtml::_('select.option',$k['alpha3'],JText::_($k['name']),$value_tag,$text_tag);
		}
		
		//Now Sort the countries
		$options = self::sortCountryArray($options,"text");
		return $options;
	}

	/**
	 * JSMCountries::convertIso2to3()
	 * 
	 * @param mixed $iso_code_2
	 * @return
	 */
	public static function convertIso2to3($iso_code_2)
	{
	  $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // welche tabelle soll genutzt werden
//$params = JComponentHelper::getParams( 'com_sportsmanagement' );
//$database_table	= $params->get( 'cfg_which_database_table' );

	// Get a db connection.
$db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
// Create a new query object.
$query = $db->getQuery(true);
	  // Select some fields
		$query->select('alpha3');
        // From table
		$query->from('#__sportsmanagement_countries');
        $query->where('alpha2 LIKE \''.$iso_code_2.'\'');
        
    
		$db->setQuery($query);
		$res = $db->loadResult();
		if ($res)
		{
			return $res;
		}
		return null;
	}

	/**
	 * JSMCountries::convertIso3to2()
	 * 
	 * @param mixed $iso_code_3
	 * @return
	 */
	public static function convertIso3to2($iso_code_3)
	{
	    $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // welche tabelle soll genutzt werden
//$params = JComponentHelper::getParams( 'com_sportsmanagement' );
//$database_table	= $params->get( 'cfg_which_database_table' );
	// Get a db connection.
$db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
// Create a new query object.
$query = $db->getQuery(true);
// Select some fields
		$query->select('alpha2');
        // From table
		$query->from('#__sportsmanagement_countries');
        $query->where('alpha3 LIKE \''.$iso_code_3.'\'');
        
	  
		
        $db->setQuery($query);
        
		$res = $db->loadResult();
		if ($res)
		{
			return $res;
		}
	
		
		return null;
	}

	/**
	 * JSMCountries::getIso3Flag()
	 * 
	 * @param mixed $iso_code_3
	 * @return
	 */
	public static function getIso3Flag($iso_code_3)
	{
		$iso2 = self::convertIso3to2($iso_code_3);
		if ($iso2)
		{
			$path = COM_SPORTSMANAGEMENT_PICTURE_SERVER.'images/com_sportsmanagement/database/flags/'.strtolower($iso2).'.png';
//            if ( !JFile::exists(COM_SPORTSMANAGEMENT_PICTURE_SERVER.'images/com_sportsmanagement/database/flags/'.strtolower($iso2).'.png') )
//			{
//                $path = COM_SPORTSMANAGEMENT_PICTURE_SERVER.'administrator/components/com_sportsmanagement/assets/images/delete.png';
//            }    
			return $path;
		}
		return null;
	}

	/**
	 * example: echo JSMCountries::getCountryFlag($country);
	 *
	 * @param string: an iso3 country code, e.g AUT
	 * @param string: additional html attributes for the img tag
	 * @return string: html code for the flag image
	 */
	public static function getCountryFlag($countrycode,$attributes='')
	{
		$app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );

        $src = self::getIso3Flag($countrycode);
		if (!$src)
        {
            //return '';
            // welche tabelle soll genutzt werden
        //$params = JComponentHelper::getParams( 'com_sportsmanagement' );
        //$database_table	= $params->get( 'cfg_which_database_table' );
        // Create a new query object.
        $query = $db->getQuery(true);
        // Select some fields
		$query->select('picture');
        // From table
		$query->from('#__sportsmanagement_countries');
        $query->where('alpha3 LIKE \''.$countrycode.'\'');
		$db->setQuery($query);
		$src = $db->loadResult();
        }
        
        if ( !JFile::exists(JPATH_SITE.DS.$src) )
        {
        $src = JComponentHelper::getParams($option)->get('ph_flags','');
        } 
        else
        {
        $src = COM_SPORTSMANAGEMENT_PICTURE_SERVER.$src;    
        }
        
		$html='<img src="'.$src.'" alt="'.self::getCountryName($countrycode).'" ';
		$html .= 'title="'.self::getCountryName($countrycode).'" '.$attributes.' />';
		return $html;
	}

  /**
   * @param string: an iso3 country code, e.g AUT
   * @return string: a country name
   */
	public static function getCountryName($iso3)
	{
	   $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // welche tabelle soll genutzt werden
//$params = JComponentHelper::getParams( 'com_sportsmanagement' );
//$database_table	= $params->get( 'cfg_which_database_table' );
	// Get a db connection.
$db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
// Create a new query object.
$query = $db->getQuery(true);
// Select some fields
		$query->select('name');
        // From table
		$query->from('#__sportsmanagement_countries');
        $query->where('alpha3 LIKE \''.$iso3.'\'');
        		
		
    
		$db->setQuery($query);
		$res = $db->loadResult();
		
		//$countries=JSMCountries::getCountries();
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

		$full=self::getCountryName($iso3);
		if (empty($full)){return false;}
		$parts=explode(',', $full);
		return JText::_($parts[0]);
	}

  /**
   * @param array:
   * @return array:
   */	
	static function sortCountryArray($array,$index)
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

	/**
	 * JSMCountries::convertAddressString()
	 * 
	 * @param string $name
	 * @param string $address
	 * @param string $state
	 * @param string $zipcode
	 * @param string $location
	 * @param string $country
	 * @param string $addressString
	 * @return
	 */
	public static function convertAddressString(	$name='',
									$address='',
									$state='',
									$zipcode='',
									$location='',
									$country='',
									$addressString='COM_SPORTSMANAGEMENT_ADDRESS_FORM')
	{
		$resultString='';

		if ((!empty($address)) ||
			 (!empty($state))	||
			 (!empty($zipcode)) ||
			 (!empty($location))
		  )
		{
			$countryFlag = self::getCountryFlag($country);
			$countryName = self::getCountryName($country);
			$dummy=self::removeEmptyFields($name, $address, $state, $zipcode, $location, $countryFlag, $countryName, JText::_($addressString));
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
	
	/**
	 * JSMCountries::removeEmptyFields()
	 * 
	 * @param string $name
	 * @param string $address
	 * @param string $state
	 * @param string $zipcode
	 * @param string $location
	 * @param string $flag
	 * @param string $country
	 * @param mixed $address
	 * @return
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
	  if (empty($name)) $address = self::checkAddressString('%NAME%', '', $address);
	  if (empty($address)) $address = self::checkAddressString('%ADDRESS%', '', $address);
	  if (empty($state)) $address = self::checkAddressString('%STATE%', '', $address);
	  if (empty($zipcode)) $address = self::checkAddressString('%ZIPCODE%', '', $address);
	  if (empty($location)) $address = self::checkAddressString('%LOCATION%', '', $address);
	  if (empty($flag)) $address = self::checkAddressString('%FLAG%', '', $address);
	  if (empty($country)) $address = self::checkAddressString('%COUNTRY%', '', $address);
	  
		return $address;
	}

	/**
	 * JSMCountries::checkAddressString()
	 * 
	 * @param mixed $find
	 * @param mixed $replace
	 * @param mixed $string
	 * @return
	 */
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