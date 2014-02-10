<?php
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
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
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
		$query='SELECT *
				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project
				  WHERE id='.$project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
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
    
    
	
}
