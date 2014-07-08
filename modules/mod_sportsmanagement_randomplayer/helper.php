<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access');


class modJSMRandomplayerHelper
{

	/**
	 * Method to get the list
	 *
	 * @access public
	 * @return array
	 */
	function getData(&$params)
	{
		$mainframe = JFactory::getApplication();
        $usedp = $params->get('projects');
		$usedtid = $params->get('teams', '0');
		$projectstring = (is_array($usedp)) ? implode(",", $usedp) : $usedp;
		$teamstring = (is_array($usedtid)) ? implode(",", $usedtid) : $usedtid;

		$db  = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $query->select('tt.id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tt ');
        $query->where('tt.project_id > 0');
                    
		if($projectstring!="" && $projectstring > 0) 
        {
            $query->where('tt.project_id IN ('.$projectstring.')' );
		}
		if($teamstring!="" && $teamstring > 0) 
        {
            $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = tt.team_id ');
            $query->where('st1.team_id IN ('.$teamstring.')' );
		}

        $query->order('rand()');
        $query->setLimit('1');
        
		$db->setQuery( $query );
		$projectteamid = $db->loadResult();
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.' projectteamid<pre>'.print_r($projectteamid,true).'</pre>' ),'Error');
        
        $query = $db->getQuery(true);
        $query->clear();

		$query->select('stp1.person_id');
        $query->select('pt.project_id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id as stp1 ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.team_id = stp1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st1.id ');
        $query->where('pt.id = ' . $projectteamid);
        
        $query->order('rand()');
        $query->setLimit('1');
        
        $db->setQuery( $query );
		$res = $db->loadRow();
        
        if ( !$res )
        {
        JError::raiseWarning(0, 'Keine Spieler vorhanden');      
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');    
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'Error');
        }
        else
        {
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($res,true).'</pre>' ),'Error');
        }

		JRequest::setVar( 'p', $res[1] );
		JRequest::setVar( 'pid', $res[0]);
		JRequest::setVar( 'pt', $projectteamid);

		if (!class_exists('sportsmanagementModelPlayer')) {
			//require_once(JLG_PATH_SITE.DS.'models'.DS.'player.php');
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'player.php');
		}
        if (!class_exists('sportsmanagementModelPerson')) {
			//require_once(JLG_PATH_SITE.DS.'models'.DS.'player.php');
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'person.php');
		}

		$mdlPerson 	= JModelLegacy::getInstance('Person', 'sportsmanagementModel');
        $mdlPlayer 	= JModelLegacy::getInstance('Player', 'sportsmanagementModel');

		$person 	= $mdlPerson->getPerson();
		$project	= sportsmanagementModelProject::getProject();
		$info		= $mdlPlayer->getTeamPlayer();
		$infoteam	= sportsmanagementModelProject::getTeaminfo($projectteamid);

		return array(	'project' 		=> $project,
						'player' 		=> $person, 
						'inprojectinfo'	=> is_array($info) && count($info) ? $info[0] : $info,
						'infoteam'		=> $infoteam);
	}
}