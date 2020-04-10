<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @file       imagehandler.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Log\Log;

require_once JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'imageselect.php';

/**
 * sportsmanagementControllerImagehandler
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerImagehandler extends BaseController
{

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
	}

	/**
	 * logic for uploading an image
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	function upload()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		// Check for request forgeries
		JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$file        = Factory::getApplication()->input->getVar('userfile', '', 'files', 'array');
		$type        = Factory::getApplication()->input->getVar('type');
		$folder      = ImageSelectSM::getfolder($type);
		$field       = Factory::getApplication()->input->getVar('field');
		$linkaddress = Factory::getApplication()->input->getVar('linkaddress');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		ClientHelper::setCredentialsFromRequest('ftp');

		// Set the target directory
		$base_Dir = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DS;

		$app->enqueueMessage(Text::_($type), '');
		$app->enqueueMessage(Text::_($folder), '');
		$app->enqueueMessage(Text::_($base_Dir), '');

		// Do we have an imagelink?
		if (!empty($linkaddress))
		{
			$file['name'] = basename($linkaddress);

			if (preg_match("/dfs_/i", $linkaddress))
			{
				$filename = $file['name'];
			}
			else
			{
				// Sanitize the image filename
				$filename = ImageSelectSM::sanitize($base_Dir, $file['name']);
			}

			$filepath = $base_Dir . $filename;

			if (!copy($linkaddress, $filepath))
			{
				echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED') . "'); window.history.go(-1); </script>\n";

				// $app->close();
			}
			else
			{
				// Echo "<script> alert('" . Text::_( 'COPY COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
				echo "<script>  window.parent.selectImage_" . $type . "('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";

				// $app->close();
			}
		}

		// Do we have an upload?
		if (empty($file['name']))
		{
			echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY') . "'); window.history.go(-1); </script>\n";

			// $app->close();
		}

		// Check the image
		$check = ImageSelectSM::check($file);

		if ($check === false)
		{
			$app->redirect($_SERVER['HTTP_REFERER']);
		}

		// Sanitize the image filename
		$filename = ImageSelectSM::sanitize($base_Dir, $file['name']);
		$filepath = $base_Dir . $filename;

		// Upload the image
		if (!File::upload($file['tmp_name'], $filepath))
		{
			echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED') . "'); window.history.go(-1); </script>\n";

			//          $app->close();
		}
		else
		{
			//          echo "<script> alert('" . Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
			//          echo "<script> alert('" . Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE' ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
			echo "<script>  window.parent.selectImage_" . $type . "('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";

			//          $app->close();
		}
	}

	/**
	 * logic to mass delete images
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	function delete()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		ClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$images = Factory::getApplication()->input->getVar('rm', array(), '', 'array');
		$type   = Factory::getApplication()->input->getVar('type');

		$folder = ImageSelectSM::getfolder($type);

		if (count($images))
		{
			foreach ($images as $image)
			{
				if ($image !== FilterInput::clean($image, 'path'))
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE') . ' ' . htmlspecialchars($image, ENT_COMPAT, 'UTF-8'), Log::WARNING, 'jsmerror');
					continue;
				}

				$fullPath      = Path::clean(JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $image);
				$fullPaththumb = Path::clean(JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . 'small' . DIRECTORY_SEPARATOR . $image);

				if (is_file($fullPath))
				{
					File::delete($fullPath);

					if (File::exists($fullPaththumb))
					{
						File::delete($fullPaththumb);
					}
				}
			}
		}

		$app->redirect('index.php?option=' . $option . '&view=imagehandler&type=' . $type . '&tmpl=component');
	}

}

