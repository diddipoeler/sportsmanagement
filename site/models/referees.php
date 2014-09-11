<?php
/**
 * @copyright	Copyright (C) 2006-2014 joomleague.at. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );

require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

class JoomleagueModelReferees extends JoomleagueModelProject
{
	function getReferees()
	{
		$query = "	SELECT p.*, pr.id AS prid, ppos.position_id, p.id AS pid, 
					pos.name AS position, pr.notes AS description, 
					pos.parent_id, 
					CASE WHEN CHAR_LENGTH( p.alias ) THEN CONCAT_WS( ':', p.id, p.alias ) ELSE p.id END AS slug,
						(SELECT count(*) 
						FROM #__joomleague_match AS m
						INNER JOIN #__joomleague_round AS r ON m.round_id=r.id 
						LEFT JOIN #__joomleague_project_team AS ptt1 ON m.projectteam1_id=ptt1.id 
						LEFT JOIN #__joomleague_project_team AS ptt2 ON m.projectteam2_id=ptt2.id 
						INNER JOIN #__joomleague_match_referee AS mr ON m.id=mr.match_id 
						WHERE (ptt1.project_id = pr.project_id or ptt2.project_id = pr.project_id) 
						AND mr.project_referee_id=pr.id) AS countGames 
					FROM #__joomleague_project_referee pr 
					INNER JOIN #__joomleague_person p ON pr.person_id = p.id 
					INNER JOIN #__joomleague_project_position ppos ON ppos.id = pr.project_position_id 
					INNER JOIN #__joomleague_position pos ON pos.id = ppos.position_id 
					WHERE pr.project_id = " . $this->projectid . "
					AND p.published = 1
					ORDER BY pos.ordering, pos.id";

		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	function getPositionEventTypes( $positionId = 0 )
	{
		$result = array();

		$query = '  SELECT	pet.*,
							et.name AS name,
							et.icon AS icon
					FROM #__joomleague_position_eventtype AS pet
					INNER JOIN #__joomleague_eventtype AS et ON et.id=pet.eventtype_id
					INNER JOIN #__joomleague_project_position AS ppos ON ppos.position_id=pet.position_id
					WHERE ppos.project_id=' . $this->projectid;

		if ( $positionId > 0 )
		{
			$query .= ' AND pet.position_id=' . (int)$positionId;
		}

		$query .= ' ORDER BY et.ordering';

		$this->_db->setQuery( $query );

		$result = $this->_db->loadObjectList();

		if ( $result )
		{
			if ( $positionId )
			{
				return $result;
			}
			else
			{
				$posEvents = array();
				foreach ( $result as $r )
				{
					$posEvents[$r->position_id][] = $r;
				}
				return ( $posEvents );
			}
		}
		return array();
	}

}
?>