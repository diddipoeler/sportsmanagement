<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * SportsManagement Model
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
	 * Method to update checked project referees
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$mainframe =& JFactory::getApplication();
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        
        if ( $show_debug_info )
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
				$this->setError($this->_db->getErrorMsg());
				$result=false;
			}
		}
		return $result;
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
            $mdlTeam = JModel::getInstance("Team", "sportsmanagementModel");
            $mdlTeam->DeleteTrainigData($post['delete'][0]);
        }
        if ( $post['tdids'] )
        {
        $mdlTeam = JModel::getInstance("Team", "sportsmanagementModel");
        $mdlTeam->UpdateTrainigData($post);
        }
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelprojectteam save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
    }
    
}
