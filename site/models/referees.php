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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );


/**
 * sportsmanagementModelReferees
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelReferees extends JModelLegacy
{
    
    
	/**
	 * sportsmanagementModelReferees::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		
        $this->projectid = JRequest::getInt( 'p', 0 );
		sportsmanagementModelProject::$projectid = $this->projectid;
		parent::__construct();
	}
    
	/**
	 * sportsmanagementModelReferees::getReferees()
	 * 
	 * @return
	 */
	function getReferees()
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
    $query = JFactory::getDbo()->getQuery(true);
    $subquery = JFactory::getDbo()->getQuery(true);
        
        // Select some fields
        $query->select('p.*,p.id AS pid,CONCAT_WS( \':\', p.id, p.alias ) AS slug');
        $query->select('pr.id AS prid, pr.notes AS description');
        $query->select('ppos.position_id');
        $query->select('pos.name AS position,pos.parent_id');
        
        $subquery->select('count(*)');
        $subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
        $subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON m.round_id = r.id');
        $subquery->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS ptt1 ON m.projectteam1_id = ptt1.id');
        $subquery->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS ptt2 ON m.projectteam2_id = ptt2.id');
        $subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr ON m.id = mr.match_id');
        $subquery->where('(ptt1.project_id = pr.project_id or ptt2.project_id = pr.project_id)');
        $subquery->where('mr.project_referee_id=pr.id');
        $query->select('('.$subquery.') AS countGames');
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee as pr ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS o ON o.id = pr.person_id');
        
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as p ON o.person_id = p.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position as ppos ON ppos.id = pr.project_position_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position as pos ON pos.id = ppos.position_id ');
        
        $query->where('pr.project_id = '.$this->projectid);
        $query->where('p.published = 1');
       
        $query->order('pos.ordering');
        $query->order('pos.id');
        
       
        //$query = "	SELECT  , , , 
//					
//					
//						(SELECT count(*) 
//						FROM #__joomleague_match AS m
//						INNER JOIN #__joomleague_round AS r ON m.round_id=r.id 
//						LEFT JOIN #__joomleague_project_team AS ptt1 ON m.projectteam1_id=ptt1.id 
//						LEFT JOIN #__joomleague_project_team AS ptt2 ON m.projectteam2_id=ptt2.id 
//						INNER JOIN #__joomleague_match_referee AS mr ON m.id=mr.match_id 
//						WHERE (ptt1.project_id = pr.project_id or ptt2.project_id = pr.project_id) 
//						AND mr.project_referee_id=pr.id) AS countGames 
//                        
//					FROM #__joomleague_project_referee pr 
//					INNER JOIN #__joomleague_person p ON pr.person_id = p.id 
//					INNER JOIN #__joomleague_project_position ppos ON ppos.id = pr.project_position_id 
//					INNER JOIN #__joomleague_position pos ON pos.id = ppos.position_id 
//					WHERE pr.project_id = " . $this->projectid . "
//					AND p.published = 1
//					ORDER BY pos.ordering, pos.id";

		JFactory::getDbo()->setQuery( $query );
		return JFactory::getDbo()->loadObjectList();
	}

//	function getPositionEventTypes( $positionId = 0 )
//	{
//		$result = array();
//
//		$query = '  SELECT	pet.*,
//							et.name AS name,
//							et.icon AS icon
//					FROM #__joomleague_position_eventtype AS pet
//					INNER JOIN #__joomleague_eventtype AS et ON et.id=pet.eventtype_id
//					INNER JOIN #__joomleague_project_position AS ppos ON ppos.position_id=pet.position_id
//					WHERE ppos.project_id=' . $this->projectid;
//
//		if ( $positionId > 0 )
//		{
//			$query .= ' AND pet.position_id=' . (int)$positionId;
//		}
//
//		$query .= ' ORDER BY et.ordering';
//
//		$this->_db->setQuery( $query );
//
//		$result = $this->_db->loadObjectList();
//
//		if ( $result )
//		{
//			if ( $positionId )
//			{
//				return $result;
//			}
//			else
//			{
//				$posEvents = array();
//				foreach ( $result as $r )
//				{
//					$posEvents[$r->position_id][] = $r;
//				}
//				return ( $posEvents );
//			}
//		}
//		return array();
//	}

}
?>