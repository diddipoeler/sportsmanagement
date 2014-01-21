<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModelplayground
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelplayground extends JModelAdmin
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
	public function getTable($type = 'playground', $prefix = 'sportsmanagementTable', $config = array()) 
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
		$form = $this->loadForm('com_sportsmanagement.playground', 'playground', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_team',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/playgrounds');
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.playground.data', array());
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
       $address_parts = array();
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (!empty($data['address']))
		{
			$address_parts[] = $data['address'];
		}
		
		if (!empty($data['city']))
		{
			if (!empty($data['zipcode']))
			{
				$address_parts[] = $data['zipcode']. ' ' .$data['city'];
			}
			else
			{
				$address_parts[] = $data['city'];
			}
		}
		if (!empty($data['country']))
		{
			$address_parts[] = JSMCountries::getShortCountryName($data['country']);
		}
		$address = implode(', ', $address_parts);
		$coords = sportsmanagementHelper::resolveLocation($address);
		
		//$mainframe->enqueueMessage(JText::_('sportsmanagementModelclub coords -> '.'<pre>'.print_r($coords,true).'</pre>' ),'');
        
        if ( $coords )
        {
        foreach( $coords as $key => $value )
		{
        $post['extended'][$key] = $value;
        }
		
		$data['latitude'] = $coords['latitude'];
		$data['longitude'] = $coords['longitude'];
        }
        else
        {
        $address_parts = array();
        if (!empty($data['address']))
		{
		$address_parts[] = $data['address'];
		}
        if (!empty($data['location']))
		{
		$address_parts[] = $data['location'];
		}
		if (!empty($data['country']))
		{
		$address_parts[] = JSMCountries::getShortCountryName($data['country']);
		}
        $address = implode(',', $address_parts);
        $coords = sportsmanagementHelper::getOSMGeoCoords($address);
		
		//$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' coords<br><pre>'.print_r($coords,true).'</pre>' ),'');
        
        $data['latitude'] = $coords['latitude'];
		$data['longitude'] = $coords['longitude'];
        foreach( $coords as $key => $value )
		{
        $post['extended'][$key] = $value;
        }    
        }
        
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
    
    
    function getNextGames( $project = 0 )
    {
        $result = array();

        $playground = self::getPlayground();
        if ( $playground->id > 0 )
        {
            $query = "SELECT m.*, DATE_FORMAT(m.time_present, '%H:%i') time_present,
                             p.name AS project_name, tj.team_id team1, tj2.team_id team2
                      FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match AS m
                      INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team tj ON tj.id = m.projectteam1_id 
                      INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team tj2 ON tj2.id = m.projectteam2_id 
                      INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project AS p ON p.id=tj.project_id
                      INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team t ON t.id = tj.team_id
                      INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_club c ON c.id = t.club_id
                      WHERE (m.playground_id= " . (int)$playground->id . "
                          OR (tj.standard_playground = " . (int)$playground->id . " AND m.playground_id IS NULL)
                          OR (c.standard_playground = " . (int)$playground->id . " AND m.playground_id IS NULL))
                      AND m.match_date > NOW()
                      AND m.published = 1
                      AND p.published = 1";
            if ( $project )
            {
                $query .= " AND project_id= " . (int)$project;
            }
            $query .= " GROUP BY m.id ORDER BY match_date ASC";
            $this->_db->setQuery( $query );
            $result = $this->_db->loadObjectList();
        }
        return $result;
    }
    
    function getPlayground( )
    {
        if ( is_null( $this->playground ) )
        {
            $pgid = JRequest::getInt( "pgid", 0 );
            if ( $pgid > 0 )
            {
                $this->playground = $this->getTable();
                $this->playground->load( $pgid );
            }
        }
        return $this->playground;
    }
    
    	/**
	 * Method to get a single record.
	 *
	 * @param	integer	$pk	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
     /*
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			$registry = new JRegistry;
			//$registry->loadString($item->extended);
            $registry->loadJSON($item->extended);
			$item->extended = $registry->toArray();
		}

		return $item;
	}
    */   
    
    
}
