<?php

/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       smimageimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Archive\Archive;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

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
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key.
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
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param  type    The table type to instantiate
	 * @param  string    A prefix for the table class name. Optional.
	 * @param  array    Configuration array for model. Optional.
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
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.smimageimport', 'smimageimport', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
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

	/**
	 * sportsmanagementModelsmimageimport::import()
	 *
	 * @return
	 */
	function import()
	{
		$app = Factory::getApplication();

		// $option = Factory::getApplication()->input->getCmd('option');
		// $post = Factory::getApplication()->input->post->getArray(array());
		$option = $app->input->getCmd('option');
		$post = $app->input->post->getArray(array());

		$server = 'http://sportsmanagement.fussballineuropa.de/jdownloads/';

		$cid = $post['cid'];

		foreach ($cid as $key => $value)
		{
			$name = $post['picture'][$value];
			$folder = $post['folder'][$value];
			$directory = $post['directory'][$value];
			$file = $post['file'][$value];

			$folder = str_replace(' ', '%20', $folder);

			$servercopy = $server . $folder . '/' . $file;

			// Set the target directory
			$base_Dir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DS;

			// $file['name'] = basename($servercopy);
			// $filename = $file['name'];
			// $filepath = $base_Dir . $filename;
			// $file['name'] = $file;
			$filename = $file;
			$filepath = $base_Dir . $filename;

			if (!copy($servercopy, $filepath))
			{
			}
			else
			{
				$extractdir = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $directory;
				$dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $filename;

				if (strtolower(File::getExt($dest)) == 'zip')
				{
					if (version_compare(JSM_JVERSION, '4', 'eq'))
					{
						$archive = new Archive;
						$result = $archive->extract($dest, $extractdir);
					}
					else
					{
						$result = JArchive::extract($dest, $extractdir);
					}

					if ($result === false)
					{
						$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_ERROR'), 'error');

						return false;
					}
					else
					{
						$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_DONE', $name), 'notice');

						// Must be a valid primary key value.
						$object->id = $value;
						$object->published = 1;

						// Update their details in the users table using id as the primary key.
						$result = Factory::getDbo()->updateObject('#__sportsmanagement_pictures', $object, 'id');
					}
				}
				else
				{
					$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_NO_ZIP_ERROR'), 'error');

					return false;
				}
			}
		}
	}

}

