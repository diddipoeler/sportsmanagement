<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      projectteam.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage projectteam
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
//jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModelprojectteam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelprojectteam extends JSMModelAdmin
{
    
    /**
	 * Method to update checked project teams
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$app =& JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $post = JFactory::getApplication()->input->post->getArray(array());

$project_id = $post['pid'];
//$app->enqueueMessage('project_id<br><pre>'.print_r($project_id, true).'</pre><br>','Notice');
$this->jsmquery->clear();
$this->jsmquery->select('l.associations');
$this->jsmquery->from('#__sportsmanagement_league as l');
$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p on p.league_id = l.id');
$this->jsmquery->where('p.id = '.$project_id);
$this->jsmdb->setQuery($this->jsmquery);
$associations = $this->jsmdb->loadResult();
//$app->enqueueMessage('associations <br><pre>'.print_r($associations , true).'</pre><br>','Notice');
		
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage('saveshort pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $app->enqueueMessage('saveshort post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        }
        
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblProjectteam = & $this->getTable();
			$tblProjectteam->id	= $pks[$x];
            $tblProjectteam->division_id = $post['division_id' . $pks[$x]];
			$tblProjectteam->start_points = $post['start_points' .$pks[$x]];
            $tblProjectteam->penalty_points = $post['penalty_points' .$pks[$x]];
            
            $tblProjectteam->is_in_score = $post['is_in_score' .$pks[$x]];
            $tblProjectteam->use_finally = $post['use_finally' .$pks[$x]];
            
			$tblProjectteam->points_finally = $post['points_finally' .$pks[$x]];
			$tblProjectteam->neg_points_finally = $post['neg_points_finally' . $pks[$x]];
            $tblProjectteam->penalty_points = $post['penalty_points' . $pks[$x]];
			$tblProjectteam->matches_finally = $post['matches_finally' . $pks[$x]];
			$tblProjectteam->won_finally = $post['won_finally' . $pks[$x]];
			$tblProjectteam->draws_finally = $post['draws_finally' . $pks[$x]];
			$tblProjectteam->lost_finally = $post['lost_finally' . $pks[$x]];
			$tblProjectteam->homegoals_finally = $post['homegoals_finally' .$pks[$x]];
			$tblProjectteam->guestgoals_finally = $post['guestgoals_finally' . $pks[$x]];
			$tblProjectteam->diffgoals_finally = $post['diffgoals_finally' . $pks[$x]];

			if(!$tblProjectteam->store()) {
				//$this->setError($this->_db->getErrorMsg());
                $app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(), true).'</pre><br>','Error');
				$result = false;
			}
		

/**
 * hier werden noch die vereine aktualisiert 
 * wenn schon ein verband/kreis vorhanden ist, kein update
 */
$clubrow = JTable::getInstance('club', 'sportsmanagementTable', array()); 
$clubrow->load($post['club_id'.$pks[$x]]);
if ( $clubrow->associations )
{
$associations = '';
}
			
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $post['club_id'.$pks[$x]];
$object->location = $post['location'.$pks[$x]];
$object->founded_year = $post['founded_year'.$pks[$x]];
$object->unique_id = $post['unique_id'.$pks[$x]];
if ( $associations )
{
$object->associations = $associations;
}
			
// Update their details in the users table using id as the primary key.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_club', $object, 'id');
			
			
		}
		return $result;
	}
    
    
    
    /**
     * sportsmanagementModelprojectteam::setseasonid()
     * 
     * @return void
     */
    function setseasonid()
    {
        $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $post = JFactory::getApplication()->input->post->getArray(array());
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $project_id = $post['pid'];
        $season_id = $post['season_id'];
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' season_id<br><pre>'.print_r($season_id , true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id , true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post , true).'</pre><br>','Notice');
        
        for ($x=0; $x < count($pks); $x++)
		{
		$projectteam_id	= $pks[$x];
        
        $proTeam = JTable::getInstance( 'Projectteam', 'sportsmanagementTable' );
		$proTeam->load( $projectteam_id );
        
        // Create and populate an object.
        $object = new stdClass();
        $object->id = $proTeam->team_id;
        $object->season_id = $season_id;
        // Update their details in the table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__sportsmanagement_season_team_id', $object, 'id');
        
        if ( !$result )
		{
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');
		}
        
        }
        
    }
    
    
    /**
     * sportsmanagementModelprojectteam::setusetable()
     * 
     * @param integer $setzer
     * @return void
     */
    function setusetable($setzer=0)
    {
    $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
    
    for ($x=0; $x < count($pks); $x++)
		{
		$projectteam_id	= $pks[$x];
                       
        // Create and populate an object.
        $object = new stdClass();
        $object->id = $projectteam_id;
        $object->is_in_score = $setzer;
        // Update their details in the table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__sportsmanagement_project_team', $object, 'id');
        
        if ( !$result )
		{
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');
		}
        
        }    
        
    }
    
    
    /**
     * sportsmanagementModelprojectteam::setusetablepoints()
     * 
     * @param integer $setzer
     * @return void
     */
    function setusetablepoints($setzer=0)
    {
    $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
    
    for ($x=0; $x < count($pks); $x++)
		{
		$projectteam_id	= $pks[$x];
                       
        // Create and populate an object.
        $object = new stdClass();
        $object->id = $projectteam_id;
        $object->use_finally = $setzer;
        // Update their details in the table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__sportsmanagement_project_team', $object, 'id');
        
        if ( !$result )
		{
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');
		}
        
        }        
        
        
    }
    
    
    
        
    /**
     * sportsmanagementModelprojectteam::matchgroups()
     * 
     * @return void
     */
    function matchgroups()
    {
        $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $post = JFactory::getApplication()->input->post->getArray(array());
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post , true).'</pre><br>','Notice');
        
        for ($x=0; $x < count($pks); $x++)
		{
		$projectteam_id	= $pks[$x];
        $projectteam_division_id = $post['division_id' . $pks[$x]];  
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' projectteam_id<br><pre>'.print_r($projectteam_id , true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' projectteam_division_id<br><pre>'.print_r($projectteam_division_id , true).'</pre><br>','Notice');
        
        // Fields to update.
        $query = $db->getQuery(true);
        $query->clear();
        $fields = array(
        $db->quoteName('division_id') . '=' . $projectteam_division_id
        );
        // Conditions for which records should be updated.
        $conditions = array(
        $db->quoteName('projectteam1_id') . '=' . $projectteam_id
        );
        $query->update($db->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $result = $db->execute();  
        if ( !$result )
		{
			$app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
		}
        
        $query->clear();
        $fields = array(
        $db->quoteName('division_id') . '=' . $projectteam_division_id
        );
        // Conditions for which records should be updated.
        $conditions = array(
        $db->quoteName('projectteam2_id') . '=' . $projectteam_id
        );
        $query->update($db->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $result = $db->execute();  
        if ( !$result )
		{
			$app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
		}
        
        
        
        
        }  
        
    }    
    /**
     * sportsmanagementModelprojectteam::storeAssign()
     * 
     * @param mixed $post
     * @return void
     */
    function storeAssign($post)
    {
        $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        //$post = JFactory::getApplication()->input->post->getArray(array());
        $post = $jinput->post->getArray();
        $_pro_teams_to_delete = array();
        $query = JFactory::getDbo()->getQuery(true);
        if ( JComponentHelper::getParams($option)->get('show_debug_info_backend') )
        {
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post , true).'</pre><br>','Notice');
        }
        $project_id = $post['project_id'];
        $assign_id = $post['project_teamslist'];
        $delete_team = $post['teamslist'];
        

        if ( $delete_team )
        {
        $mdlProjectTeams = JModelLegacy::getInstance("projectteams", "sportsmanagementModel");
        $project_teams = $mdlProjectTeams->getAllProjectTeams($project_id,0,$delete_team);
        foreach( $project_teams as $row )
          {
          $_pro_teams_to_delete[] = $row->projectteamid;  
          }
         } 
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.' _pro_teams_to_delete<br><pre>'.print_r($_pro_teams_to_delete , true).'</pre><br>','Notice');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.' delete_team<br><pre>'.print_r($delete_team , true).'</pre><br>','Notice');
        
        foreach ( $assign_id as $key => $value )
        {
            $query->clear();
            $query->select('id');		
		    $query->from('#__sportsmanagement_project_team');
		    $query->where('team_id = '.$value);
            $query->where('project_id = '.$project_id);
		    JFactory::getDbo()->setQuery($query);
		    $team_id = JFactory::getDbo()->loadResult();
            if ( !$team_id )
            {
        // Create and populate an object.
        $profile = new stdClass();
        $profile->project_id = $project_id;
        $profile->team_id = $value;
        // Insert the object into the user profile table.
        $result = JFactory::getDbo()->insertObject('#__sportsmanagement_project_team', $profile);
        }
        
        }
        
        if ( $_pro_teams_to_delete )
        {
            self::delete($_pro_teams_to_delete);
        }
        
    }
    

    
    /**
     * sportsmanagementModelprojectteam::delete()
     * 
     * @param mixed $pks
     * @return void
     */
    public function delete(&$pks)
    {
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
		$db		= JFactory::getDbo();
		//$query	= $db->getQuery(true);
        //$pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        
        // als erstes die heimspiele
        if (count($pks))
		{
			$cids = implode(',',$pks);
            /* Ein JDatabaseQuery Objekt beziehen */
            $query = $db->getQuery(true);
            $query->delete()->from('#__sportsmanagement_match')->where('projectteam1_id IN ('.$cids.')'  );
            $db->setQuery($query);
            $result = sportsmanagementModeldatabasetool::runJoomlaQuery();
            if ( $result )
            {
                $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_HOME'),'');
            }
            else
            {
                $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_HOME_ERROR'),'Error');
            }
        }    
        // dann die auswärtsspiele
        if (count($pks))
		{
			$cids = implode(',',$pks);
            /* Ein JDatabaseQuery Objekt beziehen */
            $query = $db->getQuery(true);
            $query->delete()->from('#__sportsmanagement_match')->where('projectteam2_id IN ('.$cids.')'  );
            $db->setQuery($query);
            $result = sportsmanagementModeldatabasetool::runJoomlaQuery();
            if ( $result )
            {
                $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_AWAY'),'');
            }
            else
            {
                $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_AWAY_ERROR'),'Error');
            }
        }    
        
        // dann den eigentlichen eintrag an joomla standard
        return parent::delete($pks);
        
    }
    /**
	 * return 
	 *
	 * @param int team_id
	 * @return int
	 */
	function getProjectTeam($team_id)
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
		$db	= JFactory::getDbo();
		$query	= $db->getQuery(true);
        // Select some fields
		$query->select('t.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = t.id');
        $query->where('st.team_id = '.$team_id);

		$db->setQuery($query);
		return $db->loadObject();
	}
    
}
