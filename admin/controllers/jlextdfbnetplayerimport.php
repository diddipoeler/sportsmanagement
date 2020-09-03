<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       jlextdfbnetplayerimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Log\Log;

jimport('joomla.filesystem.archive');

/**
 * sportsmanagementControllerjlextdfbnetplayerimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementControllerjlextdfbnetplayerimport extends BaseController
{

	/**
	 * sportsmanagementControllerjlextdfbnetplayerimport::save()
	 *
	 * @return
	 */
	function save()
	{
		$option   = Factory::getApplication()->input->getCmd('option');
		$app      = Factory::getApplication();
		$document = Factory::getDocument();

		// Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$msg   = '';
		$model = $this->getModel('jlextdfbnetplayerimport');
		$post  = Factory::getApplication()->input->post->getArray(array());

		$whichfile = Factory::getApplication()->input->getVar('whichfile', null);

		if (!$post['filter_season'] && $whichfile == 'playerfile')
		{
			$link = 'index.php?option=' . $option . '&view=jlextdfbnetplayerimport';
			$msg  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE_NO_SEASON');
			//Factory::getApplication()->Redirect($link, $msg, 'ERROR');
			$this->setRedirect($link, $msg, 'error');
		}

		if ($whichfile == 'playerfile')
		{
			//Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE'), Log::NOTICE, 'jsmerror');
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE'),'NOTICE');
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_PLAYERFILE');
		}
		elseif ($whichfile == 'matchfile')
		{
			//Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE'), Log::NOTICE, 'jsmerror');
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE'),'NOTICE');
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE');
			if (isset($post ['dfbimportupdate']))
			{
				//Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE_UPDATE'), Log::NOTICE, 'jsmerror');
				$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE_UPDATE'),'NOTICE');
				$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_MATCHFILE_UPDATE');
			}
		}
		else
		{
		$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_ICSFILE'),'NOTICE');
		$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_ICSFILE');
		}

		/**
		 * first step - upload
		 */
		if (isset($post ['sent']) && $post ['sent'] == 1)
		{
			$upload            = $app->input->files->get('import_package');
			$lmoimportuseteams = Factory::getApplication()->input->getVar('lmoimportuseteams', null);

			$app->setUserState($option . 'lmoimportuseteams', $lmoimportuseteams);
			$app->setUserState($option . 'whichfile', $whichfile);
			$app->setUserState($option . 'delimiter', $delimiter);

			$tempFilePath = $upload ['tmp_name'];
			$app->setUserState($option . 'uploadArray', $upload);
			$filename   = '';
			$msg        = '';
			$dest       = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $upload ['name'];
			$extractdir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp';
			$importFile = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement_import.csv';

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
					//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . '-' . 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'), Log::WARNING, 'jsmerror');
					$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'),'NOTICE');
					$link = 'index.php?option=' . $option . '&view=jlextdfbnetplayerimport';
					$this->setRedirect($link, Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_CANT_UPLOAD'), 'error');
					//return;
				}
				else
				{
					if (strtolower(File::getExt($dest)) == 'zip')
					{
						$result = JArchive::extract($dest, $extractdir);

						if ($result === false)
						{
							//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . '-' . 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR'), Log::WARNING, 'jsmerror');
							$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_ERROR'),'NOTICE');
							return false;
						}

						File::delete($dest);
						$src = Folder::files($extractdir, 'l98', false, true);

						if (!count($src))
						{
							Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . '-' . 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_EXTRACT_NOJLG'), Log::WARNING, 'jsmerror');

							return false;
						}

						if (strtolower(File::getExt($src [0])) == 'csv')
						{
							if (!@ rename($src [0], $importFile))
							{
								Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . '-' . 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_ERROR_RENAME'), Log::WARNING, 'jsmerror');

								return false;
							}
						}
						else
						{
							Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . '-' . 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_TMP_DELETED'), Log::WARNING, 'jsmerror');

							return;
						}
					}
					else
					{
						if (strtolower(File::getExt($dest)) == 'csv' || strtolower(File::getExt($dest)) == 'ics')
						{
							if (!@ rename($dest, $importFile))
							{
								//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . '-' . 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_RENAME_FAILED'), Log::WARNING, 'jsmerror');
								$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_RENAME_FAILED'),'NOTICE');
								$link = 'index.php?option=' . $option . '&view=jlextdfbnetplayerimport';
								$this->setRedirect($link, Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_RENAME_FAILED'), 'error');
							}
						}
						else
						{
							//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . '-' . 'COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_WRONG_EXTENSION'), Log::WARNING, 'jsmerror');
							$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_WRONG_EXTENSION'),'NOTICE');
							$link = 'index.php?option=' . $option . '&view=jlextdfbnetplayerimport';
							$this->setRedirect($link, Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT_CTRL_WRONG_EXTENSION'), 'error');
						}
					}
				}
			}
		}

		if (isset($post ['dfbimportupdate']))
		{
			$link = 'index.php?option=' . $option . '&view=jlextdfbnetplayerimport&task=jlextdfbnetplayerimport.update';
		}
		else
		{
			if ($whichfile == 'matchfile')
			{
				$xml_file = $model->getData($post);
				$link     = 'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit';
			}
			else
			{
				$xml_file = $model->getData($post);
				$link     = 'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit&filter_season=' . $post['filter_season'];
			}
		}

		//Factory::getApplication()->Redirect($link, $msg, 'NOTICE');
		$this->setRedirect($link, $msg, 'notice');
	}
}

