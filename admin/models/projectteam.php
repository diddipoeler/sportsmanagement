<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
        $option = JRequest::getCmd('option');
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        
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
		
			
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $post['club_id'.$pks[$x]];
$object->location = $post['location'.$pks[$x]];
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
        $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $post = JRequest::get('post');
        $pks = JRequest::getVar('cid', null, 'post', 'array');
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
        $result = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id', $object, 'id');
        
        if ( !$result )
		{
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
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
        $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $post = JRequest::get('post');
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        
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
        $query->update($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $result = $db->query();  
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
        $query->update($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $result = $db->query();  
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
        $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $post = JRequest::get('post');
        $_pro_teams_to_delete = array();
        $query = JFactory::getDbo()->getQuery(true);
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post , true).'</pre><br>','Notice');
        
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
		    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team');
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
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $post = JRequest::get('post');
       // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');
       
       //$app->enqueueMessage(JText::_('sportsmanagementModelprojectteam save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$app->enqueueMessage(JText::_('sportsmanagementModelprojectteam post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        if ( $post['delete'] )
        {
            $mdlTeam = JModelLegacy::getInstance("Team", "sportsmanagementModel");
            $mdlTeam->DeleteTrainigData($post['delete'][0]);
        }
        
        if ( $post['add_trainingData'] )
        {
            $row = JTable::getInstance( 'seasonteam', 'sportsmanagementTable' );
            $row->load( (int)$post['jform']['team_id'] );
            $mdlTeam = JModelLegacy::getInstance("Team", "sportsmanagementModel");
            $mdlTeam->addNewTrainigData($row->team_id);
        }
        
        if ( $post['tdids'] )
        {
        $mdlTeam = JModelLegacy::getInstance("Team", "sportsmanagementModel");
        $mdlTeam->UpdateTrainigData($post);
        }
        
/**
 * das mannschaftsfoto wird zusätzlich abgespeichert,
 * damit man die historischen kader sieht        
 */
        // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->id = (int)$post['jform']['team_id'];
        $object->picture = $post['jform']['picture'];
        $object->modified = $date->toSql();
	    $object->modified_by = $user->get('id');
        // Update their details in the table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__sportsmanagement_season_team_id', $object, 'id');
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelprojectteam save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
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
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		//$query	= $db->getQuery(true);
        //$pks = JRequest::getVar('cid', null, 'post', 'array');
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        
        // als erstes die heimspiele
        if (count($pks))
		{
			$cids = implode(',',$pks);
            /* Ein JDatabaseQuery Objekt beziehen */
            $query = $db->getQuery(true);
            $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match')->where('projectteam1_id IN ('.$cids.')'  );
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
            $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match')->where('projectteam2_id IN ('.$cids.')'  );
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
        $option = JRequest::getCmd('option');
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
