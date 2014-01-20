<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');



/**
 * sportsmanagementModelTeams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelTeams extends JModelList
{
	var $_identifier = "teams";
	
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        //$mainframe->enqueueMessage(JText::_('teams getListQuery search<br><pre>'.print_r($search,true).'</pre>'   ),'');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('t.*');
        $query->select('st.name AS sportstype');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = t.sports_type_id');
        // Join over the clubs
		$query->select('c.name As clubname');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ON c.id = t.club_id');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = t.checked_out');
        
        
        if (self::_buildContentWhere())
		{
        $query->where(self::_buildContentWhere());
        }
        
		$query->order(self::_buildContentOrderBy());
        
        //$mainframe->enqueueMessage(JText::_('teams query<br><pre>'.print_r($query,true).'</pre>'   ),'');
		return $query;
        
        
        
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_order','filter_order','t.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');

		if ($filter_order == 't.ordering'){
			$orderby 	= '  t.ordering '.$filter_order_Dir;
		} else {
			$orderby 	= '  '.$filter_order.' '.$filter_order_Dir.' , t.ordering ';
		}

		return $orderby;
	}
	
    function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$filter_state		= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_order','filter_order','t.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		$search			= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.search_mode','search_mode','','string');
		$search			= JString::strtolower( $search );

		$where = array();

		if ($search) {
			if($search_mode)
				$where[] = 'LOWER(t.name) LIKE '.$this->_db->Quote($search.'%');
			else
				$where[] = 'LOWER(t.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}

                if ($cid    =   JRequest::getvar('club_id', 0, 'GET', 'INT')) 
                {
                    $mainframe->setUserState( "$option.club_id", $cid); 
                    $where[] = 'club_id ='. $cid;
                }

                $where 		= ( count( $where ) ? '  '. implode( ' AND ', $where ) : '' );
		return $where;
	}
    
    public function getTeamListSelect()
	{
		$query="SELECT id,id AS value,name,club_id,short_name, middle_name,info FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team ORDER BY name";
		$this->_db->setQuery($query);
		if ($results=$this->_db->loadObjectList())
		{
			foreach ($results AS $team)
			{
				$team->text=$team->name.' - ('.$team->info.')';
			}
			return $results;
		}
		return false;
	}
    
    
    function getTeams($playground_id)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
        $query2	= $db->getQuery(true);
        $query3	= $db->getQuery(true);
        
        $teams = array();

        //$playground = self::getPlayground();
        if ( $playground_id > 0 )
        {
        // Select some fields
		$query->select('id, team_id, project_id');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team');
        $query->where('standard_playground = '.(int)$playground->id);
        
/**
 *             $query = "SELECT id, team_id, project_id
 *                       FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_team
 *                       WHERE standard_playground = ".(int)$playground->id;
 */
            $db->setQuery( $query );
            $rows = $db->loadObjectList();
			
            foreach ( $rows as $row )
            {
                $teams[$row->id]->project_team[] = $row;
                // Select some fields
		$query2->select('name, short_name, notes');
        // From table
		$query2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
        $query2->where('id='.(int)$row->team_id);

/**
 *                 $query = "SELECT name, short_name, notes
 *                           FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team
 *                           WHERE id=".(int)$row->team_id;
 */
                $db->setQuery( $query2 );
                $teams[ $row->id ]->teaminfo[] = $db->loadObjectList();
                
                // Select some fields
		$query3->select('name');
        // From table
		$query3->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
        $query3->where('id='.$row->project_id);

/**
 *                 $query= "SELECT name
 *                          FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project
 *                          WHERE id=".(int)$row->project_id;
 */
                $db->setQuery( $query3 );
            	$teams[ $row->id ]->project = $db->loadResult();
            }
        }
        return $teams;
    }
    
    
    public function getTeamsFromMatches( & $games )
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
        
        $teams = Array();

        if ( !count( $games ) )
        {
            return $teams;
        }

        foreach ( $games as $m )
        {
            $teamsId[] = $m->team1;
            $teamsId[] = $m->team2;
        }
        $listTeamId = implode( ",", array_unique( $teamsId ) );
        
        // Select some fields
		$query->select('t.id, t.name');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
        $query->where('t.id IN ('.$listTeamId.')');

/**
 *         $query = "SELECT t.id, t.name
 *                  FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team t
 *                  WHERE t.id IN (".$listTeamId.")";
 */
        $db->setQuery( $query );
        $result = $db->loadObjectList();

        foreach ( $result as $r )
        {
            $teams[$r->id] = $r;
        }

        return $teams;
    }
    
}
?>
