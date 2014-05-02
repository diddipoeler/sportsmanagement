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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModelProject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProject extends JModelAdmin
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
	public function getTable($type = 'project', $prefix = 'sportsmanagementTable', $config = array()) 
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
        // Create a new query object.
        $db = JFactory::getDBO();
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');

        //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
        
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__FUNCTION__. '<br><pre>'.print_r($form,true).'</pre>'),'Notice');
        $sports_type_id = $form->getValue('sports_type_id');
        
        $query = $db->getQuery(true);
        // select some fields
		$query->select('name');
		// from table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type');
        // where
        $query->where('id = '.(int) $sports_type_id);
        $db->setQuery($query);
        $result = $db->loadResult();
        
        switch ($result)
        {
            case 'COM_SPORTSMANAGEMENT_ST_TENNIS';
            break;
            default:
            $form->setFieldAttribute('use_tie_break', 'type', 'hidden');
            $form->setFieldAttribute('tennis_single_matches', 'type', 'hidden');
            $form->setFieldAttribute('tennis_double_matches', 'type', 'hidden');
            break;
        }
        
        //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__FUNCTION__. ' sports_type_id<br><pre>'.print_r($result,true).'</pre>'),'Notice');
        
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_logo_big',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/projects');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.project.data', array());
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
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
					return false;
				}
			}
		}
		return true;
	}
    
    /**
	 * return 
	 *
	 * @param int team_id
	 * @return int
	 */
	function getProjectTeam($projectteam_id)
	{
		$query='SELECT t.*
				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t
                  INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pt
                  on t.id = pt.team_id 
				  WHERE pt.id='.$projectteam_id;
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}
    
    /**
	 * return 
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProject($project_id)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);
        $query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
        $query->where('id = ' . $project_id);
        
//		$query='SELECT *
//				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project
//				  WHERE id='.$project_id;
		
        $db->setQuery($query);
		return $db->loadObject();
	}

    
    /**
	 * Method to return the project teams array (id, name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
/*
	function getProjectTeams($project_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		//$project_id = $mainframe->getUserState($option . 'project');

		$query = '	SELECT	pt.id AS value,
							t.name AS text,
							t.short_name AS short_name,
							t.notes

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id
					WHERE pt.project_id = ' . $project_id . '
					ORDER BY text ASC ';

		$this->_db->setQuery($query);

		if (!$result = $this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			return $result;
		}
	}
*/
    
    /**
	 * @param int iDivisionId
	 * return project teams as options
	 * @return unknown_type
	 */
	function getProjectTeamsOptions($project_id,$iDivisionId=0)
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
        $db	= $this->getDbo();
		$query = $db->getQuery(true);
        $this->project_art_id	= $mainframe->getUserState( "$option.project_art_id", '0' );
        
		//$project_id = $mainframe->getUserState($option . 'project');
        
        if ( $this->project_art_id == 3 )
        {
            // Select some fields
		    $query->select("pt.id AS value,concat(t.lastname,' - ',t.firstname,'' ) AS text");
            // From table
		    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS t');
            $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS st on st.person_id = t.id');
            $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        }
        else
        {
            // Select some fields
		    $query->select('pt.id AS value');
            $query->select('CASE WHEN CHAR_LENGTH(t.name) < 25 THEN t.name ELSE t.middle_name END AS text');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
            $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = t.id');
            $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        }
