<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      smextxmleditor.php
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
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModelsmextxmleditor
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelsmextxmleditor extends JSMModelAdmin
{

    
	
    
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
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        jimport('joomla.filesystem.file');
       
        $filePath = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$option.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'extended'.DIRECTORY_SEPARATOR.$data['filename'];
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
		// Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $item = new stdClass;
        
        $config = new stdClass;
        $configPath = JPATH_SITE.DIRECTORY_SEPARATOR.'configuration.php';
        $config->source	= File::read($configPath);
       
			$file_name	= Factory::getApplication()->input->getVar('file_name');
            $filePath = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$option.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'extended'.DIRECTORY_SEPARATOR.$file_name;

			if (file_exists($filePath)) {
				jimport('joomla.filesystem.file');
				$item->filename		= Factory::getApplication()->input->getVar('file_name');
				$item->source		= File::read($filePath);
			} else {
				$this->setError(Text::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));
			}
		//}

		return $item;
	}
    

    

}
