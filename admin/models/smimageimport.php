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

use Joomla\Archive\Archive;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * sportsmanagementModelsmimageimport
 */
class sportsmanagementModelsmimageimport extends AdminModel
{
	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param string $type   The table type to instantiate.
	 * @param string $prefix A prefix for the table class name.
	 * @param array  $config Configuration array for the table.
	 *
	 * @return Table
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
	 * @param array   $data     Data for the form.
	 * @param boolean $loadData True if the form is to load its own data.
	 *
	 * @return mixed A form object on success, false on failure.
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_sportsmanagement.smimageimport',
			'smimageimport',
			array('control' => 'jform', 'load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Download and import selected image packages.
	 *
	 * @return boolean True on success.
	 * @throws Exception
	 */
	public function import()
	{
		$app    = Factory::getApplication();
		$input  = $app->getInput();
		$post   = $input->post->getArray(array());
		$server = 'http://sportsmanagement.fussballineuropa.de/jdownloads/';
		$cids   = isset($post['cid']) && is_array($post['cid']) ? $post['cid'] : array();
		$cids   = array_values(array_filter(array_map('intval', $cids), static fn($id) => $id > 0));

		if (!$cids)
		{
			return false;
		}

		$baseDir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp';

		if (!is_writable($baseDir))
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_WRITABLE'), 'warning');
			$app->enqueueMessage(
				Text::sprintf('COM_SPORTSMANAGEMENT_FILE_PERMISSIONS', Path::getPermissions($baseDir)),
				'warning'
			);
			return false;
		}

		$http = HttpFactory::getHttp(new Registry(), array('curl', 'stream'));
		$db   = sportsmanagementHelper::getDBConnection();

		foreach ($cids as $value)
		{
			$name      = (string) ($post['picture'][$value] ?? '');
			$folder    = (string) ($post['folder'][$value] ?? '');
			$directory = trim((string) ($post['directory'][$value] ?? ''), '/\\');
			$file      = basename((string) ($post['file'][$value] ?? ''));

			if ($file === '' || $directory === '')
			{
				continue;
			}

			$serverCopy = $server . str_replace(' ', '%20', $folder) . '/' . rawurlencode($file);
			$filepath   = $baseDir . DIRECTORY_SEPARATOR . $file;

			try
			{
				$response = $http->get($serverCopy);
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage(), 'error');
				return false;
			}

			if (!$response || !in_array((int) $response->code, array(200, 310), true))
			{
				$code = $response ? (int) $response->code : 0;
				$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' HTTP ' . $code, 'error');
				return false;
			}

			try
			{
				if (!File::write($filepath, $response->body))
				{
					return false;
				}
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage(), 'error');
				return false;
			}

			if (strtolower(File::getExt($filepath)) !== 'zip')
			{
				Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_NO_ZIP_ERROR'), Log::ERROR, 'jsmerror');
				return false;
			}

			$extractDir = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR
				. 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $directory;

			try
			{
				$archive = new Archive();
				$result  = $archive->extract($filepath, $extractDir);
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage(), 'error');
				return false;
			}

			if ($result === false)
			{
				Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_ERROR'), Log::ERROR, 'jsmerror');
				return false;
			}

			Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_DONE', $name), Log::NOTICE, 'jsmerror');

			$object            = new stdClass();
			$object->id        = $value;
			$object->published = 1;
			$db->updateObject('#__sportsmanagement_pictures', $object, 'id');
		}

		return true;
	}

	/**
	 * Method override to check if an existing record can be edited.
	 *
	 * @param array  $data Input data.
	 * @param string $key  Primary key field.
	 *
	 * @return boolean
	 * @since  1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$id = (int) ($data[$key] ?? 0);

		return Factory::getApplication()->getIdentity()->authorise(
			'core.edit',
			'com_sportsmanagement.message.' . $id
		) || parent::allowEdit($data, $key);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed The data for the form.
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.smimageimport.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
