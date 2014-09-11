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
 * Joomleague Component Treetonodes Model
 *
 * @package	JoomLeague
 * @since	0.1
 */

class JoomleagueModelTreetonodes extends JoomleagueModelList
{
	var $_identifier = "treetonodes";
	
	function __construct()
	{
		parent::__construct();
		$limit=130;
		$this->setState('limit',$limit);
	}

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		
		$query = '	SELECT	ttn.*, '
			. ' t.name AS team_name, '
			. ' count(ttm.id) AS countmatch, '
			. ' tt.tree_i AS tree_i '
			. ' FROM #__joomleague_treeto_node AS ttn '
			. ' LEFT JOIN #__joomleague_project_team AS pt ON pt.id = ttn.team_id '
			. ' LEFT JOIN #__joomleague_team AS t ON t.id = pt.team_id '
			. ' LEFT JOIN #__joomleague_treeto AS tt ON tt.id = ttn.treeto_id '
			. ' LEFT JOIN #__joomleague_treeto_match AS ttm ON ttn.id = ttm.node_id ';
		$query .= $where ;
		$query .= ' GROUP BY ttn.id ' ;
		$query .=  $orderby ;
		return $query;
	}

	function _buildContentOrderBy()
	{
		// order inside db table
		$orderby 	= ' ORDER BY ttn.row ';
		
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$project_id = $mainframe->getUserState( $option . 'project' );
		$treeto_id = $mainframe->getUserState( $option . 'treeto_id' );
		$where = ' WHERE  ttn.treeto_id = ' . $treeto_id ;
		return $where;
	}

	function getMaxRound($project_id)
	{
		$result=0;
		if ($project_id > 0)
		{
		$query='SELECT COUNT(roundcode) FROM #__joomleague_round WHERE project_id='.$project_id;
			$this->_db->setQuery($query);
			$result=$this->_db->loadResult();
		}
		return $result;
	}

	function setRemoveNode()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$treeto_id = 	$mainframe->getUserState( $option . 'treeto_id' );
		$post	= 	JRequest::get( 'post' );
		$treeto_id = 	(int) $post['treeto_id'];
		
		$query= ' DELETE ttn, ttm ';
		$query .= ' FROM #__joomleague_treeto_node AS ttn ';
		$query .= ' LEFT JOIN #__joomleague_treeto_match AS ttm ON ttm.node_id=ttn.id ';
		$query .= ' WHERE ttn.treeto_id = ' . $treeto_id ;
		$query .= ';';
		$this->_db->setQuery($query);
		$this->_db->query($query);
	
		$query = ' UPDATE #__joomleague_treeto AS tt ';
		$query .= ' SET ';
		$query .= ' tt.tree_i = 0 ';
		$query .= ' ,tt.global_bestof = 1 ';
		$query .= ' ,tt.global_matchday = 0 ';
		$query .= ' ,tt.global_known = 0 ';
		$query .= ' ,tt.global_fake = 0 ';
		$query .= ' ,tt.mirror = 0 ' ;
		$query .= ' ,tt.hide = 0 ' ;
		$query .= ' ,tt.leafed = 0 ' ;
		$query .= ' WHERE tt.id = ' . $treeto_id ;
		$query .= ';';
		$this->_db->setQuery( $query );
		$this->_db->query( $query );
	
		return true;
	}
	// UPDATE selected node as a leaf AND unpublish ALL children node
	function storeshortleaf($cid,$post)
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		//$project_id = $mainframe->getUserState( $option . 'project' );
		$post	= 	JRequest::get( 'post' );
		$result=true;
		$tree_i = 	$post['tree_i'];
		$treeto_id = 	$post['treeto_id'];
		$global_fake = 	$post['global_fake'];
		//if user checked at least ONE node as leaf
		for ($x=0; $x < count($cid); $x++)
		{
			//leaf(ing) this node
			$query=' UPDATE #__joomleague_treeto_node
					SET is_leaf=1
					WHERE id='.$cid[$x];
			$this->_db->setQuery($query);
			$this->_db->query($query);
			//find index of checked node
			$query=' SELECT node 
					FROM #__joomleague_treeto_node
					WHERE id='.$cid[$x];
			$this->_db->setQuery($query);
			$this->_db->query($query);
				$resultleafnode=$this->_db->loadResult();
			//unpublish children node
			if( $resultleafnode < (pow(2,$tree_i)) )
			{
				for($y=1 ; $y<=($tree_i-1) ; $y++)
				{
					$childleft=(pow(2,$y))*$resultleafnode;
					$childright=((pow(2,$y))*($resultleafnode+1))-1;
					for($z = $childleft ; $z <= $childright ; $z++)
					{
						if( $z < pow(2,$tree_i+1) )
						{
							$query=' UPDATE 
								#__joomleague_treeto_node AS ttn
								SET 
								ttn.published=0
								WHERE 
								(ttn.node= ' . $z .
								' AND ttn.treeto_id = ' . $treeto_id . ');';
						$this->_db->setQuery($query);
						$this->_db->query($query);
						}
					}
				}
			}
		}
		// 2, 4, 8, 16, 32, 64 teams, default leaf(ing)
		for($k = pow(2,$tree_i) ; $k < pow(2,$tree_i+1) ; $k++)
		{
			$query=' UPDATE 
				#__joomleague_treeto_node AS ttn
				SET 
				ttn.is_leaf=1
				WHERE 
				(ttn.node= ' . $k .
				' AND ttn.treeto_id= ' . $treeto_id . ');';
				$this->_db->setQuery($query);
				$this->_db->query($query);
		}
		//only for menu
		$query = '	UPDATE #__joomleague_treeto
				SET leafed = 3
				WHERE id=' . $treeto_id;
		$this->_db->setQuery( $query );
		$this->_db->query( $query );

		return $result;
	}
	
	function storefinishleaf()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$project_id = $mainframe->getUserState($option . 'project');
		$post	= 	JRequest::get( 'post' );
		$tree_i = 	$post['tree_i'];
		$treeto_id = 	$post['treeto_id'];
		$global_known = $post['global_known'];
		$global_bestof = $post['global_bestof'];

		$query = ' UPDATE #__joomleague_treeto ';
		$query .= 	' SET leafed = 1 ';
		$query .= 	' WHERE id=' . $treeto_id ;
		$this->_db->setQuery( $query );
		$this->_db->query( $query );
		
		return true;
	}

	function getProjectTeamsOptions()
	{
		$option = JRequest::getCmd('option');

		$mainframe	= JFactory::getApplication();
		$project_id = $mainframe->getUserState($option . 'project');

		$query = ' SELECT	pt.id AS value, '
		. ' CASE WHEN CHAR_LENGTH(t.name) < 45 THEN t.name ELSE t.middle_name END AS text '
		. ' FROM #__joomleague_team AS t '
		. ' LEFT JOIN #__joomleague_project_team AS pt ON pt.team_id = t.id '
		. ' WHERE pt.project_id = ' . $project_id
		. ' ORDER BY text ASC ';

		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, $this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	function storeshort($cid,$post)
	{
		$option = JRequest::getCmd('option');
		$result=true;
		$post	= 	JRequest::get( 'post' );
		
		for ($x=0; $x < count($cid); $x++)
		{
			$query = ' UPDATE #__joomleague_treeto_node ';
			$query .= 	' SET team_id = ' . $post['team_id' . $cid[$x]];
			$query .= 	' WHERE id= ' . $cid[$x] ;
			$this->_db->setQuery($query);
			if(!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				$result=false;
			}
			
		}
		return $result;
	}

}
?>