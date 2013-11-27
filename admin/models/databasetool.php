<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 

class sportsmanagementModeldatabasetool extends JModelAdmin
{

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
		$form = $this->loadForm('com_sportsmanagement.databasetool', 'databasetool', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        /*        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_icon',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/agegroups');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        */
		return $form;
	}
    
    function getSportsManagementTables()
    {
        $query="SHOW TABLES LIKE '%_".COM_SPORTSMANAGEMENT_TABLE."%'";
		$this->_db->setQuery($query);
		return $this->_db->loadResultArray();
    }
    
    function setSportsManagementTableQuery($table, $command)
    {
        $query = strtoupper($command).' TABLE `'.$table.'`'; 
            $this->_db->setQuery($query);
            if (!$this->_db->query())
		{
			$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool getErrorMsg<br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
			return false;
		}
        
        
		return true;
    }
    

}
