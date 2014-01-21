<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.application.component.model');
//require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');
jimport('joomla.application.component.modellist');

class sportsmanagementModelcurrentseasons extends JModelList
{
	var $_identifier = "currentseasons";
    
    protected function getListQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where      = $this->_buildContentWhere();
		$orderby    = $this->_buildContentOrderBy();

		$query = '	SELECT	p.*,
							st.name AS sportstype,
							s.name AS season,
							l.name AS league,
							u.name AS editor
					FROM	#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = p.sports_type_id
					LEFT JOIN #__users AS u ON u.id = p.checked_out ' .
					$where .
					$orderby;

		return $query;
	}
    
    function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

        $orderby 	= ' ORDER BY p.name ';

		return $orderby;
	}
    
    function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        
        $where = array();
		$filter_season = JComponentHelper::getParams($option)->get('current_season',0);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($filter_season,true).'</pre>'),'');
		}
        
        $filter_season = implode(",",$filter_season);
		
		if($filter_season > 0) {
			$where[] = 'p.season_id IN (' . $filter_season .')';
		}

		$where = ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}
    
    
}

?>    