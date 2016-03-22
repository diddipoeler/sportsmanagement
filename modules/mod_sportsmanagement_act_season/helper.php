<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'countries.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'route.php');



/**
 * modJSMActSeasonHelper
 * 
 * @package 
 * @author Dieter Pl?ger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class modJSMActSeasonHelper
{

	
	
	/**
	 * modJSMActSeasonHelper::getData()
	 * 
	 * @param mixed $season_ids
	 * @return
	 */
	public static function getData($season_ids)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        $date = JFactory::getDate();
	   $user = JFactory::getUser();
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);
        $result = array();
    
//$heutestart = date("Y-m-d");		
//$heuteende = date("Y-m-d");
//		
//$heutestart .= ' 00:00:00';		
//$heuteende .= ' 23:59:00';
$seasons = implode(",",$season_ids); 
$query->select('pro.id,pro.name,pro.current_round as roundcode,CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug,le.name as liganame,le.country');
$query->select('le.picture as league_picture,pro.picture as project_picture');
$query->from('#__sportsmanagement_project as pro');
$query->join('INNER','#__sportsmanagement_league as le on le.id = pro.league_id');
$query->where('le.published_act_season = 1 ');
$query->where('pro.season_id IN ('.$seasons.')');
$query->order('l.country');

$db->setQuery( $query );
$result = $db->loadObjectList();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($season_ids,true).'</pre>'),'Error');

		return $result;
		
	}
	
	

	
}
