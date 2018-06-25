<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
 

class sportsmanagementModelsmquotetxt extends JModelAdmin
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
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$app->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.smquotetxt', 'smquotetxt', array('control' => 'jform', 'load_data' => $loadData));
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
    
    /**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		//$data = JFactory::getApplication()->getUserState('com_templates.edit.source.data', array());
        $data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.source.data', array());

		if (empty($data)) {
			$data = $this->getSource();
		}

		return $data;
	}
    
    
  /**
	 * Method to store the source file contents.
	 *
	 * @param	array	The souce data to save.
	 *
	 * @return	boolean	True on success, false otherwise and internal error set.
	 * @since	1.6
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        jimport('joomla.filesystem.file');
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        $filePath = JPATH_SITE.DS.'modules'.DS.'mod_sportsmanagement_rquotes'.DS.'mod_sportsmanagement_rquotes'.DS.$data['filename'];
        //$return = JFile::write($filePath, $data['source']);
        
        if ( !JFile::write($filePath, $data['source']) )
        {
        JError::raiseWarning(500,'COM_SPORTSMANAGEMENT_ADMIN_XML_FILE_WRITE');
        }
        else
        {
        JError::raiseNotice(500,'COM_SPORTSMANAGEMENT_ADMIN_XML_FILE_WRITE_SUCCESS');
        }
    }    
  
  /**
	 * Method to get a single record.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function &getSource()
	{
		$app = JFactory::getApplication();
    $option = JFactory::getApplication()->input->getCmd('option');
        $item = new stdClass;
//		if (!$this->_template) {
//			$this->getTemplate();
//		}

		//if ($this->_template) {
			$file_name	= JFactory::getApplication()->input->getVar('file_name');
			//$client		= JApplicationHelper::getClientInfo($this->_template->client_id);
			//$filePath	= JPath::clean($client->path.'/templates/'.$this->_template->element.'/'.$fileName);
            $filePath = JPATH_SITE.DS.'modules'.DS.'mod_sportsmanagement_rquotes'.DS.'mod_sportsmanagement_rquotes'.DS.$file_name;

			if (file_exists($filePath)) {
				jimport('joomla.filesystem.file');

				//$item->extension_id	= $this->getState('extension.id');
				$item->filename		= JFactory::getApplication()->input->getVar('file_name');
				$item->source		= JFile::read($filePath);
			} else {
				$this->setError(JText::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));
			}
		//}

		return $item;
	}
    

    

}
