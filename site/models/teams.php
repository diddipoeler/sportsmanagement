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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelTeams
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelTeams extends JModel
{
	var $projectid = 0;
	var $divisionid = 0;
	var $teamid = 0;
	var $team = null;
	var $club = null;

	/**
	 * sportsmanagementModelTeams::__construct()
	 * 
	 * @return void
	 */
	function __construct( )
	{
		$this->projectid = JRequest::getInt( "p", 0 );
		$this->divisionid = JRequest::getInt( "division", 0 );
        
        sportsmanagementModelProject::$projectid = $this->projectid; 

		parent::__construct( );
	}

	/**
	 * sportsmanagementModelTeams::getDivision()
	 * 
	 * @return
	 */
	function getDivision()
	{
		$division = null;
		if ($this->divisionid != 0)
		{
			$division = parent::getDivision($this->divisionid);
		}
		return $division;
	}

//	/**
//	 * sportsmanagementModelTeams::getTeams()
//	 * 
//	 * @return
//	 */
//	function getTeams()
//	{
//		$teams = array();
//
//		$query = "SELECT
//                    tl.id AS projectteamid,
//                    tl.team_id,
//                    tl.picture projectteam_picture,
//                    tl.project_id,
//                    t.id,
//                    t.name as team_name,
//                    t.short_name,
//                    t.middle_name,
//                    t.club_id,
//                    t.website AS team_www,
//                    t.picture team_picture,
//                    c.name as club_name,
//                    c.address as club_address,
//                    c.zipcode as club_zipcode,
//                    c.state as club_state,
//                    c.location as club_location,
//                    c.email as club_email,
//                    c.logo_big,
//                    c.unique_id,
//                    c.logo_small,
//                    c.logo_middle,
//                    c.country as club_country,
//                    c.website AS club_www,
//				    CASE WHEN CHAR_LENGTH( t.alias ) THEN CONCAT_WS( ':', t.id, t.alias ) ELSE t.id END AS team_slug,
//				    CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( ':', c.id, c.alias ) ELSE c.id END AS club_slug
//                  FROM #__joomleague_project_team tl
//                  LEFT JOIN #__joomleague_team t ON tl.team_id = t.id
//                  LEFT JOIN #__joomleague_club c ON t.club_id = c.id
//                  LEFT JOIN #__joomleague_division d ON d.id = tl.division_id
//                  LEFT JOIN #__joomleague_playground plg ON plg.id = tl.standard_playground
//                  WHERE tl.project_id = " . (int)$this->projectid;
//
//		if ( $this->divisionid > 0 )
//		{
//			$query .= " AND tl.division_id = " . $this->divisionid;
//		}
//		$query .= " ORDER BY t.name";
//
//		$this->_db->setQuery($query);
//		if ( ! $teams = $this->_db->loadObjectList() )
//		{
//			echo $this->_db->getErrorMsg();
//		}
//
//		return $teams;
//	}

}
?>