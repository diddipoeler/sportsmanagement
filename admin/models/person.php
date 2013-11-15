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
	* Fetch google map data refere to
	* http://code.google.com/apis/maps/documentation/geocoding/#Geocoding	 
	*/	 	
	public function getAddressData($address)
	{

		$url = 'http://maps.google.com/maps/api/geocode/json?' . 'address='.urlencode($address) .'&sensor=false&language=de';
		$content = $this->getContent($url);
		
		$status = null;	
		if(!empty($content))
		{
			$json = new Services_JSON();
			$status = $json->decode($content);
		}

		return $status;
	}

  public function resolveLocation($address)
	{
		$mainframe = JFactory::getApplication();
    $coords = array();
		$data = $this->getAddressData($address);
		//$mainframe->enqueueMessage(JText::_('google -> '.'<pre>'.print_r($data,true).'</pre>' ),'');
		if($data){
			if($data->status == 'OK')
			{
				$this->latitude  = $data->results[0]->geometry->location->lat;
				$coords['latitude'] = $data->results[0]->geometry->location->lat; 
				$this->longitude = $data->results[0]->geometry->location->lng;
				$coords['longitude'] = $data->results[0]->geometry->location->lng;
				
				for ($a=0; $a < sizeof($data->results[0]->address_components); $a++ )
				{
        switch($data->results[0]->address_components[$a]->types[0])
        {
        case 'administrative_area_level_1':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;
        
        case 'administrative_area_level_2':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;
        
        case 'administrative_area_level_3':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;

        case 'locality':
        $coords['COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        break;
        
        case 'sublocality':
        $coords['COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        break;
                        
        }
                
        
        }
				
				
				return $coords;
			}
		}
	}  
  
  // Return content of the given url
	static public function getContent($url , $raw = false , $headerOnly = false)
	{
		if (!$url)
			return false;
		
		if (function_exists('curl_init'))
		{
			$ch			= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, true );
			
			if($raw){
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true );
			}

			$response	= curl_exec($ch);
			
			$curl_errno	= curl_errno($ch);
			$curl_error	= curl_error($ch);
			
			if ($curl_errno!=0)
			{
				$mainframe	= JFactory::getApplication();
				$err		= 'CURL error : '.$curl_errno.' '.$curl_error;
				$mainframe->enqueueMessage($err, 'error');
			}
			
			$code		= curl_getinfo( $ch , CURLINFO_HTTP_CODE );

			// For redirects, we need to handle this properly instead of using CURLOPT_FOLLOWLOCATION
			// as it doesn't work with safe_mode or openbase_dir set.
			if( $code == 301 || $code == 302 )
			{
				list( $headers , $body ) = explode( "\r\n\r\n" , $response , 2 );
				
				preg_match( "/(Location:|URI:)(.*?)\n/" , $headers , $matches );
				
				if( !empty( $matches ) && isset( $matches[2] ) )
				{
					$url	= JString::trim( $matches[2] );
					curl_setopt( $ch , CURLOPT_URL , $url );
					curl_setopt( $ch , CURLOPT_RETURNTRANSFER, 1);
					curl_setopt( $ch , CURLOPT_HEADER, true );
					$response	= curl_exec( $ch );
				}
			}
			
			
			if(!$raw){
				list( $headers , $body )	= explode( "\r\n\r\n" , $response , 2 );
			}
			
			$ret	= $raw ? $response : $body;
			$ret	= $headerOnly ? $headers : $ret;
			
			curl_close($ch);
			return $ret;
		}
	
		// CURL unavailable on this install
		return false;
	}
  
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
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.person', 'person', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
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
    
  public function getExtraFields($person_id) 
	{
  $mainframe =& JFactory::getApplication();
		$query = "SELECT ef.*,ev.fvalue as fvalue 
    FROM #__sportsmanagement_extra_fields as ef 
    LEFT JOIN #__sportsmanagement_extra_values as ev 
    ON ef.id=ev.f_id AND ev.uid=".$person_id." WHERE ef.published=1 AND ef.type='0' ORDER BY ef.ordering";
		$this->_db->setQuery($query);
    //$mainframe->enqueueMessage('ext_fields<br><pre>'.print_r($query, true).'</pre><br>','Notice');
		return $this->_db->loadObjectList();
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
	
	
  function saveExtraFields($post,$pid)
  {
  //-------extra fields-----------//
		if(isset($post['extraf']) && count($post['extraf']))
    {
			for($p=0;$p<count($post['extraf']);$p++){
				$query = "DELETE FROM #__sportsmanagement_extra_values WHERE f_id = ".$post['extra_id'][$p]." AND uid = ".$pid;
				$this->_db->setQuery($query);
				$this->_db->query();
				$query = "INSERT INTO #__sportsmanagement_extra_values(f_id,uid,fvalue) VALUES(".$post['extra_id'][$p].",".$pid.",'".$post['extraf'][$p]."')";
				$this->_db->setQuery($query);
				$this->_db->query();
			}
		}
  
  }
  
  
	
	
    /**
	 * return 
	 *
	 * @param int team_id
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
				$this->setError($this->_db->getErrorMsg());
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
					$this->setError($this->_db->getErrorMsg());
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
       $post=JRequest::get('post');
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
    }
    
    
	
}
