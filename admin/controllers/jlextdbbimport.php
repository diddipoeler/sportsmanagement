<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jlextdbbimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

/*
Spielplan - Bayernliga Herren Nord (Senioren; Liganr.: 20001)
http://www.basketball-bund.net/index.jsp?Action=101&liga_id=12150

ergebnisse
http://www.basketball-bund.net/public/ergebnisse.jsp?print=1&viewDescKey=sport.dbb.liga.ErgebnisseViewPublic/index.jsp_&liga_id=12150
spielplan
http://www.basketball-bund.net/public/spielplan_list.jsp?print=1&viewDescKey=sport.dbb.liga.SpielplanViewPublic/index.jsp_&liga_id=12150

*/

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementControllerjlextdbbimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementControllerjlextdbbimport extends BaseController
{


	/**
	 * sportsmanagementControllerjlextdbbimport::save()
	 *
	 * @return
	 */
	function save()
	{
		  $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$document = Factory::getDocument();

		// Check for request forgeries
		Factory::getApplication()->input->checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$msg = '';
		$model = $this->getModel('jlextdbbimport');
		$post = Factory::getApplication()->input->get('post');

			  $whichfile = Factory::getApplication()->input->getVar('whichfile', null);

		if ($whichfile == 'playerfile')
		{
			 Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_PLAYERFILE'), Log::NOTICE, 'jsmerror');
		}
		elseif ($whichfile == 'matchfile')
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_MATCHFILE'), Log::NOTICE, 'jsmerror');

			if (isset($post ['dfbimportupdate']))
			{
				Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_MATCHFILE_UPDATE'), Log::NOTICE, 'jsmerror');
			}
		}

			  // First step - upload
		if (isset($post ['sent']) && $post ['sent'] == 1)
		{
			$upload = Factory::getApplication()->input->getVar('import_package', null, 'files', 'array');

					  $lmoimportuseteams = Factory::getApplication()->input->getVar('lmoimportuseteams', null);

					  $app->setUserState($option . 'lmoimportuseteams', $lmoimportuseteams);
			$app->setUserState($option . 'whichfile', $whichfile);
			$app->setUserState($option . 'delimiter', $delimiter);

					  $tempFilePath = $upload ['tmp_name'];
			$app->setUserState($option . 'uploadArray', $upload);
			$filename = '';
			$msg = '';
			$dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $upload ['name'];
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

				if (! File::upload($tempFilePath, $dest))
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_CANT_UPLOAD'), Log::NOTICE, 'jsmerror');

					return;
				}
				else
				{
					if (strtolower(File::getExt($dest)) == 'zip')
					{
						$result = JArchive::extract($dest, $extractdir);

						if ($result === false)
						{
							Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_EXTRACT_ERROR'), Log::NOTICE, 'jsmerror');

							return false;
						}

						File::delete($dest);
						$src = Folder::files($extractdir, 'l98', false, true);

						if (! count($src))
						{
							Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_EXTRACT_NOJLG'), Log::NOTICE, 'jsmerror');

							// Todo: delete every extracted file / directory
							return false;
						}

						if (strtolower(File::getExt($src [0])) == 'csv')
						{
							if (! @ rename($src [0], $importFile))
							{
								Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_ERROR_RENAME'), Log::NOTICE, 'jsmerror');

								return false;
							}
						}
						else
						{
							Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_TMP_DELETED'), Log::NOTICE, 'jsmerror');

							return;
						}
					}
					else
					{
						if (strtolower(File::getExt($dest)) == 'csv' || strtolower(File::getExt($dest)) == 'ics')
						{
							if (! @ rename($dest, $importFile))
							{
								Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_RENAME_FAILED'), Log::NOTICE, 'jsmerror');

								return false;
							}
						}
						else
						{
							Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_WRONG_EXTENSION'), Log::NOTICE, 'jsmerror');

							return false;
						}
					}
				}
			}
		}

		if (isset($post ['dfbimportupdate']))
		{
			$link = 'index.php?option=' . $option . '&view=jlextdfbnetplayerimport&task=jlextdbbimport.update';
		}
		else
		{
			if ($whichfile == 'matchfile')
			{
				$xml_file = $model->getData();
				$link = 'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit';
			}
			else
			{
					$xml_file = $model->getData();
					$link = 'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit';
			}
		}

			$this->setRedirect($link, $msg);
	}

}

