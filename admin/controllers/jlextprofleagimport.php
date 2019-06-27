<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextprofleagimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
jimport('joomla.filesystem.archive');


/**
 * sportsmanagementControllerjlextprofleagimport
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
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
		$app = Factory::getApplication();
        // Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$msg='';
		ToolbarHelper::back(Text::_('JPREV'),Route::_('index.php?option=com_sportsmanagement&view=jlextprofleagimport&controller=jlextprofleagimport'));
		$app = Factory::getApplication();
		$post=Factory::getApplication()->input->post->getArray(array());
    $model=$this->getModel('jlextprofleagimport');
    
		// first step - upload
		if (isset($post['sent']) && $post['sent']==1)
		{
			//$upload=Factory::getApplication()->input->getVar('import_package',null,'files','array');
$upload = $app->input->files->get('import_package');

			$lmoimportuseteams=Factory::getApplication()->input->getVar('lmoimportuseteams',null);
			$app->setUserState($option.'lmoimportuseteams',$lmoimportuseteams);
			
			$tempFilePath=$upload['tmp_name'];
			$app->setUserState($option.'uploadArray',$upload);
			$filename='';
			$msg='';
			$dest=JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$upload['name'];
			$extractdir=JPATH_SITE.DIRECTORY_SEPARATOR.'tmp';
			$importFile=JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'. DS.'joomleague_import.xml';
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
					if (!File::upload($tempFilePath,$dest))
					{
						JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_CANT_UPLOAD'));
						return;
					}
					else
					{
						if (strtolower(File::getExt($dest))=='zip')
						{
							$result=JArchive::extract($dest,$extractdir);
							if ($result === false)
							{
								JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_EXTRACT_ERROR'));
								return false;
							}
							File::delete($dest);
							$src=Folder::files($extractdir,'xml',false,true);
							if(!count($src))
							{
								JError::raiseWarning(500,'COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_EXTRACT_NOJLG');
								//todo: delete every extracted file / directory
								return false;
							}
							if (strtolower(File::getExt($src[0]))=='xml')
							{
								if (!@ rename($src[0],$importFile))
								{
									JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_ERROR_RENAME'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_TMP_DELETED'));
								return;
							}
						}
						else
						{
							if (strtolower(File::getExt($dest))=='xml')
							{
								if (!@ rename($dest,$importFile))
								{
									JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_RENAME_FAILED'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_CTRL_WRONG_EXTENSION'));
								return false;
							}
						}
					}
			}
		}

/*        
$convert = array (
'Ä' => '&#196;',
'Ö' => '&#214;',
'Ü' => '&#220;',
'ä' => '&#228;',
'ö' => '&#246;',
'ü' => '&#252;',
'ß' => '&#223;',
'charset=' => '',
'<![CDATA[' => '',
']]>' => '',
'é' => '&#233;'
  );
*/


$convert = array (
'charset=' => '',
'encoding="ISO-8859-1"' => 'encoding="utf-8"',
'<h2>' => '',
'</h2>' => '',
'<h3>' => '',
'</h3>' => '',
'<br>' => '',
'</br>' => '',
'<b>' => '',
'</b>' => '',
'<![CDATA[' => '',
']]>' => ''
  );
  

      
$source	= File::read($importFile);
$source = str_replace(array_keys($convert), array_values($convert), $source  );
$source = utf8_encode ( $source );
$return = File::write($importFile,$source );

    $model->getData();
		//$link='index.php?option='.$option.'&task=jlextlmoimports.edit';
        $link = 'index.php?option='.$option.'&view=jlxmlimports&task=jlxmlimport.edit';
		#echo '<br />Message: '.$msg.'<br />';
		#echo '<br />Redirect-Link: '.$link.'<br />';
		
        $this->setRedirect($link,$msg);
        
	}










}


?>
