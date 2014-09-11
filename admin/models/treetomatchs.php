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

jimport('joomla.application.component.model');
require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');

/**
 * Joomleague Component Treetomatchs Model
 *
 * @package	JoomLeague
 * @since	0.1
 */

class JoomleagueModelTreetomatchs extends JoomleagueModelList
{
	var $_identifier = "treetomatchs";

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		
		$query = ' SELECT mc.id AS mid ';
		$query .=	' ,mc.match_number AS match_number';
		$query .=	' ,t1.name AS projectteam1';
		$query .=	' ,mc.team1_result AS projectteam1result';
		$query .=	' ,mc.team2_result AS projectteam2result';
		$query .=	' ,t2.name AS projectteam2';
		$query .=	' ,mc.round_id AS rid ';
		$query .=	' ,mc.published AS published ';
		$query .=	' ,ttm.node_id AS node_id ';
		$query .=	' ,r.roundcode AS roundcode, mc.checked_out ';
		$query .=	' FROM #__joomleague_match AS mc ';
		$query .=	' LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
		$query .=	' LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
		$query .=	' LEFT JOIN #__joomleague_team AS t1 ON t1.id = pt1.team_id ';
		$query .=	' LEFT JOIN #__joomleague_team AS t2 ON t2.id = pt2.team_id ';
		$query .=	' LEFT JOIN #__joomleague_round AS r ON r.id = mc.round_id ';
		$query .=	' LEFT JOIN #__joomleague_treeto_match AS ttm ON mc.id = ttm.match_id ';
		$query .=	$where ;
		$query .=	$orderby ;
		return $query;
	}

	function _buildContentOrderBy()
	{
		$orderby = ' ORDER BY r.roundcode ';
		
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		
		$mainframe	= JFactory::getApplication();
		$node_id = $mainframe->getUserState($option . 'node_id');
		$where = ' WHERE  ttm.node_id = ' . $node_id ;
		
		return $where;
	}

	function store( $data )
	{
		$result = true;
		$peid = $data['node_matcheslist'];
		if ( $peid == null )
		{
			$query = "	DELETE
						FROM #__joomleague_treeto_match
						WHERE node_id = '" . $data['id'] . "'";
		}
		else
		{
			JArrayHelper::toInteger( $peid );
			$peids = implode( ',', $peid );
			$query = "	DELETE
						FROM #__joomleague_treeto_match
						WHERE node_id = '" . $data['id'] . "' AND match_id NOT IN  (" . $peids . ")";
		}

		$this->_db->setQuery( $query );
		if ( !$this->_db->query() )
		{
			$this->setError( $this->_db->getErrorMsg() );
			$result = false;
		}

		for ( $x = 0; $x < count( $data['node_matcheslist'] ); $x++ )
		{
			$query = "	INSERT IGNORE
						INTO #__joomleague_treeto_match
						(node_id, match_id)
						VALUES ( '" . $data['id'] . "', '".$data['node_matcheslist'][$x] . "')";

			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				$result = false;
			}
		}
		return $result;
	}

	//function getMatchToNode()
	function getMatches()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$node_id = $mainframe->getUserState($option . 'node_id');
		$treeto_id = $mainframe->getUserState($option . 'treeto_id');
		$project_id = $mainframe->getUserState($option . 'project');
		
		$query = ' SELECT mc.id AS value ';
		$query .=	' ,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text ';
		$query .=	' ,mc.id AS info ';
		$query .=	' FROM #__joomleague_match AS mc ';
		$query .=	' LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
		$query .=	' LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
		$query .=	' LEFT JOIN #__joomleague_team AS t1 ON t1.id = pt1.team_id ';
		$query .=	' LEFT JOIN #__joomleague_team AS t2 ON t2.id = pt2.team_id ';
		$query .=	' LEFT JOIN #__joomleague_round AS r ON r.id = mc.round_id ';
		$query .=	' WHERE  r.project_id = ' . $project_id ;
		$query .=	' AND NOT mc.projectteam1_id IN ';
		$query .=	' ( ';
		$query .=		' SELECT ttn.team_id ';
		$query .=		' FROM #__joomleague_treeto_node AS ttn' ;
		$query .=		' LEFT JOIN #__joomleague_treeto_node AS ttn2 ';
		$query .=		' ON (ttn.node = 2*ttn2.node OR ttn.node = 2*ttn2.node + 1) ' ;
		$query .=		' WHERE  ttn2.id = ' . $node_id ;
		$query .=		' AND  ttn.treeto_id = ' . $treeto_id ;
		$query .=	' ) ';
		$query .=	' AND NOT mc.projectteam2_id IN ';
		$query .=	' ( ';
		$query .=		' SELECT ttn.team_id ';
		$query .=		' FROM #__joomleague_treeto_node AS ttn' ;
		$query .=		' LEFT JOIN #__joomleague_treeto_node AS ttn2 ';
		$query .=		' ON (ttn.node = 2*ttn2.node OR ttn.node = 2*ttn2.node + 1) ' ;
		$query .=		' WHERE  ttn2.id = ' . $node_id ;
		$query .=		' AND  ttn.treeto_id = ' . $treeto_id ;
		$query .=	' ) ';
		$query .=	' ORDER BY r.id ';
		$query .=	';';
		
		$this->_db->setQuery( $query );
		if ( !$result = $this->_db->loadObjectList() )
		{
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
		else
		{
			return $result;
		}
	}

	//function getMatchInNode()
	function getNodeMatches($node_id=0)
	{
		$query = ' SELECT mc.id AS value ';
		$query .=	' ,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text ';
		$query .=	' ,mc.id AS notes ';
		$query .=	' ,mc.id AS info ';
		$query .=	' FROM #__joomleague_match AS mc ';
		$query .=	' LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
		$query .=	' LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
		$query .=	' LEFT JOIN #__joomleague_team AS t1 ON t1.id = pt1.team_id ';
		$query .=	' LEFT JOIN #__joomleague_team AS t2 ON t2.id = pt2.team_id ';
		$query .=	' LEFT JOIN #__joomleague_round AS r ON r.id = mc.round_id ';
		$query .=	' LEFT JOIN #__joomleague_treeto_match AS ttm ON mc.id = ttm.match_id ';
		$query .=	' WHERE  ttm.node_id = ' . $node_id ;
		$query .=	' ORDER BY mc.id ';
		$query .=	';';
		$this->_db->setQuery( $query );
		if ( !$result = $this->_db->loadObjectList() )
		{
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
		else
		{
			return $result;
		}
	}

}
?>