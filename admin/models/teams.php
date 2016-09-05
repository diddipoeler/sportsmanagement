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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
class sportsmanagementModelTeams extends JSMModelList
{
	var $_identifier = "teams";
    //static $cfg_which_database = 0;
	
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
                        't.sports_type_id',
                        't.website',
                        't.middle_name',
                        't.short_name',
                        't.info',
                        't.alias',
                        't.picture',
                        't.id',
                        't.ordering',
                        't.checked_out',
                        't.checked_out_time',
                        't.agegroup_id',
                        'ag.name'
                        );
                parent::__construct($config);
                
                $this->app = JFactory::getApplication();
		$this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');
        
        $this->club_id = $this->jinput->post->get('club_id');
        if ( empty($this->club_id)  )
        {
        $this->club_id = $this->jinput->get->get('club_id');
        }
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jinput<br><pre>'.print_r($this->jinput ,true).'</pre>'),'');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($this->club_id,true).'</pre>'),'');
        
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
                
        $this->user	= JFactory::getUser();     
        $this->jsmdb = $this->getDbo();
        $this->query = $this->jsmdb->getQuery(true);
                
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
       
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context ->'.$this->context.''),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.sports_type', 'filter_sports_type', '');
		$this->setState('filter.sports_type', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        
        $value = JRequest::getUInt('limitstart', 0);
		$this->setState('list.start', $value);

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
        // Select some fields
        $this->query->select('t.*');
        $this->query->select('st.name AS sportstype');
        $this->query->select('ag.name AS agename');
        // From table
		$this->query->from('#__sportsmanagement_team AS t');
        $this->query->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = t.sports_type_id');
        $this->query->join('LEFT', '#__sportsmanagement_agegroup as ag ON ag.id = t.agegroup_id');
        // Join over the clubs
		$this->query->select('c.name As clubname,c.country');
		$this->query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id');
        // Join over the users for the checked out user.
		$this->query->select('uc.name AS editor');
		$this->query->join('LEFT', '#__users AS uc ON uc.id = t.checked_out');
        
        
        if ($this->getState('filter.search'))
		{
        $this->query->where('LOWER(t.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        
        if ($this->getState('filter.search_nation'))
		{
        $this->query->where('c.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').''));
        }
        if ($this->getState('filter.sports_type'))
		{
        $this->query->where('t.sports_type_id = ' . $this->getState('filter.sports_type') );
        }        
        if ( $this->club_id ) 
                {
                    $this->app->setUserState( "$this->option.club_id", $this->club_id); 
                    $this->query->where('club_id ='. $this->club_id);
                }
                else
                {
                $this->app->setUserState( "$this->option.club_id", '0');     
                }
        

        
        $this->query->order($this->jsmdb->escape($this->getState('list.ordering', 't.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
        
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($this->query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }

		return $this->query;
        
        
        
	}
    
    /**
     * sportsmanagementModelTeams::getTeamListSelect()
     * 
     * @return
     */
    public function getTeamListSelect()
	{
	   
        $starttime = microtime(); 
        $results = array();
        // Select some fields
		$this->query->select('id,id AS value,name,club_id,short_name, middle_name,info');
        // From table
		$this->query->from('#__sportsmanagement_team');
        $this->query->order('name');

		$this->jsmdb->setQuery($this->query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		if ($results = $this->jsmdb->loadObjectList())
		{
			foreach ($results AS $team)
			{
				$team->text=$team->name.' - ('.$team->info.')';
			}
			return $results;
		}
		//return false;
        return $results;
	}
    
    
    /**
     * sportsmanagementModelTeams::getTeams()
     * 
     * @param mixed $playground_id
     * @return
     */
    public static function getTeams($playground_id)
    {
        
        $teams = array();

        //$playground = self::getPlayground();
        if ( $playground_id > 0 )
        {
        $this->query->clear();
        // Select some fields
		$this->query->select('pt.id, st.team_id, pt.project_id');
        // From table
		$this->query->from('#__sportsmanagement_project_team as pt');
        $this->query->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $this->query->where('pt.standard_playground = '.(int)$playground_id);
        
        $starttime = microtime(); 

            $this->jsmdb->setQuery( $this->query );
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $rows = $this->jsmdb->loadObjectList();
			
            foreach ( $rows as $row )
            {
                $teams[$row->id]->project_team[] = $row;
                // Select some fields
                $this->query->clear();
		$this->query->select('name, short_name, notes');
        // From table
		$this->query->from('#__sportsmanagement_team');
        $this->query->where('id='.(int)$row->team_id);

$starttime = microtime(); 
                $this->jsmdb->setQuery( $this->query );
                if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
                $teams[ $row->id ]->teaminfo[] = $this->jsmdb->loadObjectList();
                
                // Select some fields
                $this->query->clear();
		$this->query->select('name');
        // From table
		$this->query->from('#__sportsmanagement_project');
        $this->query->where('id='.$row->project_id);
$starttime = microtime(); 
                $this->jsmdb->setQuery( $this->query );
                if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
            	$teams[ $row->id ]->project = $this->jsmdb->loadResult();
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
    public static function getTeamsFromMatches( & $games )
    {
        
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
		$this->query->select('t.id, t.name');
        // From table
		$this->query->from('#__sportsmanagement_team AS t');
        $this->query->where('t.id IN ('.$listTeamId.')');

        $this->jsmdb->setQuery( $this->query );
        $result = $this->jsmdb->loadObjectList();

        foreach ( $result as $r )
        {
            $teams[$r->id] = $r;
        }

        return $teams;
    }
    
}
?>
