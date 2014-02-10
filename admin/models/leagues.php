<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');



class sportsmanagementModelLeagues extends JModelList
{
	var $_identifier = "leagues";
	
	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        $search_nation		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_nation','search_nation','','word');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*');
        $query->select('st.name AS sportstype');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league as obj');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = obj.sports_type_id');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        if ($search || $search_nation)
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
 
		//$mainframe->enqueueMessage(JText::_('leagues query<br><pre>'.print_r($query,true).'</pre>'   ),'');
        return $query;
	}

  
	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','obj.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		if ($filter_order == 'obj.ordering')
		{
			$orderby=' obj.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby=' '.$filter_order.' '.$filter_order_Dir.',obj.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order',		'filter_order',		'obj.ordering',	'cmd');
		//$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
        $search_nation		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_nation','search_nation','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
		$search=JString::strtolower($search);
		$where=array();
		if ($search)
		{
			$where[]='LOWER(obj.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
        if ( $search_nation )
		{
		  $where[] = "obj.country = '".$search_nation."'";
        }
		$where=(count($where) ? ' '.implode(' AND ',$where) : ' ');
		return $where;
	}
    
    /**
     * Method to return a leagues array (id,name)
     *
     * @access	public
     * @return	array seasons
     * @since	1.5.0a
     */
    function getLeagues()
    {
        // Get a db connection.
        $db = JFactory::getDBO();
        // Create a new query object.
        $query = $db->getQuery(true);
        $query->select(array('id', 'name'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league')
        ->order('name ASC');

        $db->setQuery($query);
        if (!$result = $db->loadObjectList())
        {
            sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
            return array();
        }
        foreach ($result as $league)
        {
            $league->name = JText::_($league->name);
        }
        return $result;
    }

	
}
?>