<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
 

/**
 * sportsmanagementModelsmextxmleditor
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
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
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$app->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
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
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        jimport('joomla.filesystem.file');
        //$app->enqueueMessage(JText::_('sportsmanagementModelsmextxmleditor save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        $filePath = JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'assets'.DS.'extended'.DS.$data['filename'];
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
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $item = new stdClass;
        
        $config = new stdClass;
        $configPath = JPATH_SITE.DS.'configuration.php';
        $config->source	= JFile::read($configPath);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' source<br><pre>'.print_r($config->source,true).'</pre>'),'');
        
//		if (!$this->_template) {
//			$this->getTemplate();
//		}

		//if ($this->_template) {
			$file_name	= JFactory::getApplication()->input->getVar('file_name');
			//$client		= JApplicationHelper::getClientInfo($this->_template->client_id);
			//$filePath	= JPath::clean($client->path.'/templates/'.$this->_template->element.'/'.$fileName);
            $filePath = JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'assets'.DS.'extended'.DS.$file_name;

			if (file_exists($filePath)) {
				jimport('joomla.filesystem.file');

				//$item->extension_id	= $this->getState('extension.id');
				$item->filename		= JFactory::getApplication()->input->getVar('file_name');
				$item->source		= JFile::read($filePath);
			} else {
				$this->setError(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));
			}
		//}

		return $item;
	}
    

    

}
