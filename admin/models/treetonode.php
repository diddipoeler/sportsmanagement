<?php
/**
 * @copyright	Copyright (C) 2006-2014 joomleague.at. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
require_once ( JPATH_COMPONENT . DS . 'models' . DS . 'item.php' );

/**
 * Joomleague Component treeto Model
 *
 * @author	comraden
 * @package	JoomLeague
 * @
 */
class JoomleagueModelTreetonode extends JoomleagueModelItem
{
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_data ) )
		{
			$query =	'	SELECT ttn.*
							FROM #__joomleague_treeto_node AS ttn
							WHERE ttn.id = ' . (int) $this->_id;

			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_data ) )
		{
			$node = new stdClass();
			$node->id				= 0;
			$node->treeto_id		= 0;
			$node->node				= 0;
			$node->row				= 0;
			$node->bestof			= 0;
			$node->title			= null;
			$node->content			= null;
			$node->team_id			= 0;
			$node->published		= 0;
			$node->is_leaf			= 0;
			$node->is_lock			= 0;
			$node->got_lc			= 0;
			$node->got_rc			= 0;
			$node->checked_out		= 0;
			$node->checked_out_time	= 0;
			$node->modified			= null;
			$node->modified_by		= null;
			
			$this->_data			= $node;
			return (boolean) $this->_data;
		}
		return true;
	}

	function getNodeMatch()
	{
		$option = JRequest::getCmd('option');

		$mainframe	= JFactory::getApplication();
		//$division_id = $mainframe->getUserState( $option . 'division_id' );
		$query = ' SELECT mc.id AS mid ';
	//	$query .=	' CONCAT(t1.name, \'_\', mc.team1_result, \':\', mc.team2_result, \'_\',  t2.name) AS text ';
		$query .=	' ,mc.match_number AS match_number';
		$query .=	' ,t1.name AS projectteam1';
		$query .=	' ,mc.team1_result AS projectteam1result';
		$query .=	' ,mc.team2_result AS projectteam2result';
		$query .=	' ,t2.name AS projectteam2';
		$query .=	' ,mc.round_id AS rid ';
		$query .=	' ,mc.published AS published ';
		$query .=	' ,ttm.node_id AS node_id ';
		$query .=	' FROM #__joomleague_match AS mc ';
		$query .=	' LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
		$query .=	' LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
		$query .=	' LEFT JOIN #__joomleague_team AS t1 ON t1.id = pt1.team_id ';
		$query .=	' LEFT JOIN #__joomleague_team AS t2 ON t2.id = pt2.team_id ';
		$query .=	' LEFT JOIN #__joomleague_round AS r ON r.id = mc.round_id ';
		$query .=	' LEFT JOIN #__joomleague_treeto_match AS ttm ON mc.id = ttm.match_id ';
		$query .=	' WHERE ttm.node_id = ' . (int) $this->_id;
		$query .=	' ORDER BY mid ASC ';
		$query .=';';

		$this->_db->setQuery( $query );

		if ( !$result = $this->_db->loadObjectList() )
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	function setUnpublishNode()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$post	= 	JRequest::get( 'post' );
		$id = 	(int) $post['id'];
		
		$query = ' UPDATE #__joomleague_treeto_node AS ttn ';
		$query .= ' SET ';
		$query .= ' ttn.published = 0 ';
		$query .= ' WHERE ttn.id = ' . $id;
		$query .= ';';
		$this->_db->setQuery( $query );
		$this->_db->query( $query );
	
		return true;
	}
	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'treetonode', $prefix = 'table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_joomleague.'.$this->name, $this->name,
				array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_joomleague.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
}
?>