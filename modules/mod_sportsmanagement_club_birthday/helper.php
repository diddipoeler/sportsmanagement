<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      helper.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_club_birthday
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* welche joomla version ?
*/
if( version_compare(substr(JVERSION,0,1),'4','eq') ) 
{
JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.arrayhelper', JPATH_SITE);
////require_once(JPATH_BASE.DS.'libraries'.DS.'vendor'.DS.'joomla'.DS.'utilities'.DS.'src'.DS.'ArrayHelper.php');	
////add the classes for handling
//$classpath = JPATH_BASE.DS.'libraries'.DS.'vendor'.DS.'joomla'.DS.'utilities'.DS.'src'.DS.'ArrayHelper.php';
//JLoader::register('ArrayHelper', $classpath);
//JModelLegacy::getInstance("ArrayHelper");	
}
else
{
jimport( 'joomla.utilities.arrayhelper' );
}

$clubs = array();
$crew = array();

/**
 * modSportsmanagementClubBirthdayHelper
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class modSportsmanagementClubBirthdayHelper
{
    
/**
 * modSportsmanagementClubBirthdayHelper::jsm_birthday_sort()
 * 
 * @param mixed $array
 * @param mixed $sort
 * @return
 */
public static function jsm_birthday_sort ($array, $sort) 
{

    /**
	 * Utility function to sort an array of objects on a given field
	 *
	 * @param   array  &$a             An array of objects
	 * @param   mixed  $k              The key (string) or a array of key to sort on
	 * @param   mixed  $direction      Direction (integer) or an array of direction to sort in [1 = Ascending] [-1 = Descending]
	 * @param   mixed  $caseSensitive  Boolean or array of booleans to let sort occur case sensitive or insensitive
	 * @param   mixed  $locale         Boolean or array of booleans to let sort occur using the locale language or not
	 *
	 * @return  array  The sorted array of objects
	 *
	 * @since   11.1
	 */
     
/**
 * welche joomla version ?
 */
if( version_compare(substr(JVERSION,0,1),'4','eq') ) 
{
	//$res = ArrayHelper::sortObjects($array,'age',$sort);
    $res = JArrayHelper::sortObjects($array,'age',$sort);
}
else
{	
	$res = JArrayHelper::sortObjects($array,'age',$sort);
}
        return $res;
	}    


/**
 * modSportsmanagementClubBirthdayHelper::getClubs()
 * 
 * @param mixed $limit
 * @param mixed $season_ids
 * @return
 */
public static function getClubs($limit,$season_ids)
	{
	   $app = JFactory::getApplication();
$birthdaytext = '';
$database = sportsmanagementHelper::getDBConnection();
/**
 * get club info, we have to make a function for this
 */
$dateformat = "DATE_FORMAT(c.founded,'%Y-%m-%d') AS date_of_birth";

if ( $season_ids )
{
foreach ( $season_ids as $key => $val ) 
{
$season_ids[$key] = (int)$val;
}    
$seasons = implode(",",$season_ids); 
}
	$query = $database->getQuery(true);
    $query->select('c.id,c.country,c.founded,c.name,c.alias,c.founded_year,c.logo_big AS picture, DATE_FORMAT(c.founded, \'%m-%d\')AS daymonth,YEAR( CURRENT_DATE( ) ) as year');
    $query->select('(YEAR( CURRENT_DATE( ) ) - YEAR( c.founded ) + IF(DATE_FORMAT(CURDATE(), \'%m.%d\') > DATE_FORMAT(c.founded, \'%m.%d\'), 1, 0)) AS age, YEAR( CURRENT_DATE( ) ) - c.founded_year as age_year');
    $query->select($dateformat);
    $query->select('(TO_DAYS(DATE_ADD(c.founded, INTERVAL(YEAR(CURDATE()) - YEAR(c.founded) + IF(DATE_FORMAT(CURDATE(), \'%m.%d\') > DATE_FORMAT(c.founded, \'%m.%d\'), 1, 0))YEAR)) - TO_DAYS( CURDATE())+0) AS days_to_birthday');
    $query->from('#__sportsmanagement_club AS c ');
    $query->join('INNER',' #__sportsmanagement_team as t ON t.club_id = c.id ');
    $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.team_id = t.id ');
    $query->join('INNER',' #__sportsmanagement_project_team as pt ON st.id = pt.team_id ');
    $query->where('( c.founded != \'0000-00-00\' AND c.founded_year != \'0000\'  AND c.founded_year != \'\' ) ');
	if ( $seasons )
    {
    $query->where('st.season_id IN ('.$seasons.')');    
    }		
    
    $query->group('c.id,c.country,c.founded,c.name,c.alias,c.founded_year,c.logo_big');

    $query->order('days_to_birthday ASC');

try{
    $database->setQuery($query,0,$limit);
    $result = $database->loadObjectList();
	$database->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return $result;
    }
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
    $database->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return false;
}
    
}

}


?>
