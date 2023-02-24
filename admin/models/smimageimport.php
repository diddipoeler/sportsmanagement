<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       smimageimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Archive\Archive;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Filesystem\Path;

/**
 * sportsmanagementModelsmimageimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */

/**
 * sportsmanagementModelsmimageimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelsmimageimport extends BaseDatabaseModel
{

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type  $type  The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array    Configuration array for model. Optional.
	 *
	 * @return JTable    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'Pictures', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_sportsmanagement.smimageimport', 'smimageimport', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * sportsmanagementModelsmimageimport::import()
	 *
	 * @return false|void
	 * @throws Exception
	 */
	function import()
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$post   = $app->input->post->getArray(array());
		$server = 'http://sportsmanagement.fussballineuropa.de/jdownloads/';
		$cid = $post['cid'];

		foreach ($cid as $key => $value)
		{
			$name      = $post['picture'][$value];
			$folder    = $post['folder'][$value];
			$directory = $post['directory'][$value];
			$file      = $post['file'][$value];
			$folder = str_replace(' ', '%20', $folder);
			$servercopy = $server . $folder . '/' . $file;
			$endung = strtolower(File::getExt($servercopy ));

			/** Set the target directory */
			$base_Dir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' ;
			$filename = $file;
			$filepath = $base_Dir . DIRECTORY_SEPARATOR .$filename;

// Try to make the template file writable.
		if (!is_writable($base_Dir))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_WRITABLE'), 'warning');
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_PERMISSIONS', Path::getPermissions($base_Dir)), 'warning');
/*
			if (!Path::isOwner($filePath))
			{
				$app->enqueueMessage(Text::_('COM_TEMPLATES_CHECK_FILE_OWNERSHIP'), 'warning');
			}
			return false;
			*/
		}
			
// Download the package
		try
		{
			if (version_compare(JSM_JVERSION, '4', 'eq'))
			{
			$result = HttpFactory::getHttp([], ['curl', 'stream'])->get($servercopy);
			}
			else
			{
			$http = HttpFactory::getHttp(null, array('curl', 'stream'));
			$result  = $http->get($servercopy);	
			}
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($e->getMessage()), 'Error');
			return false;
		}
		if (!$result || ($result->code != 200 && $result->code != 310))
		{
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($result->code), 'Error');
			return false;
		}
			
		try
		{
		// Write the file to disk
		$resultwrite = File::write($filepath, $result->body);
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($e->getMessage()), 'Error');
			return false;
		}
			
/*			
try
{			
$http = HttpFactory::getHttp(null, array('curl', 'stream'));
$resulthttp  = $http->get($servercopy );
File::write($filepath, $resulthttp->body);
}
catch (Exception $e)
{
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($e->getMessage()), 'Error');
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($servercopy ), 'Error');
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($endung ), 'Error');
}
*/
			
			if (!$resultwrite)
			{
			}
			else
			{
				$extractdir = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $directory;
				$dest       = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $filename;

				if (strtolower(File::getExt($dest)) == 'zip')
				{
					if (version_compare(JSM_JVERSION, '4', 'eq'))
					{
						try
		{
						$archive = new Archive;
						$result  = $archive->extract($dest, $extractdir);
							}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($e->getMessage()), 'Error');
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($servercopy ), 'Error');
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. Text::_($endung ), 'Error');
			$result = false;
		}
					}
					else
					{
						$result = JArchive::extract($dest, $extractdir);
					}

					if ($result === false)
					{
                        Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_ERROR'), Log::ERROR, 'jsmerror');
						return false;
					}
					else
					{
                        Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_DONE', $name), Log::NOTICE, 'jsmerror');
						$object = new stdClass();
						$object->id = $value;
						$object->published = 1;
						$result = Factory::getDbo()->updateObject('#__sportsmanagement_pictures', $object, 'id');
					}
				}
				else
				{
                    Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_NO_ZIP_ERROR'), Log::ERROR, 'jsmerror');
					return false;
				}
			}
		}
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return boolean
	 * @since  1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return Factory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.' . ((int) isset($data[$key]) ? $data[$key] : 0)) || parent::allowEdit($data, $key);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.smimageimport.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

}

