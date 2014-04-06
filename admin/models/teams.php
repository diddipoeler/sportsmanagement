<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
	
    /**
     * sportsmanagementModelTeams::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        't.name',
                        //'c.name',
                        't.website',
                        't.middle_name',
                        't.short_name',
                        't.info',
                        //'st.name',
                        't.picture',
                        't.id',
                        't.ordering',
                        't.checked_out',
                        't.checked_out_time'
                        );
                parent::__construct($config);
        }
        
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

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('t.name', 'asc');
	}
    
	/**
	 * sportsmanagementModelTeams::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $this->getState('filter.search');

        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select(implode(",",$this->filter_fields));
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
        
        
        if ($search)
		{
        $query->where('LOWER(t.name) LIKE '.$this->_db->Quote('%'.$search.'%'));
        }
        if ($cid = JRequest::getvar('club_id', 0, 'GET', 'INT')) 
                {
                    $mainframe->setUserState( "$option.club_id", $cid); 
                    $query->where('club_id ='. $cid);
                }
        

        
        $query->order($db->escape($this->getState('list.ordering', 't.name')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
        
if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }

		return $query;
        
        
        
	}
    
    /**
     * sportsmanagementModelTeams::getTeamListSelect()
     * 
     * @return
     */
    public function getTeamListSelect()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
        $starttime = microtime(); 
        
        // Select some fields
		$query->select('id,id AS value,name,club_id,short_name, middle_name,info');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
        $query->order('name');
        
//		$query="SELECT id,id AS value,name,club_id,short_name, middle_name,info FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team ORDER BY name";

		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		if ($results = $db->loadObjectList())
		{
			foreach ($results AS $team)
			{
				$team->text=$team->name.' - ('.$team->info.')';
			}
			return $results;
		}
		return false;
	}
    
    
    /**
     * sportsmanagementModelTeams::getTeams()
     * 
     * @param mixed $playground_id
     * @return
     */
    function getTeams($playground_id)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
//        $query2	= $db->getQuery(true);
//        $query3	= $db->getQuery(true);

        $teams = array();

        //$playground = self::getPlayground();
        if ( $playground_id > 0 )
        {
        // Select some fields
		$query->select('pt.id, st.team_id, pt.project_id');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pt');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt.team_id ');
        $query->where('pt.standard_playground = '.(int)$playground->id);
        
        $starttime = microtime(); 

            $db->setQuery( $query );
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $rows = $db->loadObjectList();
			
            foreach ( $rows as $row )
            {
                $teams[$row->id]->project_team[] = $row;
                // Select some fields
                $query->clear();
		$query->select('name, short_name, notes');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
        $query->where('id='.(int)$row->team_id);

$starttime = microtime(); 
                $db->setQuery( $query );
                if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
                $teams[ $row->id ]->teaminfo[] = $db->loadObjectList();
                
                // Select some fields
                $query->clear();
		$query->select('name');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
        $query->where('id='.$row->project_id);
$starttime = microtime(); 
                $db->setQuery( $query );
                if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
            	$teams[ $row->id ]->project = $db->loadResult();
            }
        }
        return $teams;
    }
    
    
    /**
     * sportsmanagementModelTeams::getTeamsFromMatches()
     * 
     * @param mixed $games
     * @return
     */
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
