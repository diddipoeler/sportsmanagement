<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
    //$query->select('pt.project_id');
    $query->from('#__sportsmanagement_club AS c ');
    $query->join('INNER',' #__sportsmanagement_team as t ON t.club_id = c.id ');
    $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.team_id = t.id ');
    $query->join('INNER',' #__sportsmanagement_project_team as pt ON st.id = pt.team_id ');
    //$query->join('INNER',' #__sportsmanagement_project as p ON p.id = pt.project_id ');
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
