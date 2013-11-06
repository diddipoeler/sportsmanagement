<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');


/**
 * Sportsmanagement Component Positionstool Model
 *
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementModelProjectpositions extends JModelList
{
	var $_identifier = "pposition";
    var $_project_id = 0;
	
	protected function getListQuery()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $subQuery1= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
        
        // Select some fields
		$query->select('pt.*,pt.id AS positiontoolid');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS pt');
        // Select some fields
		$query->select('po.*,po.name AS name');
		// From the table
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position po ON pt.position_id=po.id');
        // Select some fields
		$query->select('pid.name AS parent_name');
		// From the table
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position pid ON po.parent_id=pid.id');
        // count 
        $subQuery1->select('count(*)');
        $subQuery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pe ');
        $subQuery1->where('pe.position_id=po.id');
        $query->select('('.$subQuery1.') AS countEvents');
        // count 
        $subQuery2->select('count(*)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ');
        $subQuery2->where('ps.position_id=po.id');
        $query->select('('.$subQuery2.') AS countStats');

        $query->where(self::_buildContentWhere());
		$query->order(self::_buildContentOrderBy());
        return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.po_filter_order','filter_order','po.name','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.po_filter_order_Dir','filter_order_Dir','','word');

		if ($filter_order=='po.name')
		{
			$orderby=' po.parent_id,po.name '.$filter_order_Dir;
		}
		else
		{
			$orderby=' '.$filter_order.' '.$filter_order_Dir.',po.name ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$where =' pt.project_id='.$this->_project_id;
		return $where;
	}

	/**
	 * Method to update project positions list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function store($data)
	{
		//echo '<br /><pre>1~'.print_r($data,true).'~</pre><br />';
		$result=true;
		//$peid=(isset($data['project_teamslist']));
		$peid=(isset($data['project_positionslist']));
		if ($peid==null)
		{
			$query="DELETE FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_position WHERE project_id=".$data['id'];
		}
		else
		{
			$pidArray=$data['project_positionslist'];
			JArrayHelper::toInteger($pidArray);
			$peids=implode(",",$pidArray);
			$query="DELETE FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_position WHERE project_id=".$data['id']." AND position_id NOT IN ($peids)";
		}
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			$result=false;
		}
		for ($x=0; $x < count($data['project_positionslist']); $x++)
		{
			$query="INSERT IGNORE INTO #__".COM_SPORTSMANAGEMENT_TABLE."_project_position (project_id,position_id) VALUES ('".$data['id']."','".$data['project_positionslist'][$x]."')";
			$this->_db->setQuery($query);
			if(!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				$result=false;
			}
		}
		return $result;
	}

	/**
	 * Method to return the positions which are subpositions and are equal to a sportstype array (id,name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getSubPositions($sports_type_id=1)
	{
		$query='	SELECT	id AS value,
							name AS text,
							sports_type_id AS type,
							parent_id AS parentID
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position
					WHERE published=1 AND sports_type_id='.$sports_type_id.'
					ORDER BY parent_id ASC,name ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		//echo '<br /><pre>2~'.print_r($result,true).'~</pre><br />';
		return $result;
	}

	/**
	 * Method to return the project positions array (id,name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getProjectPositions()
	{
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState('com_joomleagueproject');
		$query='	SELECT	p.id AS value,
							p.name AS text,
							p.sports_type_id AS type,
							p.parent_id AS parentID
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS p
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS pp ON pp.position_id=p.id
					WHERE pp.project_id='.$project_id.'
					ORDER BY p.parent_id ASC,p.name ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}

	/**
	* Method to assign positions of an existing project to a copied project
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function cpCopyPositions($post)
	{
		$old_id=(int)$post['old_id'];
		$project_id=(int)$post['id'];
		//copy positions
		$query="SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_position WHERE project_id=".$old_id;
		$this->_db->setQuery($query);
		if ($results=$this->_db->loadAssocList())
		{
			foreach($results as $result)
			{
				$p_position =& $this->getTable();
				$p_position->bind($result);
				$p_position->set('id',NULL);
				$p_position->set('project_id',$project_id);
				if (!$p_position->store())
				{
					echo $this->_db->getErrorMsg();
					return false;
				}
				$newid = $this->getDbo()->insertid();
				$query = "UPDATE #__".COM_SPORTSMANAGEMENT_TABLE."_team_player " . 
							"SET project_position_id = " . $newid .
							" WHERE project_position_id = " . $result['id'];
				$this->_db->setQuery($query);
				if(!$this->_db->query())
				{
					$this->setError($this->_db->getErrorMsg());
					$result=false;
				}	
			}
		}
		return true;
	}
	
	/**
	 * return count of projectpositions
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectPositionsCount($project_id)
	{
		$query='SELECT count(*) AS count
		FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS pp
		JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on p.id = pp.project_id
		WHERE p.id='.$project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
	
}
?>