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
 * modSportsmanagementTeamStatHelper
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class modSportsmanagementTeamStatHelper
{

	/**
	 * Method to get the list
	 *
	 * @access public
	 * @return array
	 */
	public static function getData(&$params)
	{
	   $mainframe = JFactory::getApplication();
		$db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'');

		sportsmanagementModelProject::setProjectId($params->get('p'));
		$stat_id = (int)$params->get('sid');
		
        if ( $stat_id )
        {		
		$project = sportsmanagementModelProject::getProject();
		$stat = current(current(sportsmanagementModelProject::getProjectStats($stat_id,0,0)));
        //$stat = sportsmanagementModelProject::getProjectStats($stat_id,0,0);
		if (!$stat) 
        {
			echo 'Undefined stat<br>';
		}
		
//        echo ' stat<br><pre>'.print_r($stat,true).'</pre>';
//        echo ' current stat<br><pre>'.print_r(current($stat),true).'</pre>';
//        echo ' 2 current stat<br><pre>'.print_r(current(current($stat)),true).'</pre>';
        
		$ranking = $stat->getTeamsRanking($project->id, $params->get('limit'), 0, $params->get('ranking_order', 'DESC'));
		if (empty($ranking)) 
        {
			return false;
		}
		
		$ids = array();
		foreach ($ranking as $r)
		{
			$ids[] = $db->Quote($r->team_id);
		}
        
        $query->select('t.*, c.logo_small');
        $query->select('CASE WHEN CHAR_LENGTH( t.alias ) THEN CONCAT_WS( \':\', t.id, t.alias ) ELSE t.id END AS team_slug');
        $query->select('CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( \':\', c.id, c.alias ) ELSE c.id END AS club_slug');
        $query->from('#__sportsmanagement_team as t ');
        $query->join('LEFT',' #__sportsmanagement_club AS c ON c.id = t.club_id ');
        $query->where('t.id IN ('.implode(',', $ids).')');

		$db->setQuery($query);
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$teams = $db->loadObjectList('id');
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return array('project' => $project, 'ranking' => $ranking, 'teams' => $teams, 'stat' => $stat);
        }
        else
        {
            return FALSE;
        }
	}
		
	/**
	 * get img for team
	 * @param object ranking row
	 * @param int type = 1 for club small logo, 2 for country
	 * @return html string
	 */
	public static function getLogo($item, $type = 1)
	{
		if ($type == 1) // club small logo
		{
			if (!empty($item->logo_small))
			{
				return JHTML::image($item->logo_small, $item->short_name, 'class="teamlogo"');
			}
		}		
		else if ($type == 2 && !empty($item->country))
		{
			return JSMCountries::getCountryFlag($item->country, 'class="teamcountry"');
		}
		
		return '';
	}

	/**
	 * modSportsmanagementTeamStatHelper::getTeamLink()
	 * 
	 * @param mixed $item
	 * @param mixed $params
	 * @param mixed $project
	 * @return
	 */
	public static function getTeamLink($item, $params, $project)
	{
		switch ($params->get('teamlink'))
		{
			case 'teaminfo':
				return sportsmanagementHelperRoute::getTeamInfoRoute($project->slug, $item->team_slug);
			case 'roster':
				return sportsmanagementHelperRoute::getPlayersRoute($project->slug, $item->team_slug);
			case 'teamplan':
				return sportsmanagementHelperRoute::getTeamPlanRoute($project->slug, $item->team_slug);
			case 'clubinfo':
				return sportsmanagementHelperRoute::getClubInfoRoute($project->slug, $item->club_slug);
				
		}
	}

	/**
	 * modSportsmanagementTeamStatHelper::getStatIcon()
	 * 
	 * @param mixed $stat
	 * @return
	 */
	public static function getStatIcon($stat)
	{
		if ($stat->icon == 'media/com_sportsmanagement/event_icons/event.gif')
		{
			$txt = $stat->name;
		}
		else
		{
			$imgTitle=JText::_($stat->name);
			$imgTitle2=array(' title' => $imgTitle, ' alt' => $imgTitle);
			$txt=JHTML::image($stat->icon, $imgTitle, $imgTitle2);
		}
		return $txt;
	}
}