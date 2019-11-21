<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlxmlimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage controllers
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
 * sportsmanagementControllerJLXMLImport
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerJLXMLImport extends BaseController
{
	/**
	 * sportsmanagementControllerJLXMLImport::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

/**
 * 		Register Extra tasks
 */
		$this->registerTask('edit','display');
		$this->registerTask('insert','display');
		$this->registerTask('selectpage','display');
	}

	/**
	 * sportsmanagementControllerJLXMLImport::display()
	 * 
	 * @param bool $cachable
	 * @param bool $urlparams
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
	   $app = Factory::getApplication();
     
		switch ($this->getTask())
		{
			case 'edit':
				Factory::getApplication()->input->set('layout','form');
				Factory::getApplication()->input->set('view','jlxmlimports');
				Factory::getApplication()->input->set('edit',true);
				break;
			case 'insert':
				Factory::getApplication()->input->set('layout','info');
				Factory::getApplication()->input->set('view','jlxmlimports');
				Factory::getApplication()->input->set('edit',true);
				break;
           case 'update':
				Factory::getApplication()->input->set('layout','update');
				Factory::getApplication()->input->set('view','jlxmlimports');
				Factory::getApplication()->input->set('edit',true);
				break;     
		}

		parent::display($cachable = false, $urlparams = false);
	}

	/**
	 * sportsmanagementControllerJLXMLImport::select()
	 * 
	 * @return void
	 */
	function select()
	{
		$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
		$selectType = Factory::getApplication()->input->getVar('type',0,'get','int');
		$recordID = Factory::getApplication()->input->getVar('id',0,'get','int');
		$app->setUserState($option.'selectType',$selectType);
		$app->setUserState($option.'recordID',$recordID);

		Factory::getApplication()->input->set('hidemainmenu',1);
		Factory::getApplication()->input->set('layout','selectpage');
		Factory::getApplication()->input->set('view','jlxmlimports');

		parent::display();
	}

	/**
	 * sportsmanagementControllerJLXMLImport::save()
	 * 
	 * @return
	 */
	function save()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$msg = '';
		ToolbarHelper::back(Text::_('JPREV'),Route::_('index.php?option=com_sportsmanagement&task=jlxmlimport.display'));
		$app = Factory::getApplication();
		$post = Factory::getApplication()->input->post->getArray(array());
        
        $projectid = Factory::getApplication()->input->getVar('projektfussballineuropa',null);

		$app->setUserState('com_sportsmanagement'.'importelanska',$post['importelanska'] );
		$app->setUserState('com_sportsmanagement'.'country',$post['country'] );
		$app->setUserState('com_sportsmanagement'.'agegroup',$post['agegroup'] );
		
        if ( $projectid )
    {
        
        if ( $post['importupdate'] )
        {
        $europalink = "http://www.fussballineuropa.de/index.php?option=com_sportsmanagement&view=jlxmlexports&p=".$projectid."&update=1";    
        }
        else
        {
        $europalink = "http://www.fussballineuropa.de/index.php?option=com_sportsmanagement&view=jlxmlexports&p=".$projectid."&update=0";    
        }
        
        $app->enqueueMessage(Text::_('hole daten von -> '.$europalink.''),'Notice');
        //set the target directory
		$base_Dir = JPATH_SITE .DIRECTORY_SEPARATOR. 'tmp' . DS;
        $filepath = $base_Dir . 'sportsmanagement_import.jlg';
        if ( !copy($europalink,$filepath) )
{
$app->enqueueMessage(Text::_('daten -> '.$europalink.' konnten nicht kopiert werden!'),'Error');
}
else
{
$upload['name'] = $europalink;    
$app->setUserState('com_sportsmanagement'.'uploadArray',$upload); 
$app->setUserState('com_sportsmanagement'.'projectidimport',$projectid);     
$app->enqueueMessage(Text::_('daten -> '.$europalink.' sind kopiert worden!'),'Notice');    
}
        }
        else
        {
        // first step - upload
		if ( isset($post['sent']) && $post['sent'] == 1 )
		{
			$upload = $app->input->files->get('import_package');
			$tempFilePath = $upload['tmp_name'];
			$app->setUserState('com_sportsmanagement'.'uploadArray',$upload);
			$filename = '';
			$msg = '';
			$dest = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$upload['name'];
			$extractdir = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp';
			$importFile = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.jlg';
           
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
					   Log::add(Text::_(__METHOD__.' '.__LINE__.'-'.'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD'), Log::WARNING, 'jsmerror');
						return;
					}
					else
					{
						if ( strtolower(File::getExt($dest)) == 'zip' )
						{
							$result = JArchive::extract($dest,$extractdir);
							if ( $result === false )
							{
							 Log::add(Text::_(__METHOD__.' '.__LINE__.'-'.'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_ERROR'), Log::WARNING, 'jsmerror');
								return false;
							}
							File::delete($dest);
							$src = Folder::files($extractdir,'jlg',false,true);
							if(!count($src))
							{
							 Log::add(Text::_(__METHOD__.' '.__LINE__.'-'.'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_NOJLG'), Log::WARNING, 'jsmerror');
								return false;
							}
							if ( strtolower(File::getExt($src[0])) == 'jlg' )
							{
								if (!@ rename($src[0],$importFile))
								{
								    Log::add(Text::_(__METHOD__.' '.__LINE__.'-'.'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_ERROR_RENAME'), Log::WARNING, 'jsmerror');
									return false;
								}
							}
							else
							{
							 Log::add(Text::_(__METHOD__.' '.__LINE__.'-'.'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_TMP_DELETED'), Log::WARNING, 'jsmerror');
								return;
							}
						}
						else
						{
							if ( strtolower(File::getExt($dest)) == 'jlg' )
							{
								if (!@ rename($dest,$importFile))
								{
								    Log::add(Text::_(__METHOD__.' '.__LINE__.'-'.'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_RENAME_FAILED'), Log::WARNING, 'jsmerror');
									return false;
								}
							}
							else
							{
							 Log::add(Text::_(__METHOD__.' '.__LINE__.'-'.'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_WRONG_EXTENSION'), Log::WARNING, 'jsmerror');
								return false;
							}
						}
					}
			}
		}
        }
        
        if ( $post['importupdate'] )
        {
            $link='index.php?option=com_sportsmanagement&task=jlxmlimport.update&project_id='.$projectid;
        }
        else
        {
            $link='index.php?option=com_sportsmanagement&task=jlxmlimport.edit&project_id='.$projectid;
        }    
		$this->setRedirect($link,$msg);
	}

	/**
	 * sportsmanagementControllerJLXMLImport::insert()
	 * 
	 * @return void
	 */
	function insert()
	{
		ToolbarHelper::back(Text::_('JPREV'),Route::_('index.php?option=com_sportsmanagement'));
		$post = Factory::getApplication()->input->post->getArray(array());
		$link = 'index.php?option=com_sportsmanagement&task=jlxmlimport.insert';
		$this->setRedirect($link);
	}

	/**
	 * sportsmanagementControllerJLXMLImport::cancel()
	 * 
	 * @return void
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_sportsmanagement&task=jlxmlimport.display');
	}

}
?>
