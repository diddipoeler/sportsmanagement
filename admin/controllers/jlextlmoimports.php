<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextlmoimports.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Log\Log;
jimport('joomla.filesystem.archive');

/**
 * sportsmanagementControllerjlextlmoimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementControllerjlextlmoimports extends BaseController
{
	
	/**
	 * sportsmanagementControllerjlextlmoimports::save()
	 * 
	 * @return
	 */
	function save()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        // Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$msg = '';
		ToolbarHelper::back(Text::_('JPREV'),Route::_('index.php?option=com_sportsmanagement&view=jllmoimport&controller=jllmoimport'));
		$app = Factory::getApplication();
		$post = Factory::getApplication()->input->post->getArray(array());
        $model = $this->getModel('jlextlmoimports');
    
		// first step - upload
		if (isset($post['sent']) && $post['sent']==1)
		{
        $upload = $app->input->files->get('import_package');

			$lmoimportuseteams = Factory::getApplication()->input->getVar('lmoimportuseteams',null);
			$app->setUserState($option.'lmoimportuseteams',$lmoimportuseteams);
			
			$tempFilePath = $upload['tmp_name'];
			$app->setUserState($option.'uploadArray',$upload);
			$filename = '';
			$msg = '';
			$dest = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$upload['name'];
			$extractdir = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp';
			$importFile = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.l98';
			if ( File::exists($importFile) )
			{
				File::delete($importFile);
			}
			if ( File::exists($tempFilePath) )
			{
					if ( File::exists($dest) )
					{
						File::delete($dest);
					}
					if ( !File::upload($tempFilePath,$dest) )
					{
                        Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_CANT_UPLOAD'), Log::NOTICE, 'jsmerror');
						return;
					}
					else
					{
						if ( strtolower(File::getExt($dest)) == 'zip' )
						{
							$result = JArchive::extract($dest,$extractdir);
							if ($result === false)
							{
                                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_EXTRACT_ERROR'), Log::NOTICE, 'jsmerror');
								return false;
							}
							File::delete($dest);
							$src = Folder::files($extractdir,'l98',false,true);
							if(!count($src))
							{
                                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_EXTRACT_NOJLG'), Log::NOTICE, 'jsmerror');
								//todo: delete every extracted file / directory
								return false;
							}
							if ( strtolower(File::getExt($src[0])) == 'l98' )
							{
								if (!@ rename($src[0],$importFile))
								{
                                    Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_ERROR_RENAME'), Log::NOTICE, 'jsmerror');
									return false;
								}
							}
							else
							{
                                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_TMP_DELETED'), Log::NOTICE, 'jsmerror');
								return;
							}
						}
						else
						{
							if ( strtolower(File::getExt($dest)) == 'l98' )
							{
								if (!@ rename($dest,$importFile))
								{
                                    Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_RENAME_FAILED'), Log::NOTICE, 'jsmerror');
									return false;
								}
							}
							else
							{
                                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_WRONG_EXTENSION'), Log::NOTICE, 'jsmerror');
								return false;
							}
						}
					}
			}
		}
    $model->getData();
        $link = 'index.php?option='.$option.'&view=jlxmlimports&task=jlxmlimport.edit';
		$this->setRedirect($link,$msg);
	}










}


?>
