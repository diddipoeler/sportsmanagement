<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlxmlimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
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
class sportsmanagementControllerJLXMLImport extends JControllerLegacy
{
	/**
	 * sportsmanagementControllerJLXMLImport::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
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
       
       //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getTask <br><pre>'.print_r($this->getTask(),true).'</pre>'),'');
       
		switch ($this->getTask())
		{
			case 'edit':
				//Factory::getApplication()->input->setVar('hidemainmenu',0);
				Factory::getApplication()->input->set('layout','form');
				Factory::getApplication()->input->set('view','jlxmlimports');
				Factory::getApplication()->input->set('edit',true);
				break;

			case 'insert':
				//Factory::getApplication()->input->setVar('hidemainmenu',0);
				Factory::getApplication()->input->set('layout','info');
				Factory::getApplication()->input->set('view','jlxmlimports');
				Factory::getApplication()->input->set('edit',true);
				break;
                
           case 'update':
				//Factory::getApplication()->input->setVar('hidemainmenu',0);
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
		$selectType=Factory::getApplication()->input->getVar('type',0,'get','int');
		$recordID=Factory::getApplication()->input->getVar('id',0,'get','int');
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
		JSession::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));
		$msg='';
		JToolbarHelper::back(Text::_('JPREV'),JRoute::_('index.php?option=com_sportsmanagement&task=jlxmlimport.display'));
		$app = Factory::getApplication();
		$post = Factory::getApplication()->input->post->getArray(array());
        
        $projectid = Factory::getApplication()->input->getVar('projektfussballineuropa',null);

		
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
		$base_Dir = JPATH_SITE . DS . 'tmp' . DS;
        $filepath = $base_Dir . 'joomleague_import.jlg';
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
		if (isset($post['sent']) && $post['sent']==1)
		{
			//$upload=Factory::getApplication()->input->getVar('import_package',null,'files','array');
			$upload = $app->input->files->get('import_package');
			$tempFilePath = $upload['tmp_name'];
			$app->setUserState('com_sportsmanagement'.'uploadArray',$upload);
			$filename = '';
			$msg = '';
			$dest = JPATH_SITE.DS.'tmp'.DS.$upload['name'];
			$extractdir = JPATH_SITE.DS.'tmp';
			$importFile = JPATH_SITE.DS.'tmp'. DS.'joomleague_import.jlg';
           
			if (JFile::exists($importFile))
			{
				JFile::delete($importFile);
			}
			if (JFile::exists($tempFilePath))
			{
					if (JFile::exists($dest))
					{
						JFile::delete($dest);
					}
					if (!JFile::upload($tempFilePath,$dest))
					{
						JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD'));
						return;
					}
					else
					{
						if (strtolower(JFile::getExt($dest))=='zip')
						{
							$result=JArchive::extract($dest,$extractdir);
							if ($result === false)
							{
								JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_ERROR'));
								return false;
							}
							JFile::delete($dest);
							$src=JFolder::files($extractdir,'jlg',false,true);
							if(!count($src))
							{
								JError::raiseWarning(500,'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_NOJLG');
								//todo: delete every extracted file / directory
								return false;
							}
							if (strtolower(JFile::getExt($src[0]))=='jlg')
							{
								if (!@ rename($src[0],$importFile))
								{
									JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_ERROR_RENAME'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(500,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_TMP_DELETED'));
								return;
							}
						}
						else
						{
							if (strtolower(JFile::getExt($dest))=='jlg')
							{
								if (!@ rename($dest,$importFile))
								{
									JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_RENAME_FAILED'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(21,Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_WRONG_EXTENSION'));
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
		JToolbarHelper::back(Text::_('JPREV'),JRoute::_('index.php?option=com_sportsmanagement'));
		$post=Factory::getApplication()->input->post->getArray(array());

		$link='index.php?option=com_sportsmanagement&task=jlxmlimport.insert';
		//echo $link;
        //$this->setRedirect('index.php?option=com_sportsmanagement&task=jlxmlimport.display');

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
