<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * SportsManagement Model
 */
class sportsmanagementModeltemplate extends JModelAdmin
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
	public function getTable($type = 'template', $prefix = 'sportsmanagementTable', $config = array()) 
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
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.template', 'template', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.template.data', array());
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
       
       $mainframe->enqueueMessage(JText::_('sportsmanagementModeltemplate save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       $mainframe->enqueueMessage(JText::_('sportsmanagementModeltemplate post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        $mainframe->enqueueMessage(JText::_('sportsmanagementModeltemplate save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
    }
    
    function getAllTemplatesList($project_id,$master_id)
	{
		
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.
		$db		= $this->getDbo();
		$query1	= $db->getQuery(true);
        $query2	= $db->getQuery(true);
        $query3	= $db->getQuery(true);
        
        
        // Select some fields
		$query1->select('template');
        // From table
		$query1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config');
        $query1->where('project_id='.$project_id);
        $db->setQuery($query1);
		$current = $db->loadResultArray();
        $current = implode("','",$current);
        // Select some fields
		$query2->select('id as value, title as text');
        // From table
		$query2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config');
        $query2->where('project_id='.$master_id.' and template NOT IN (\''.$current.'\') ');
        $db->setQuery($query2);
        $result1 = $db->loadObjectList();
        // Select some fields
		$query3->select('id as value, title as text');
        // From table
		$query3->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config');
        $query3->where('project_id='.$project_id);
        $query3->order('title');
        $db->setQuery($query3);
		$result2 = $db->loadObjectList();
        
		return array_merge($result2,$result1);
	}
    
    
    
    
}
