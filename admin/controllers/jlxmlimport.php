<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

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
	   $app = JFactory::getApplication();
       
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask <br><pre>'.print_r($this->getTask(),true).'</pre>'),'');
       
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

	/**
	 * sportsmanagementControllerJLXMLImport::select()
	 * 
	 * @return void
	 */
	function select()
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$selectType=JRequest::getVar('type',0,'get','int');
		$recordID=JRequest::getVar('id',0,'get','int');
		$app->setUserState($option.'selectType',$selectType);
		$app->setUserState($option.'recordID',$recordID);

		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar('layout','selectpage');
		JRequest::setVar('view','jlxmlimports');

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
		JRequest::checkToken() or die('JINVALID_TOKEN');
		$msg='';
		JToolBarHelper::back(JText::_('JPREV'),JRoute::_('index.php?option=com_sportsmanagement&task=jlxmlimport.display'));
		$app = JFactory::getApplication();
		$post=JRequest::get('post');
        
        $projectid = JRequest::getVar('projektfussballineuropa',null);

		
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
        
        $app->enqueueMessage(JText::_('hole daten von -> '.$europalink.''),'Notice');
        //set the target directory
		$base_Dir = JPATH_SITE . DS . 'tmp' . DS;
        $filepath = $base_Dir . 'joomleague_import.jlg';
        if ( !copy($europalink,$filepath) )
{
$app->enqueueMessage(JText::_('daten -> '.$europalink.' konnten nicht kopiert werden!'),'Error');
}
else
{
$upload['name'] = $europalink;    
$app->setUserState('com_sportsmanagement'.'uploadArray',$upload); 
$app->setUserState('com_sportsmanagement'.'projectidimport',$projectid);     
$app->enqueueMessage(JText::_('daten -> '.$europalink.' sind kopiert worden!'),'Notice');    
}
        }
        else
        {
        // first step - upload
		if (isset($post['sent']) && $post['sent']==1)
		{
			$upload=JRequest::getVar('import_package',null,'files','array');
			$tempFilePath=$upload['tmp_name'];
			$app->setUserState('com_sportsmanagement'.'uploadArray',$upload);
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
            $link='index.php?option=com_sportsmanagement&task=jlxmlimport.update&project_id='.$projectid;
        }
        else
        {
            $link='index.php?option=com_sportsmanagement&task=jlxmlimport.edit&project_id='.$projectid;
        }    
		//$link='index.php?option=com_joomleague&task=jlxmlimport.edit';
		$this->setRedirect($link,$msg);
	}

	/**
	 * sportsmanagementControllerJLXMLImport::insert()
	 * 
	 * @return void
	 */
	function insert()
	{
		JToolBarHelper::back(JText::_('JPREV'),JRoute::_('index.php?option=com_sportsmanagement'));
		$post=JRequest::get('post');

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