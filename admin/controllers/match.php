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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 

/**
 * sportsmanagementControllermatch
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllermatch extends JControllerForm
{


	/**
	 * sportsmanagementControllermatch::insertgooglecalendar()
	 * 
	 * @return void
	 */
	function insertgooglecalendar()
    {
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model = $this->getModel('match');
        $result = $model->insertgooglecalendar();
        
        if ( $result )
        {
            $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_GOOGLE_EVENT');
        }
        else
        {
            $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_NO_GOOGLECALENDAR_ID');
        }
        
        $link = 'index.php?option=com_sportsmanagement&view=matches';
		$this->setRedirect($link,$msg);
    }
    
    /**
	 * Method add a match to round
	 *
	 * @access	public
	 * @return	
	 * @since	0.1
	 */
    function addmatch()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$post = JRequest::get('post');
		$post['project_id'] = $mainframe->getUserState( "$option.pid", '0' );
		$post['round_id'] = $mainframe->getUserState( "$option.rid", '0' );
		$model = $this->getModel('match');
        $row = $model->getTable();
        // bind the form fields to the table
        if (!$row->bind($post)) {
        $this->setError($this->_db->getErrorMsg());
        return false;
        }
         // make sure the record is valid
        if (!$row->check()) {
        $this->setError($this->_db->getErrorMsg());
        return false;
        }
        // store to the database
		if ($row->store($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH').$model->getError();
		}
		$link = 'index.php?option=com_sportsmanagement&view=matches';
		$this->setRedirect($link,$msg);
	}
    
    
    
    /**
	 * Method to remove a matchday
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function remove()
	{
	$mainframe = JFactory::getApplication();
    $pks = JRequest::getVar('cid', array(), 'post', 'array');
    $model = $this->getModel('match');
    $model->delete($pks);
	
    $this->setRedirect('index.php?option=com_sportsmanagement&view=matches');    
        
   }
   
   
	/**
	 * sportsmanagementControllermatch::picture()
	 * 
	 * @return void
	 */
	function picture()
  {
  //$cid = JRequest::getVar('cid',array(0),'','array');
	$match_id = JRequest::getInt('id',0);
  $dest = JPATH_ROOT.'/images/com_sportsmanagement/database/matchreport/'.$match_id;
  $folder = 'matchreport/'.$match_id;
  //$this->setState('folder', $folder);
  if(JFolder::exists($dest)) {
  }
  else
  {
  JFolder::create($dest);
  }

  $msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_MATCHPICTURE');
  $link='index.php?option=com_media&view=images&tmpl=component&asset=com_sportsmanagement&author=&folder=com_sportsmanagement/database/'.$folder;
	$this->setRedirect($link,$msg);
  
  }
  
  /**
   * sportsmanagementControllermatch::readpressebericht()
   * 
   * @return void
   */
  function readpressebericht()
    {
    JRequest::setVar('hidemainmenu',1);
		JRequest::setVar('layout','readpressebericht');
		JRequest::setVar('view','match');
		JRequest::setVar('edit',true);

		
		parent::display();    
        
        
    }
    
    /**
     * sportsmanagementControllermatch::savepressebericht()
     * 
     * @return
     */
    function savepressebericht()
    {
    	// Check for request forgeries
		JRequest::checkToken() or die('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN');
		$msg='';
		JToolBarHelper::back(JText::_('JPREV'),JRoute::_('index.php?option=com_sportsmanagement&task=jlxmlimport.display'));
		$mainframe = JFactory::getApplication();
		$post=JRequest::get('post');
        $model = $this->getModel('match');

		// first step - upload
		if (isset($post['sent']) && $post['sent']==1)
		{
			$upload = JRequest::getVar('import_package',null,'files','array');
            //$cid = JRequest::getVar('cid',array(0),'','array');
            $match_id = JRequest::getInt('id',0);
			$tempFilePath = $upload['tmp_name'];
			$mainframe->setUserState('com_sportsmanagement'.'uploadArray',$upload);
			$filename = '';
			$msg = '';
			$dest = JPATH_SITE.DS.'tmp'.DS.$upload['name'];
			$extractdir = JPATH_SITE.DS.'tmp';
			//$importFile = JPATH_SITE.DS.'tmp'. DS.'pressebericht.jlg';
            $importFile = JPATH_SITE.DS.'media'.DS.'com_sportsmanagement'.DS.'pressebericht'.DS.$match_id.'.jlg';
            
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
							if (strtolower(JFile::getExt($dest))=='csv')
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
        //$csv_file = $model->getPressebericht();  
		$link='index.php?option=com_sportsmanagement&tmpl=component&task=match.readpressebericht&match_id='.$match_id;
		$this->setRedirect($link,$msg);    
        
        
    }
    
  /**
   * sportsmanagementControllermatch::savecsvpressebericht()
   * 
   * @return void
   */
  function savecsvpressebericht()
    {
    JRequest::setVar('hidemainmenu',1);
	JRequest::setVar('layout','savepressebericht');
	JRequest::setVar('view','match');
	JRequest::setVar('edit',true);
	
	parent::display();
    }
        
    /**
     * sportsmanagementControllermatch::pressebericht()
     * 
     * @return void
     */
    function pressebericht()
    {
    JRequest::setVar('hidemainmenu',1);
	JRequest::setVar('layout','pressebericht');
	JRequest::setVar('view','match');
	JRequest::setVar('edit',true);
	
	parent::display();    
        
    }
    
       
    

}
