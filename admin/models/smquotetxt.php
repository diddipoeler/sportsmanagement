<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      smquotetxt.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text; 
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\AdminModel;
jimport('joomla.filesystem.folder');
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log; 

class sportsmanagementModelsmquotetxt extends AdminModel
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
		$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $cfg_which_media_tool = ComponentHelper::getParams($option)->get('cfg_which_media_tool',0);

        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.smquotetxt', 'smquotetxt', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}

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
		//$data = Factory::getApplication()->getUserState('com_templates.edit.source.data', array());
        $data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.source.data', array());

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
		$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        jimport('joomla.filesystem.file');
       
        $filePath = JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_sportsmanagement_rquotes'.DIRECTORY_SEPARATOR.'mod_sportsmanagement_rquotes'.DIRECTORY_SEPARATOR.$data['filename'];
        //$return = File::write($filePath, $data['source']);
        
        if ( !File::write($filePath, $data['source']) )
        {
        Log::add( 'COM_SPORTSMANAGEMENT_ADMIN_XML_FILE_WRITE');
        }
        else
        {
        Log::add( 'COM_SPORTSMANAGEMENT_ADMIN_XML_FILE_WRITE_SUCCESS');
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
		$app = Factory::getApplication();
    $option = Factory::getApplication()->input->getCmd('option');
        $item = new stdClass;
//		if (!$this->_template) {
//			$this->getTemplate();
//		}

		//if ($this->_template) {
			$file_name	= Factory::getApplication()->input->getVar('file_name');
			//$client		= JApplicationHelper::getClientInfo($this->_template->client_id);
			//$filePath	= JPath::clean($client->path.'/templates/'.$this->_template->element.'/'.$fileName);
            $filePath = JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_sportsmanagement_rquotes'.DIRECTORY_SEPARATOR.'mod_sportsmanagement_rquotes'.DIRECTORY_SEPARATOR.$file_name;

			if (file_exists($filePath)) {
				jimport('joomla.filesystem.file');

				//$item->extension_id	= $this->getState('extension.id');
				$item->filename		= Factory::getApplication()->input->getVar('file_name');
				$item->source		= File::read($filePath);
			} else {
				$this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));
			}
		//}

		return $item;
	}
    

    

}
