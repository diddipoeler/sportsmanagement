<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       jlextprofleagimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Log\Log;

jimport('joomla.filesystem.archive');


/**
 * sportsmanagementControllerjlextprofleagimport
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerjlextprofleagimport extends BaseController
{

	/**
	 * sportsmanagementControllerjlextprofleagimport::save()
	 *
	 * @return
	 */
	function save()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$msg = '';
		ToolbarHelper::back(Text::_('JPREV'), Route::_('index.php?option=com_sportsmanagement&view=jlextprofleagimport&controller=jlextprofleagimport'));
		$app   = Factory::getApplication();
		$post  = Factory::getApplication()->input->post->getArray(array());
		$model = $this->getModel('jlextprofleagimport');

		// First step - upload
		if (isset($post['sent']) && $post['sent'] == 1)
		{
			$upload = $app->input->files->get('import_package');

			$lmoimportuseteams = Factory::getApplication()->input->getVar('lmoimportuseteams', null);
			$app->setUserState($option . 'lmoimportuseteams', $lmoimportuseteams);

			$tempFilePath = $upload['tmp_name'];
			$app->setUserState($option . 'uploadArray', $upload);
			$filename   = '';
			$msg        = '';
			$dest       = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $upload['name'];
			$extractdir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp';
			$importFile = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.xml';

			if (File::exists($importFile))
			{
				File::delete($importFile);
			}

			if (File::exists($tempFilePath))
			{
				if (File::exists($dest))
				{
					File::delete($dest);
				}

				if (!File::upload($tempFilePath, $dest))
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_CANT_UPLOAD'), Log::WARNING, 'jsmerror');

					return;
				}
				else
				{
					if (strtolower(File::getExt($dest)) == 'zip')
					{
						$result = JArchive::extract($dest, $extractdir);

						if ($result === false)
						{
							Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_EXTRACT_ERROR'), Log::WARNING, 'jsmerror');

							return false;
						}

						File::delete($dest);
						$src = Folder::files($extractdir, 'xml', false, true);

						if (!count($src))
						{
							Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_EXTRACT_NOJLG'), Log::WARNING, 'jsmerror');

							// Todo: delete every extracted file / directory
							return false;
						}

						if (strtolower(File::getExt($src[0])) == 'xml')
						{
							if (!@ rename($src[0], $importFile))
							{
								Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_ERROR_RENAME'), Log::WARNING, 'jsmerror');

								return false;
							}
						}
						else
						{
							Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_TMP_DELETED'), Log::WARNING, 'jsmerror');

							return;
						}
					}
					else
					{
						if (strtolower(File::getExt($dest)) == 'xml')
						{
							if (!@ rename($dest, $importFile))
							{
								Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_RENAME_FAILED'), Log::WARNING, 'jsmerror');

								return false;
							}
						}
						else
						{
							Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_WRONG_EXTENSION'), Log::WARNING, 'jsmerror');

							return false;
						}
					}
				}
			}
		}

		$convert = array(
			'charset='              => '',
			'encoding="ISO-8859-1"' => 'encoding="utf-8"',
			'<h2>'                  => '',
			'</h2>'                 => '',
			'<h3>'                  => '',
			'</h3>'                 => '',
			'<br>'                  => '',
			'</br>'                 => '',
			'<b>'                   => '',
			'</b>'                  => '',
			'<![CDATA['             => '',
			']]>'                   => ''
		);

		$source = File::read($importFile);
		$source = str_replace(array_keys($convert), array_values($convert), $source);
		$source = utf8_encode($source);
		$return = File::write($importFile, $source);

		$model->getData();
		$link = 'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit';
		$this->setRedirect($link, $msg);

	}

}

