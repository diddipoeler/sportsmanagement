<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @file       imagehandler.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;

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
	 * logic for uploading an image
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	function upload()
	{
		$app    = Factory::getApplication();
		$input  = $app->getInput();
		$option = $input->getCmd('option');

		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$file        = $input->files->get('userfile', array(), 'array');
		$type        = $input->getCmd('type');
		$folder      = ImageSelectSM::getfolder($type);
		$field       = $input->getCmd('field');
		$linkaddress = $input->getString('linkaddress', '');

		/** Set FTP credentials, if given */
		ClientHelper::setCredentialsFromRequest('ftp');

		/** Set the target directory */
		$baseDir = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;

		$app->enqueueMessage(Text::_($type), '');
		$app->enqueueMessage(Text::_($folder), '');
		$app->enqueueMessage(Text::_($baseDir), '');

		/** Do we have an imagelink? */
		if (!empty($linkaddress))
		{
			$file['name'] = basename($linkaddress);

			if (preg_match('/dfs_/i', $linkaddress))
			{
				$filename = $file['name'];
			}
			else
			{
				$filename = ImageSelectSM::sanitize($baseDir, $file['name']);
			}

			$filepath = $baseDir . $filename;

			if (!copy($linkaddress, $filepath))
			{
				echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED') . "'); window.history.go(-1); </script>\n";
			}
			else
			{
				echo "<script>  window.parent.selectImage_" . $type . "('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";
			}
		}

		/** Do we have an upload? */
		if (empty($file['name']))
		{
			echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY') . "'); window.history.go(-1); </script>\n";
			return;
		}

		/** Check the image */
		$check = ImageSelectSM::check($file);

		if ($check === false)
		{
			$app->redirect($_SERVER['HTTP_REFERER']);
			return;
		}

		/** Sanitize the image filename */
		$filename = ImageSelectSM::sanitize($baseDir, $file['name']);
		$filepath = $baseDir . $filename;

		/** Upload the image */
		if (!File::upload($file['tmp_name'], $filepath))
		{
			echo "<script> alert('" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED') . "'); window.history.go(-1); </script>\n";
		}
		else
		{
			echo "<script>  window.parent.selectImage_" . $type . "('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";
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
		$input  = $app->getInput();
		$option = $input->getCmd('option');

		/** Set FTP credentials, if given */
		ClientHelper::setCredentialsFromRequest('ftp');

		/** Get some data from the request */
		$images = $input->get('rm', array(), 'array');
		$type   = $input->getCmd('type');
		$folder = ImageSelectSM::getfolder($type);

		if (count($images))
		{
			foreach ($images as $image)
			{
				if ($image !== InputFilter::clean($image, 'path'))
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE') . ' ' . htmlspecialchars($image, ENT_COMPAT, 'UTF-8'), Log::WARNING, 'jsmerror');
					continue;
				}

				$fullPath      = Path::clean(JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $image);
				$fullPaththumb = Path::clean(JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . 'small' . DIRECTORY_SEPARATOR . $image);

				if (is_file($fullPath))
				{
					File::delete($fullPath);

					if (is_file($fullPaththumb))
					{
						File::delete($fullPaththumb);
					}
				}
			}
		}

		$app->redirect('index.php?option=' . $option . '&view=imagehandler&type=' . $type . '&tmpl=component');
	}
}
