<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.application.component.model');
//jimport('joomla.application.component.modelitem');
//jimport('joomla.application.component.modelform');

jimport('joomla.application.component.modeladmin');

require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'imageselect.php');


/**
 * sportsmanagementModelEditPerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelEditPerson extends JModelAdmin
{
  
  /* interfaces */
	var $latitude	= null;
	var $longitude	= null;
	
	/**
	 * Method to load content person data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function getData()
	{
	   $this->_id = JRequest::getInt('id',0);
//		// Lets load the content if it doesn't already exist
//		if (empty($this->_data))
//		{
			$query='SELECT * FROM #__sportsmanagement_person WHERE id='.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return $this->_data;
//		}
//		return true;
	}

	

	/**
	 * Method to return a joomla users array (id,name)
	 *
	 * @access	public
	 * @return	array
	 *

	function getJLUsers()
	{
		$query="SELECT id AS value,name AS text FROM #__users ";
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}
	 */
	



	/**
	 * Returns a Table object, always creating it
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
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$cfg_which_media_tool = JComponentHelper::getParams(JRequest::getCmd('option'))->get('cfg_which_media_tool',0);
        $app = JFactory::getApplication('site');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->name, $this->name,array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams(JRequest::getCmd('option'))->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
    
}
