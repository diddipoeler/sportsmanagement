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
 * sportsmanagementModelTeamPlayers
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelTeamPlayers extends JModelList
{
	var $_identifier = "teamplayers";
    var $_project_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;

	function getListQuery()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		        
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $this->_team_id = JRequest::getVar('team_id');
        $this->_project_team_id = JRequest::getVar('project_team_id');
        
        if ( !$this->_team_id )
        {
            $this->_team_id	= $mainframe->getUserState( "$option.team_id", '0' );
        }
        if ( !$this->_project_team_id )
        {
            $this->_project_team_id	= $mainframe->getUserState( "$option.project_team_id", '0' );
        }
        
        // Get the WHERE and ORDER BY clauses for the query
		$where = self::_buildContentWhere();
		$orderby = self::_buildContentOrderBy();
        
        $query->select(array('ppl.firstname',
							'ppl.lastname',
							'ppl.nickname',
                            'ppl.height',
                            'ppl.weight',
                            'ppl.id',
                            'ppl.id AS person_id',
							'tp.*',
                            'tp.id as tpid',
							'u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS ppl')
        ->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp on tp.person_id = ppl.id')
        ->join('LEFT', '#__users AS u ON u.id = tp.checked_out');

        
        if ($where)
        {
            $query->where($where);
        }
        if ($orderby)
        {
            $query->order($orderby);
        }

		return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.tp_filter_order','filter_order','tp.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.tp_filter_order_Dir','filter_order_Dir','','word');
		if ($filter_order=='ppl.lastname')
		{
			$orderby=' ppl.lastname '.$filter_order_Dir;
		}
		else
		{
			$orderby=' '.$filter_order.' '.$filter_order_Dir.',ppl.lastname ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		
        //$project_id=$mainframe->getUserState($option.'project');
		//$team_id=$mainframe->getUserState($option.'project_team_id');
        
		$filter_state	= $mainframe->getUserStateFromRequest( $option . '.'.$this->_identifier.'.tp_filter_state','filter_state','','word' );
		$search			= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.tp_search','search','','string');
		$search_mode	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.tp_search_mode','search_mode','','string');
		$search=JString::strtolower($search);
		$where=array();
		$where[]='tp.projectteam_id= '.$this->_project_team_id;
		$where[]="ppl.published = '1'";
		if ($search)
		{
			if ($search_mode)
			{
				$where[]='(LOWER(ppl.lastname) LIKE '.$this->_db->Quote($search.'%') .
							'OR LOWER(ppl.firstname) LIKE '.$this->_db->Quote($search.'%') .
							'OR LOWER(ppl.nickname) LIKE '.$this->_db->Quote($search.'%').')';
			}
			else
			{
				$where[]='(LOWER(ppl.lastname) LIKE '.$this->_db->Quote('%'.$search.'%').
							'OR LOWER(ppl.firstname) LIKE '.$this->_db->Quote('%'.$search.'%') .
							'OR LOWER(ppl.nickname) LIKE '.$this->_db->Quote('%'.$search.'%').')';
			}
		}

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'tp.published = 1';
			}
			elseif ($filter_state == 'U' )
			{
				$where[] = 'tp.published = 0';
			}
		}

		$where=(count($where) ? ' '.implode(' AND ',$where) : '');
		return $where;
	}

	function getProjectTeamplayers($project_team_id)
    {
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        // Create a new query object.
		$db		= &JFactory::getDBO();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        //$mainframe->enqueueMessage('sportsmanagementModelTeamPlayers getProjectTeamplayers project_team_id<br><pre>'.print_r($project_team_id, true).'</pre><br>','Notice');
        
        // Select some fields
		$query->select('pl.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as pl');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player as tpl on tpl.person_id = pl.id');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pt on pt.id = tpl.projectteam_id');
        $query->where('pt.team_id = '.$project_team_id);
        $db->setQuery($query);
        //$db->query();
        $result = $db->loadObjectList();
        //$mainframe->enqueueMessage('sportsmanagementModelTeamPlayers getProjectTeamplayers query<br><pre>'.print_r($query, true).'</pre><br>','Notice');
                
		if (!$result)
		{
			//$this->setError($this->_db->getErrorMsg());
            $mainframe->enqueueMessage('sportsmanagementModelTeamPlayers getProjectTeamplayers message<br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
            $mainframe->enqueueMessage('sportsmanagementModelTeamPlayers getProjectTeamplayers nummer<br><pre>'.print_r($db->getErrorNum(), true).'</pre><br>','Error');
			return false;
		}
		return $result;
    }

	
	

	

	

	
	/**
	 * remove specified players from team
	 * @param $cids player ids
	 * @return int count of removed
	 */
	function remove($cids)
	{
		$count=0;
		foreach($cids as $cid)
		{
			$object=&$this->getTable('teamplayer');
			if ($object->canDelete($cid) && $object->delete($cid))
			{
				$count++;
			}
			else
			{
				$this->setError(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFFS_MODEL_ERROR_REMOVE_TEAMPLAYER',$object->getError()));
			}
		}
		return $count;
	}

}
?>