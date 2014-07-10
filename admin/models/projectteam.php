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
jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModelprojectteam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelprojectteam extends JModelAdmin
{
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
    
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'projectteam', $prefix = 'sportsmanagementTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
    
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.projectteam', 'projectteam', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_team',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/projectteams');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_home', 'default', JComponentHelper::getParams($option)->get('ph_logo_small',''));
        $form->setFieldAttribute('trikot_home', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/projectteams/trikot_home');
        $form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_away', 'default', JComponentHelper::getParams($option)->get('ph_logo_small',''));
        $form->setFieldAttribute('trikot_away', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/projectteams/trikot_away');
        $form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);
        
		return $form;
	}
    
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}
    
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.projectteam.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	/**
	 * Method to save item order
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($pks = NULL, $order = NULL)
	{
		$row =& $this->getTable();
		
		// update ordering values
		for ($i=0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering=$order[$i];
				if (!$row->store())
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}
		return true;
	}
    
    /**
	 * Method to update checked project teams
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$mainframe =& JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage('saveshort pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $mainframe->enqueueMessage('saveshort post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
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
                $mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(), true).'</pre><br>','Error');
				$result = false;
			}
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
		$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $post = JRequest::get('post');
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $project_id = $post['pid'];
        $season_id = $post['season_id'];
        
        //$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' season_id<br><pre>'.print_r($season_id , true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id , true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post , true).'</pre><br>','Notice');
        
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
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
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
		$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $post = JRequest::get('post');
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        
        //$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post , true).'</pre><br>','Notice');
        
        for ($x=0; $x < count($pks); $x++)
		{
		$projectteam_id	= $pks[$x];
        $projectteam_division_id = $post['division_id' . $pks[$x]];  
        
        //$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' projectteam_id<br><pre>'.print_r($projectteam_id , true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' projectteam_division_id<br><pre>'.print_r($projectteam_division_id , true).'</pre><br>','Notice');
        
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
			$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
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
			$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
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
		$mainframe = JFactory::getApplication();
        $post = JRequest::get('post');
        $mainframe->enqueueMessage(__METHOD__.' '.__FUNCTION__.' post<br><pre>'.print_r($post , true).'</pre><br>','Notice');
        
        $project_id = $post['project_id'];
        $assign_id = $post['project_teamslist'];
        
        foreach ( $assign_id as $key => $value )
        {
        // Create and populate an object.
        $profile = new stdClass();
        $profile->project_id = $project_id;
        $profile->team_id = $value;
        // Insert the object into the user profile table.
        $result = JFactory::getDbo()->insertObject('#__sportsmanagement_project_team', $profile);
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
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       $post = JRequest::get('post');
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelprojectteam save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelprojectteam post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
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
        if ( $post['tdids'] )
        {
        $mdlTeam = JModelLegacy::getInstance("Team", "sportsmanagementModel");
        $mdlTeam->UpdateTrainigData($post);
        }
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelprojectteam save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
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
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		//$query	= $db->getQuery(true);
        //$pks = JRequest::getVar('cid', null, 'post', 'array');
        
        //$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        
        // als erstes die heimspiele
        if (count($pks))
		{
			$cids = implode(',',$pks);
            /* Ein JDatabaseQuery Objekt beziehen */
            $query = $db->getQuery(true);
            $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match')->where('projectteam1_id IN ('.$cids.')'  );
            $db->setQuery($query);
            $result = $db->query();
            if ( $result )
            {
                $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_HOME'),'');
            }
            else
            {
                $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_HOME_ERROR'),'Error');
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
            $result = $db->query();
            if ( $result )
            {
                $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_AWAY'),'');
            }
            else
            {
                $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_AWAY_ERROR'),'Error');
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
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
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
