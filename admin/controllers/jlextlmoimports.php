<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextlmoimports.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
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
class sportsmanagementControllerjlextlmoimports extends JControllerLegacy
{
	
	/**
	 * sportsmanagementControllerjlextlmoimports::save()
	 * 
	 * @return
	 */
	function save()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        // Check for request forgeries
		JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));
		$msg = '';
		JToolbarHelper::back(JText::_('JPREV'),JRoute::_('index.php?option=com_sportsmanagement&view=jllmoimport&controller=jllmoimport'));
		$app = JFactory::getApplication();
		$post = JFactory::getApplication()->input->post->getArray(array());
    $model = $this->getModel('jlextlmoimports');
    
		// first step - upload
		if (isset($post['sent']) && $post['sent']==1)
		{
			//$upload=JFactory::getApplication()->input->getVar('import_package',null,'files','array');
$upload = $app->input->files->get('import_package');

			$lmoimportuseteams = JFactory::getApplication()->input->getVar('lmoimportuseteams',null);
			$app->setUserState($option.'lmoimportuseteams',$lmoimportuseteams);
			
			$tempFilePath = $upload['tmp_name'];
			$app->setUserState($option.'uploadArray',$upload);
			$filename='';
			$msg='';
			$dest=JPATH_SITE.DS.'tmp'.DS.$upload['name'];
			$extractdir=JPATH_SITE.DS.'tmp';
			$importFile=JPATH_SITE.DS.'tmp'. DS.'joomleague_import.l98';
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
						JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_CANT_UPLOAD'));
						return;
					}
					else
					{
						if (strtolower(JFile::getExt($dest))=='zip')
						{
							$result=JArchive::extract($dest,$extractdir);
							if ($result === false)
							{
								JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_EXTRACT_ERROR'));
								return false;
							}
							JFile::delete($dest);
							$src=JFolder::files($extractdir,'l98',false,true);
							if(!count($src))
							{
								JError::raiseWarning(500,'COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_EXTRACT_NOJLG');
								//todo: delete every extracted file / directory
								return false;
							}
							if (strtolower(JFile::getExt($src[0]))=='l98')
							{
								if (!@ rename($src[0],$importFile))
								{
									JError::raiseWarning(21,JText::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_ERROR_RENAME'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_TMP_DELETED'));
								return;
							}
						}
						else
						{
							if (strtolower(JFile::getExt($dest))=='l98')
							{
								if (!@ rename($dest,$importFile))
								{
									JError::raiseWarning(21,JText::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_RENAME_FAILED'));
									return false;
								}
							}
							else
							{
								JError::raiseWarning(21,JText::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_CTRL_WRONG_EXTENSION'));
								return false;
							}
						}
					}
			}
		}
    $model->getData();
		//$link='index.php?option='.$option.'&task=jlextlmoimports.edit';
        $link = 'index.php?option='.$option.'&view=jlxmlimports&task=jlxmlimport.edit';
		#echo '<br />Message: '.$msg.'<br />';
		#echo '<br />Redirect-Link: '.$link.'<br />';
		$this->setRedirect($link,$msg);
	}










}


?>
