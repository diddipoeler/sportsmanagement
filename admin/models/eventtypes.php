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
 * Sportsmanagement Component Events Model
 *
 * @package	Sportsmanagement
 * @since	1.5.0a
 */
class sportsmanagementModelEventtypes extends JModelList
{
	var $_identifier = "eventtypes";
	
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('obj.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype as obj');
        // Join over the sportstype
		$query->select('st.name AS sportstype');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = obj.sports_type_id');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        
        if (self::_buildContentWhere())
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelEventtypes getListQuery query<br><pre>'.print_r($query,true).'</pre>'   ),'');
		return $query;
        
        
        
        
        
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','obj.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		if ($filter_order=='obj.ordering')
		{
			$orderby='  obj.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby='  '.$filter_order.' '.$filter_order_Dir.',obj.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_sports_type	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_sports_type','filter_sports_type','','int');
		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','obj.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_mode','search_mode','','string');
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelEventtypes _buildContentWhere filter_sports_type<br><pre>'.print_r($filter_sports_type,true).'</pre>'   ),'');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelEventtypes _buildContentWhere filter_state<br><pre>'.print_r($filter_state,true).'</pre>'   ),'');

		$search=JString::strtolower($search);
		$where=array();
		if ($filter_sports_type > 0)
		{
			$where[]='obj.sports_type_id='.$this->_db->Quote($filter_sports_type);
		}
		if ($search)
		{
			$where[]='LOWER(obj.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		if ($filter_state)
		{
			if ($filter_state == 'P')
			{
				$where[]='obj.published=1';
			}
			elseif ($filter_state == 'U')
			{
				$where[]='obj.published=0';
			}
		}
		$where=(count($where) ? '  '. implode(' AND ',$where) : '');
		return $where;
	}
    
    /**
	* Method to return a events array (id,name)
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getEvents($sports_type_id  = 0)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $query = $this->_db->getQuery(true);
        // Select some fields
		$query->select('evt.id AS value, concat(evt.name, " (" , st.name, ")") AS text,evt.name as posname,st.name AS stname');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype as evt');
        // Join over the sportstype
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = evt.sports_type_id');
        $query->where('evt.published = 1');
        if ( $sports_type_id )
        {
            $query->where('evt.sports_type_id = '.$sports_type_id);
        }
        $query->order('evt.name ASC');
                
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		foreach ($result as $position)
        {
            //$position->text = JText::_($position->text);
            $position->text = JText::_($position->posname).' ('.JText::_($position->stname).')';
        }
		return $result;
	}
    
    /**
	* Method to return the position events array (id,name)
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getEventsPosition($id)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $query='	SELECT	p.id AS value,
        p.name as posname,
						st.name AS stname,
                        concat(p.name, " (" , st.name, ")") AS text  
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS p
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pe
						ON pe.eventtype_id=p.id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = p.sports_type_id 
					WHERE pe.position_id='. $id.'
					ORDER BY pe.ordering ASC ';

		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		foreach ($result as $event)
        {
            //$event->text = JText::_($event->text);
            $event->text = JText::_($event->posname).' ('.JText::_($event->stname).')';
        }
		return $result;
	}
    
    public function getEventList()
	{
		$query='SELECT *,id AS value,name AS text FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype ORDER BY name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
    
    
    

}
?>