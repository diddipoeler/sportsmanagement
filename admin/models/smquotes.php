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
 * Sportsmanagement Component Leagues Model
 *
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementModelsmquotes extends JModelList
{
	var $_identifier = "smquotes";
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('administrator');
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('obj.quote', 'asc');
	}
    
    
    
    
    protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $this->getState('filter.search');
        $filter_state = $this->getState('filter.state');
        $filter_catid = $this->getState('filter.category_id');
        
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes getListQuery filter_state<br><pre>'.print_r($filter_state,true).'</pre>'   ),'');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes getListQuery filter_catid<br><pre>'.print_r($filter_catid,true).'</pre>'   ),'');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes getListQuery search<br><pre>'.print_r($search,true).'</pre>'   ),'');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes _buildContentWhere request<br><pre>'.print_r($_REQUEST,true).'</pre>'   ),'');
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_rquote as obj');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        // Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = obj.catid');
        
        
//        $where = self::_buildContentWhere();
//        if ($where)
//		{
//        $query->where($where);
//        }
        if ( $search || $filter_state || $filter_catid )
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
        //$search_nation		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_nation','search_nation','','word');
        
        
        $filter_state		= $this->getState('filter.state');
        $filter_catid		= $this->getState('filter.category_id');
		//$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        $search	= $this->getState('filter.search');
        
		$search=JString::strtolower($search);
        
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes _buildContentWhere filter_state<br><pre>'.print_r($filter_state,true).'</pre>'   ),'');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes _buildContentWhere filter_catid<br><pre>'.print_r($filter_catid,true).'</pre>'   ),'');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes _buildContentWhere request<br><pre>'.print_r($_REQUEST,true).'</pre>'   ),'');
        
		$where=array();
		if ($search)
		{
			$where[]='LOWER(obj.author) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
        if (is_numeric($filter_state) )
		{
			
				$where[] = 'obj.published = '.$filter_state;
			
		}
        if (is_numeric($filter_catid) )
		{
			
				$where[] = 'obj.catid = '.$filter_catid;
			
		}

		$where=(count($where) ? ' '.implode(' AND ',$where) : ' ');
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes _buildContentWhere where<br><pre>'.print_r($where,true).'</pre>'   ),'');
        
		return $where;
	}
    
    

	
}
?>