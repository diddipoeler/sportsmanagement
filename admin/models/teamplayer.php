<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * SportsManagement Model
 */
class sportsmanagementModelteamplayer extends JModelAdmin
{
	
    var $_project_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;
  
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
	public function getTable($type = 'teamplayer', $prefix = 'sportsmanagementTable', $config = array()) 
	{
	$config['dbo'] = sportsmanagementHelper::getDBConnection(); 
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
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$app->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.teamplayer', 'teamplayer', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/teamplayers');
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.teamplayer.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	
    /**
	 * Method to update checked teamplayers
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$app =& JFactory::getApplication();
        // Get the input
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $post = JFactory::getApplication()->input->post->getArray(array());
        
        $app->enqueueMessage('saveshort $pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $app->enqueueMessage('saveshort post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblPerson = & $this->getTable();
			$tblPerson->id = $pks[$x];
			$tblPerson->project_position_id	= $post['project_position_id'.$pks[$x]];
			$tblPerson->jerseynumber = $post['jerseynumber'.$pks[$x]];
			$tblPerson->market_value = $post['market_value'.$pks[$x]];

			if(!$tblPerson->store()) {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				$result=false;
			}
		}
		return $result;
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
	 * Method to remove teamplayer
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$app =& JFactory::getApplication();
    $app->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    /* Ein Datenbankobjekt beziehen */
    $db = JFactory::getDbo();
    /* Ein JDatabaseQuery Objekt beziehen */
    $query = $db->getQuery(true);
    
	$result = false;
    if (count($pks))
		{
			$cids = implode(',',$pks);
            $app->enqueueMessage(JText::_('delete cids<br><pre>'.print_r($cids,true).'</pre>'),'');
            // wir löschen mit join
            $query = 'DELETE mp,ms,me
            FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player as m    
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player as mp
            ON mp.teamplayer_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic as ms
            ON ms.teamplayer_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as me
            ON me.teamplayer_id = m.id
            WHERE m.id IN ('.$cids.')';
            $db->setQuery($query);
            $db->execute();
            if (!$db->execute()) 
            {
                $app->enqueueMessage(JText::_('delete getErrorMsg<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                return false; 
            }
            
        }  
    
    
    //if ( $result )
    //{        
    return parent::delete($pks);
    //}
     
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
       $option = JFactory::getApplication()->input->getCmd('option');
       $season_id = $app->getUserState( "$option.season_id");
       $post=JFactory::getApplication()->input->post->getArray(array());
       $db		= $this->getDbo();
	   $query	= $db->getQuery(true);
       $query2	= $db->getQuery(true);
        
       //$app->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$app->enqueueMessage(JText::_('sportsmanagementModelplayground post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       // update personendaten
       // Fields to update.
    $fields = array(
    $db->quoteName('injury') .'=\''.$data['injury'].'\'',
        $db->quoteName('injury_date') .'=\''.$data['injury_date'].'\'',
        $db->quoteName('injury_end') .'=\''.$data['injury_end'].'\'',
        $db->quoteName('injury_detail') .'=\''.$data['injury_detail'].'\'',
        $db->quoteName('injury_date_start') .'=\''.$data['injury_date_start'].'\'',
        $db->quoteName('injury_date_end') .'=\''.$data['injury_date_end'].'\'',
        $db->quoteName('suspension') .'=\''.$data['suspension'].'\'',
        $db->quoteName('suspension_date') .'=\''.$data['suspension_date'].'\'',
        $db->quoteName('suspension_end') .'=\''.$data['suspension_end'].'\'',
        $db->quoteName('suspension_detail') .'=\''.$data['suspension_detail'].'\'',
        $db->quoteName('susp_date_start') .'=\''.$data['susp_date_start'].'\'',
        $db->quoteName('susp_date_end') .'=\''.$data['susp_date_end'].'\'',
        $db->quoteName('away') .'=\''.$data['away'].'\'',
		$db->quoteName('away_date') .'=\''.$data['away_date'].'\'',
        $db->quoteName('away_end') .'=\''.$data['away_end'].'\'',
        $db->quoteName('away_detail') .'=\''.$data['away_detail'].'\'',
        $db->quoteName('away_date_start') .'=\''.$data['away_date_start'].'\'',
        $db->quoteName('away_date_end') .'=\''.$data['away_date_end'].'\''
        );
     // Conditions for which records should be updated.
    $conditions = array(
    $db->quoteName('id') .'='. $data['person_id']
    );
     $query->update($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person'))->set($fields)->where($conditions);
     $db->setQuery($query);   
 
  
 if (!$db->execute())
		{
		    $app->enqueueMessage(JText::_('sportsmanagementModelteamplayer save personendaten<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}
        
// update personendaten pro saison
       // Fields to update.
    unset($fields);
    unset($conditions);   
    $fields = array(
    $db->quoteName('picture') .'=\''.$data['picture'].'\''
        );
     // Conditions for which records should be updated.
    $conditions = array(
    $db->quoteName('person_id') .'='. $data['person_id'],
    $db->quoteName('season_id') .'='. $season_id
    );
     $query2->update($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id'))->set($fields)->where($conditions);
     $db->setQuery($query2);   
 
 if (!$db->execute())
		{
		    $app->enqueueMessage(JText::_('sportsmanagementModelteamplayer save person season <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}
                
        
 
        
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        
        // Proceed with the save
		return parent::save($data);   
    }
    
    
   
}
