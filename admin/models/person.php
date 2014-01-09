<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * SportsManagement Model
 */
class sportsmanagementModelperson extends JModelAdmin
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
	public function getTable($type = 'person', $prefix = 'sportsmanagementTable', $config = array()) 
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
        
        
        
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.person', 'person', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelperson form<br><pre>'.print_r($form->getValue('person_art'),true).'</pre>'),'Notice');
        
        switch($form->getValue('person_art'))
        {
            case 1:
            break;
            case 2:
            $form->setFieldAttribute('person_id1', 'type', 'personlist');
            $form->setFieldAttribute('person_id2', 'type', 'personlist');
            break;
            
        }
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_logo_big',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/persons');
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
	
  
  
  public function getAgeGroupID($age) 
	{
  $mainframe = JFactory::getApplication();
		$query = "SELECT id
    FROM #__sportsmanagement_agegroup 
    WHERE ".$age." >= age_from and ".$age." <= age_to";
		
    //$mainframe->enqueueMessage('getAgeGroupID<br><pre>'.print_r($query, true).'</pre><br>','Notice');
		
		$this->_db->setQuery($query);
			$person_range = $this->_db->loadResult();
            return $person_range;
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.person.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	
  
  
  
	
	
    /**
	 * return 
	 *
	 * @param int person_id
	 * @return int
	 */
	function getPerson($person_id)
	{
		$query='SELECT *
				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person
				  WHERE id='.$person_id;
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}
    
    
    /**
	 * Method to update checked persons
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		// Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblPerson = & $this->getTable();
			$tblPerson->id			= $pks[$x];
			$tblPerson->firstname	= $post['firstname'.$pks[$x]];
			$tblPerson->lastname	= $post['lastname'.$pks[$x]];
			$tblPerson->nickname	= $post['nickname'.$pks[$x]];
			$tblPerson->birthday	= sportsmanagementHelper::convertDate($post['birthday'.$pks[$x]],0);
			$tblPerson->deathday	= $post['deathday'.$pks[$x]];
			$tblPerson->country		= $post['country'.$pks[$x]];
			$tblPerson->position_id	= $post['position'.$pks[$x]];
			if(!$tblPerson->store()) {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				$result=false;
			}
		}
		return $result;
	}
    
    
    /**
	 * Method to save assign persons
	 *
	 * @access	
	 * @return	
	 * @since	
	 */
    function storeAssign($post)
    {
    $option = JRequest::getCmd('option');
	$mainframe	= JFactory::getApplication();  
    $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
    $this->_team_id = $mainframe->getUserState( "$option.team_id", '0' );;
    $this->_project_team_id = $mainframe->getUserState( "$option.project_team_id", '0' );;
    $cid = $post['cid'];
          
    $mdlPerson = JModel::getInstance("person", "sportsmanagementModel");
    $mdlPersonTable = $mdlPerson->getTable();
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModelPersons storeAssign post<br><pre>'.print_r($post,true).'</pre>'),'');    
    
    switch ($post['type'])
            {
                case 0:
                $mdl = JModel::getInstance("teamplayer", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $mdlPersonTable->load($cid[$x]);    
                $mdlTable = $mdl->getTable();
                $mdlTable->projectteam_id = $this->_project_team_id;
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->published = 1;
                $mdlTable->person_id = $cid[$x];   
                if ($mdlTable->store()===false)
				{
				}
				else
				{
				}
                 
		        }
                break;
                case 1:
                $mdl = JModel::getInstance("teamstaff", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $mdlPersonTable->load($cid[$x]); 
                $mdlTable = $mdl->getTable();
                $mdlTable->projectteam_id = $this->_project_team_id;
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->published = 1;
                $mdlTable->person_id = $cid[$x];   
                if ($mdlTable->store()===false)
				{
				}
				else
				{
				}
                 
		        }
                break;
                case 2:
                $mdl = JModel::getInstance("projectreferee", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $mdlPersonTable->load($cid[$x]); 
                $mdlTable = $mdl->getTable();
                $mdlTable->project_id = $this->_project_id;
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->person_id = $cid[$x];   
                if ($mdlTable->store()===false)
				{
				}
				else
				{
				}
                 
		        }
                break;
                
            }
    
    
    
    return true;    
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
       $address_parts = array();
       $person_double = array();
       // Get a db connection.
        $db = JFactory::getDbo();
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelperson save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelperson post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       switch($data['person_art'])
        {
            case 1:
            break;
            case 2:
            if ( $data['person_id1'] && $data['person_id2'] )
            {
            $person_1 = $data['person_id1'];
            $person_2 = $data['person_id2'];
            $table = 'person';
            $row = JTable::getInstance( $table, 'sportsmanagementTable' );
            $row->load((int) $person_1);
            $person_double[] = $row->firstname.' '.$row->lastname;
            $row->load((int) $person_2);
            $person_double[] = $row->firstname.' '.$row->lastname;
            $data['lastname'] = implode(" - ",$person_double);
            $data['firstname'] = '';
            }
            break;
            
        }
       
       
       
        if (isset($data['season_ids']) && is_array($data['season_ids'])) 
		{
		  foreach( $data['season_ids'] as $key => $value )
          {
          
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('person_id','season_id');
        // Insert values.
        $values = array($data['id'],$value);
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

		if (!$db->query())
		{
    $mainframe->enqueueMessage(JText::_('sportsmanagementModelperson save<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}  
          
          }
		//$mdl = JModel::getInstance("seasonperson", "sportsmanagementModel");
		}
       
       if (!empty($data['address']))
		{
			$address_parts[] = $data['address'];
		}
		if (!empty($data['state']))
		{
			$address_parts[] = $data['state'];
		}
		if (!empty($data['location']))
		{
			if (!empty($data['zipcode']))
			{
				$address_parts[] = $data['zipcode']. ' ' .$data['location'];
			}
			else
			{
				$address_parts[] = $data['location'];
			}
		}
		if (!empty($data['country']))
		{
			$address_parts[] = Countries::getShortCountryName($data['country']);
		}
		$address = implode(', ', $address_parts);
		$coords = sportsmanagementHelper::resolveLocation($address);
		
		//$mainframe->enqueueMessage(JText::_('sportsmanagementModelperson coords -> '.'<pre>'.print_r($coords,true).'</pre>' ),'');
        
        foreach( $coords as $key => $value )
		{
        $post['extended'][$key] = $value;
        }
		
		$data['latitude'] = $coords['latitude'];
		$data['longitude'] = $coords['longitude'];
        
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelperson save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);
    
        // Proceed with the save
		return parent::save($data);   
    }
    
    
    
    
	
}