/*
		$query = ' SELECT	pt.id AS value, '
		. ' CASE WHEN CHAR_LENGTH(t.name) < 25 THEN t.name ELSE t.middle_name END AS text '
		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id '
		. ' WHERE pt.project_id = ' . $project_id;
*/		
        $query->where('pt.project_id = ' . $project_id);
        
        if( $iDivisionId > 0 )  
        {
            $query->where('pt.division_id = ' . $iDivisionId);
		}
		
        $query->order('text ASC'); 

		$db->setQuery($query);
		$result = $db->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, $db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}
    
    
    /**
	 * Method to remove projects
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$mainframe =& JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $success = $this->deleteProjectsData($pks);  
    
    if ( $success )
    {
    $mainframe->setUserState( "$option.pid", 0 );     
    return parent::delete($pks);
    }
         
   }
   
    /**
	 * Method to remove all project datas
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function deleteProjectsData($pk=array())
	{
	$mainframe =& JFactory::getApplication();
    /* Ein Datenbankobjekt beziehen */
    $db = JFactory::getDbo();
    /* Ein JDatabaseQuery Objekt beziehen */
    $query = $db->getQuery(true);
    
	$result = false;
    if (count($pk))
		{
			//JArrayHelper::toInteger($cid);
			$cids = implode(',',$pk);
            // wir l�schen mit join
            
            $query = 'DELETE QUICK t1,t2,t3,t4,t5,t6,t7,t8,t9,t10,t11,t12,t13,t14,t15,t16,t17,t18,t19,t20
            FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project as p
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round as t1
            ON t1.project_id = p.id   
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division as t2
            ON t2.project_id = p.id   
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as t3
            ON t3.round_id = t1.id  
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary as t4
            ON t4.match_id = t3.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic as t5
            ON t5.match_id = t3.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic as t6
            ON t6.match_id = t3.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff as t7
            ON t7.match_id = t3.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as t8
            ON t8.match_id = t3.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee as t9
            ON t9.match_id = t3.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player as t10
            ON t10.match_id = t3.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position as t11
            ON t11.project_id = p.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee as t12
            ON t12.project_id = p.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as t13
            ON t13.project_id = p.id    
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player as t14
            ON t14.projectteam_id = t13.id    
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff as t15
            ON t15.projectteam_id = t13.id    
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata as t16
            ON t16.project_id = p.id    
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config as t17
            ON t17.project_id = p.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_treeto as t18
            ON t18.project_id = p.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_treeto_match as t19
            ON t19.match_id = t3.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_treeto_node as t20
            ON t20.treeto_id = t18.id
            WHERE p.id IN ('.$cids.')';
            
            
            /*
            $query = 'DELETE t1,t2,t3,t4,t5,t6,t7,t8,t9,t10,t11,t12,t13,t14,t15,t16,t17,t18,t19,t20
            FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project as p
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round as t1
            ON t1.project_id = p.id   
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division as t2
            ON t2.project_id = p.id   
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as t3
            ON t3.round_id = t1.id  
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary as t4
            ON t4.match_id = t3.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic as t5
            ON t5.match_id = t3.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic as t6
            ON t6.match_id = t3.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff as t7
            ON t7.match_id = t3.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as t8
            ON t8.match_id = t3.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee as t9
            ON t9.match_id = t3.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player as t10
            ON t10.match_id = t3.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position as t11
            ON t11.project_id = p.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee as t12
            ON t12.project_id = p.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as t13
            ON t13.project_id = p.id    
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player as t14
            ON t14.projectteam_id = t13.id    
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff as t15
            ON t15.projectteam_id = t13.id    
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata as t16
            ON t16.project_id = p.id    
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config as t17
            ON t17.project_id = p.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_treeto as t18
            ON t18.project_id = p.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_treeto_match as t19
            ON t19.match_id = t3.id
            JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_treeto_node as t20
            ON t20.treeto_id = t18.id
            WHERE p.id IN ('.$cids.')';
            */
            $db->setQuery($query);
            $db->query();
            if (!$db->query()) 
            {
                $mainframe->enqueueMessage(JText::_('deleteProjectsData getErrorMsg<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                return false; 
            }
            
        }    
   return true;     
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
       $address_parts = array();
       $post = JRequest::get('post');
       
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
       
       $data['sports_type_id'] = $data['request']['sports_type_id'];
       $data['agegroup_id'] = $data['request']['agegroup_id'];
       
       $data['fav_team'] = implode(',',$post['jform']['fav_team']);
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        if (isset($post['extendeduser']) && is_array($post['extendeduser'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extendeduser']);
			$data['extendeduser'] = (string)$parameter;
		}
        
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
       
       //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);
        
       // Proceed with the save
		return parent::save($data);   
    }    
    
	
}
