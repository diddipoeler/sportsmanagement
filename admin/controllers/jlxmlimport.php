<?php
/**
* @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
* @license		GNU/GPL,see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License,and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

/**
 * Joomleague Component JLXMLImport Controller
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5.0a
 */
class sportsmanagementControllerJLXMLImport extends JController
{
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('edit','display');
		$this->registerTask('insert','display');
		$this->registerTask('selectpage','display');
	}

	public function display($cachable = false, $urlparams = false)
	{
		switch ($this->getTask())
		{
			case 'edit':
				//JRequest::setVar('hidemainmenu',0);
				JRequest::setVar('layout','form');
				JRequest::setVar('view','jlxmlimports');
				JRequest::setVar('edit',true);
				break;

			case 'insert':
				//JRequest::setVar('hidemainmenu',0);
				JRequest::setVar('layout','info');
				JRequest::setVar('view','jlxmlimports');
				JRequest::setVar('edit',true);
				break;
                
           case 'update':
				//JRequest::setVar('hidemainmenu',0);
				JRequest::setVar('layout','update');
				JRequest::setVar('view','jlxmlimports');
				JRequest::setVar('edit',true);
				break;     
		}

		parent::display($cachable = false, $urlparams = false);
	}

	function select()
	{
		$mainframe = JFactory::getApplication();
		$selectType=JRequest::getVar('type',0,'get','int');
		$recordID=JRequest::getVar('id',0,'get','int');
		$mainframe->setUserState('com_sportsmanagement'.'selectType',$selectType);
		$mainframe->setUserState('com_sportsmanagement'.'recordID',$recordID);

		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar('layout','selectpage');
		JRequest::setVar('view','jlxmlimports');

		parent::display();
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('JINVALID_TOKEN');
		$msg='';
		JToolBarHelper::back(JText::_('JPREV'),JRoute::_('index.php?option=com_sportsmanagement&task=jlxmlimport.display'));
		$mainframe = JFactory::getApplication();
		$post=JRequest::get('post');
        
        $projectid = JRequest::getVar('projektfussballineuropa',null);

		
        if ( $projectid )
    {
        
        if ( $post['importupdate'] )
        {
        $europalink = "http://www.fussballineuropa.de/index.php?option=com_joomleague&view=jlxmlexports&p=".$projectid."&update=1";    
        }
        else
        {
        $europalink = "http://www.fussballineuropa.de/index.php?option=com_joomleague&view=jlxmlexports&p=".$projectid."&update=0";    
        }
        
        $mainframe->enqueueMessage(JText::_('hole daten von -> '.$europalink.''),'Notice');
        //set the target directory
		$base_Dir = JPATH_SITE . DS . 'tmp' . DS;
        $filepath = $base_Dir . 'joomleague_import.jlg';
        if ( !copy($europalink,$filepath) )
{
$mainframe->enqueueMessage(JText::_('daten -> '.$europalink.' konnten nicht kopiert werden!'),'Error');
}
else
{
$upload['name'] = $europalink;    
$mainframe->setUserState('com_sportsmanagement'.'uploadArray',$upload); 
$mainframe->setUserState('com_sportsmanagement'.'projectidimport',$projectid);     
$mainframe->enqueueMessage(JText::_('daten -> '.$europalink.' sind kopiert worden!'),'Notice');    
}
        }
        else
        {
        // first step - upload
		if (isset($post['sent']) && $post['sent']==1)
		{
			$upload=JRequest::getVar('import_package',null,'files','array');
			$tempFilePath=$upload['tmp_name'];
			$mainframe->setUserState('com_sportsmanagement'.'uploadArray',$upload);
			$filename='';
			$msg='';
			$dest=JPATH_SITE.DS.'tmp'.DS.$upload['name'];
			$extractdir=JPATH_SITE.DS.'tmp';
			$importFile=JPATH_SITE.DS.'tmp'. DS.'joomleague_import.jlg';
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
						JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD'));
						return;
					}
					else
					{
						if (strtolower(JFile::getExt($dest))=='zip')
						{
							$result=JArchive::extract($dest,$extractdir);
							if ($result === false)
							{
								JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_ERROR'));
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
									JError::raiseWarning(21,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_ERROR_RENAME'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_TMP_DELETED'));
								return;
							}
						}
						else
						{
							if (strtolower(JFile::getExt($dest))=='jlg')
							{
								if (!@ rename($dest,$importFile))
								{
									JError::raiseWarning(21,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_RENAME_FAILED'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(21,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_WRONG_EXTENSION'));
								return false;
							}
						}
					}
			}
		}
        }
        
        if ( $post['importupdate'] )
        {
            $link='index.php?option=com_sportsmanagement&task=jlxmlimport.update';
        }
        else
        {
            $link='index.php?option=com_sportsmanagement&task=jlxmlimport.edit';
        }    
		//$link='index.php?option=com_joomleague&task=jlxmlimport.edit';
		$this->setRedirect($link,$msg);
	}

	function insert()
	{
		JToolBarHelper::back(JText::_('JPREV'),JRoute::_('index.php?option=com_sportsmanagement'));
		$post=JRequest::get('post');

		$link='index.php?option=com_sportsmanagement&task=jlxmlimport.insert';
		echo $link;

		#$this->setRedirect($link);
	}

	function cancel()
	{
		$this->setRedirect('index.php?option=com_sportsmanagement&task=jlxmlimport.display');
	}

}
?>