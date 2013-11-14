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

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * Sportsmanagement Component Seasons Model
 *
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementModelRounds extends JModelList
{
	var $_identifier = "rounds";
    var $_project_id = 0;
	
	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info_'.$this->_identifier,0) ;
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        
        //$this->_project_id	= JRequest::getVar('pid');
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $subQuery1= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
        $subQuery3= $db->getQuery(true);
        
		// Select some fields
		$query->select('r.*');
		// From the rounds table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round as r');
        // join match
        $subQuery1->select('count(published)');
        $subQuery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery1->where('round_id=r.id and published=0');
        // join match
        $subQuery2->select('count(*)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery2->where('round_id=r.id AND cancel=0 AND (team1_result is null OR team2_result is null)');
        // join match
        $subQuery3->select('count(*)');
        $subQuery3->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery3->where('round_id=r.id');
        
        $query->select('('.$subQuery1.') AS countUnPublished');
        $query->select('('.$subQuery2.') AS countNoResults');
        $query->select('('.$subQuery3.') AS countMatches');
        
        
       
        
       
        $query->where(self::_buildContentWhere());
		$query->order(self::_buildContentOrderBy());
        
        if ( $show_debug_info )
        {
 		$mainframe->enqueueMessage(JText::_('sportsmanagementModelRounds query<br><pre>'.print_r($query,true).'</pre>'   ),'');
        $mainframe->enqueueMessage(JText::_('sportsmanagementModelRounds project<br><pre>'.print_r($this->_project_id,true).'</pre>'   ),'');
        }
        
        return $query;
	}
	
  
  
	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','r.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');

		if ( $filter_order == 'r.ordering' )
		{
			$orderby = ' r.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby = ' '.$filter_order.' '.$filter_order_Dir.',r.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order',		'filter_order',		'r.ordering',	'cmd');
		//$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
		$search=JString::strtolower($search);
		$where=array();
        $where[]=' r.project_id = '.$this->_project_id;
		if ($search)
		{
			$where[]=' LOWER(r.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		$where=(count($where) ? '  '.implode(' AND ',$where) : ' ');
		return $where;
	}
	
	/**
	 * return count of  project rounds
	 *
	 * @param int project_id
	 * @return int
	 */
	function getRoundsCount($project_id)
	{
		$query='SELECT count(*) AS count
				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round
				  WHERE project_id='.$project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
    
    
	
	

	
}
?>