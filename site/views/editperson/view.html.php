<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');


/**
 * sportsmanagementViewEditPerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewEditPerson extends JViewLegacy
{
	/**
	 * sportsmanagementViewEditPerson::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		
		$option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
        
        $params         = $app->getParams();
        $dispatcher = JDispatcher::getInstance();
        
        // Get some data from the models
        $state          = $this->get('State');
        $this->item           = $this->get('Item');
        $this->form     = $this->get('Form');
        
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('position_id', 'request', $this->item->position_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
        $this->form->setValue('person_art', 'request', $this->item->person_art);
        $this->form->setValue('person_id1', 'request', $this->item->person_id1);
        $this->form->setValue('person_id2', 'request', $this->item->person_id2);
        
        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'person');
		$this->assignRef( 'extended', $extended ); 
        
        $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields('frontend',$model::$cfg_which_database) );
        if ( $this->checkextrafields )
        {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id,'frontend',$model::$cfg_which_database);
        }
               
 
                // Check for errors.
                if (count($errors = $this->get('Errors'))) 
                {
                        JError::raiseError(500, implode('<br />', $errors));
                        return false;
                }


/*                
		//$model = $this->getModel();
		//$edit = JRequest::getVar('edit',true);
		
		//$this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_joomleague')->get('cfg_which_media_tool',0) );
            
		$lists = array();

		//get the person
		$person = $this->get('Data');
		$isNew = ($person->id < 1);

//    // fail if checked out not by 'me'
//		if ($model->isCheckedOut($user->get('id')))
//		{
//			$msg=JText::sprintf('DESCBEINGEDITTED',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_THEPERSON'),$person->name);
//			$app->redirect('index.php?option='.$option,$msg);
//		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout($user->get('id'));
		}
		else
		{
			$person->ordering = 0;
		}


		$this->assignRef('form'	, $this->get('form'));	
		$this->assignRef('edit',$edit);
		$extended = sportsmanagementHelper::getExtended($person->extended, 'person');		
		$this->assignRef( 'extended', $extended );
		//$this->assignRef('lists',$lists);
		$this->assignRef('person',$person);
*/

		//$this->addToolbar();			
		parent::display($tpl);
	}

//	function _displayModal($tpl)
//	{
//		$app	= JFactory::getApplication();
//
//		// Do not allow cache
//		JResponse::allowCache(false);
//
//		$document = JFactory::getDocument();
//		$prjid=array();
//		$prjid=JRequest::getVar('prjid',array(0),'post','array');
//		$proj_id=(int) $prjid[0];
//
//		//build the html select list for projects
//		$projects[]=JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PROJECT'),'id','name');
//
//		if ($res=JoomleagueHelper::getProjects()){$projects=array_merge($projects,$res);}
//		$lists['projects']=JHTMLSelect::genericlist(	$projects,
//														'prjid[]',
//														'class="inputbox" onChange="this.form.submit();" style="width:170px"',
//														'id',
//														'name',
//														$proj_id);
//		unset($projects);
//
//		$projectteams[]=JHTMLSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'),'value','text');
//
//		// if a project is active we show the teams select list
//		if ($proj_id > 0)
//		{
//			if ($res=JoomleagueHelper::getProjectteams($proj_id)){$projectteams=array_merge($projectteams,$res);}
//			$lists['projectteams']=JHTMLSelect::genericlist($projectteams,'xtid[]','class="inputbox" style="width:170px"','value','text');
//			unset($projectteams);
//		}
//
//		$this->assignRef('lists',$lists);
//		$this->assignRef('project_id',$proj_id);
//
//		parent::display($tpl);
//	}
    
//	/**
//	* Add the page title and toolbar.
//	*
//	* @since	1.7
//	*/
//	protected function addToolbar()
//	{	
//		// Set toolbar items for the page
//		$text = !$this->edit ? JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NEW') : JText::_('COM_SPORTSMANAGEMENT_GLOBAL_EDIT');
//
//		JLToolBarHelper::save('person.save');
//
//		if (!$this->edit)
//		{
//			JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_TITLE'));
//			JToolBarHelper::divider();
//			JLToolBarHelper::cancel('person.cancel');
//		}
//		else
//		{
//			// for existing items the button is renamed `close` and the apply button is showed
//			JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_TITLE2'));
//			JLToolBarHelper::apply('person.apply');
//			JToolBarHelper::divider();
//			JLToolBarHelper::cancel('person.cancel',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOSE'));
//		}
//		JToolBarHelper::divider();
//		JToolBarHelper::back();
//		JLToolBarHelper::onlinehelp();
//	}		

}
?>