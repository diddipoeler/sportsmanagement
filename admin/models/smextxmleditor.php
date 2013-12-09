<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
 

class sportsmanagementModelsmextxmleditor extends JModelAdmin
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
		$form = $this->loadForm('com_sportsmanagement.smextxmleditor', 'smextxmleditor', array('control' => 'jform', 'load_data' => $loadData));
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
    
    
  public function getContents()
  {
    
    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $file_name = JRequest::getVar('file_name');
    
      //$path = $this->getState('translation.path');
      $path = JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'assets'.DS.'extended'.DS.$file_name;
      if (JFile::exists($path))
      {
        $this->contents = file_get_contents($path);
      }
      else
      {
        $this->contents = '';
      }
    
    return $this->contents;
  }
    
//    function getSportsManagementTables()
//    {
//        $mainframe = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $query="SHOW TABLES LIKE '%_".COM_SPORTSMANAGEMENT_TABLE."%'";
//		$this->_db->setQuery($query);
//		return $this->_db->loadResultArray();
//    }
//    
//    function setSportsManagementTableQuery($table, $command)
//    {
//        $mainframe = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        $query = strtoupper($command).' TABLE `'.$table.'`'; 
//            $this->_db->setQuery($query);
//            if (!$this->_db->query())
//		{
//			$mainframe->enqueueMessage(JText::_('sportsmanagementModeldatabasetool getErrorMsg<br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Error');
//			return false;
//		}
//        
//        
//		return true;
//    }
    

}
