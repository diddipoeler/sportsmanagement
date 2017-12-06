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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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

jimport('joomla.application.component.view');



/**
 * sportsmanagementViewpredictionproject
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewpredictionproject extends sportsmanagementView
{
	/**
	 * sportsmanagementViewpredictionproject::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		// Reference global application object
		$app = JFactory::getApplication();
        // JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
		$model	= $this->getModel();
        
//        $project_id = JFactory::getApplication()->input->getVar('project_id');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__  .' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
        
//        $id = JFactory::getApplication()->input->getVar('id');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__  .' id<br><pre>'.print_r($id,true).'</pre>'),'');

        
 /*       
        if ($this->getLayout()=='form')
		{
			$this->_displayForm($tpl);
			return;
		}
		elseif ($this->getLayout()=='predsettings')
		{
			$this->_displayPredSettings($tpl);
			return;
		}
*/
		
        
        
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
        
        //$pred_admins = $model->getAdmins($item->id);
		//$pred_projects = $model->getPredictionProjectIDs($item->id);
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->item->name = '';
		$this->script = $script;
		
		$app->setUserState( "$option.pid", $this->item->project_id );
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__  .' item<br><pre>'.print_r($this->item,true).'</pre>'),'');
		
 
		// Set the document
		$this->setDocument();
    
	}

//	function _displayForm($tpl)
//	{
//	  
//		$option = JFactory::getApplication()->input->getCmd('option');
//		$app = JFactory::getApplication();
//		$uri 	= JFactory::getURI();
//		$user 	= JFactory::getUser();
//		$model	= $this->getModel();
//		
//		
//		$lists=array();
//
//		//get the prediction game and its admins
//		$prediction =& $this->get('data');
//		//$this->assignRef('prediction',$prediction);
//		$pred_admins=$model->getAdmins();
//		$pred_projects=$model->getPredictionProjectIDs();
//
//		$isNew=($prediction->id < 1);
//
//		// fail if checked out not by 'me'
//		if ($model->isCheckedOut($user->get('id')))
//		{
//			$msg=JText::sprintf('DESCBEINGEDITTED',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_THE_PREDICTIONGAME'),$prediction->name);
//			$app->redirect('index.php?option='.$option,$msg);
//		}
//
//		// Edit or Create?
//		if (!$isNew){$model->checkout($user->get('id'));}
//
//		//build the html select list for Joomla users
//		$jl_users[]=array();
//		if ($res =& $model->getJLUsers()){$jl_users=array_merge($jl_users,$res);}
//		$lists['jl_users']=JHtmlSelect::genericlist(	$res,
//														'user_ids[]',
//														'class="inputbox validate-select-required" size="5" multiple="multiple"',
//														'value',
//														'text',
//														$pred_admins);
//		unset($jl_users);
//
//		//build the html select list for projects
//		$projects[]=array();
//		if ($res =& $model->getProjects()){$projects=array_merge($projects,$res);}
//		$lists['projects']=JHtmlSelect::genericlist(	$res,
//														'project_ids[]',
//														'class="inputbox validate-select-required" size="5" multiple="multiple"',
//														'value',
//														'text',
//														$pred_projects);
//		#echo '<pre>'.print_r($projects,true).'</pre>';
//		unset($res);
//
//		// build the html radio for auto_activate_user
//		$lists['auto_activate_user']=JHtmlSelect::booleanlist('auto_approve','class="inputbox"',$prediction->auto_approve);
//
//		// build the html radio for only_favteams
//		$lists['only_favteams']=JHtmlSelect::booleanlist('only_favteams','class="inputbox"',$prediction->only_favteams);
//
//		// build the html radio for admin_tipp
//		$lists['admin_tipp']=JHtmlSelect::booleanlist('admin_tipp','class="inputbox"',$prediction->admin_tipp);
//
//    $this->assignRef('form'      	, $this->get('form'));
//    
//		$this->assignRef('lists',$lists);
//		$this->assignRef('prediction',$prediction);
//		$this->assignRef('pred_admins',$pred_admins);
//		$this->assignRef('pred_projects',$pred_projects);
//
//		parent::display($tpl);
//	}

//	function _displayPredSettings($tpl)
//	{
//		$option = JFactory::getApplication()->input->getCmd('option');
//		$app =& JFactory::getApplication();
//		$db =& sportsmanagementHelper::getDBConnection();
//		$uri =& JFactory::getURI();
//		$user =& JFactory::getUser();
//		$model =& $this->getModel();
//		$lists=array();
//
//		//get the prediction game and the predicition project
//		$prediction =& $this->get('data');
//		$pred_project=$model->getPredictionProject();
//
//		$isNew=($prediction->id < 1);
//
//		// fail if checked out not by 'me'
//		if ($model->isCheckedOut($user->get('id')))
//		{
//			$msg=JText::sprintf('DESCBEINGEDITTED',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_THE_PREDICTIONGAME'),$pred_project->project_name);
//			$app->redirect('index.php?option='.$option,$msg);
//		}
//
//		// Edit or Create?
//		if (!$isNew){$model->checkout($user->get('id'));}
//
//		// build the html radio for usage of published
//		$lists['published']=JHtml::_('select.booleanlist','published','class="inputbox" onclick="change_published(); " ',$pred_project->published);
//
//		// build the html dropdown for Prediction game mode
//		$mode=array(	JHtmlSelect::option('1',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_PRED_TOTO'),'id','name'),
//						JHtmlSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_PRED_TIPP'),'id','name'));
//		$lists['mode']=JHtmlSelect::genericlist($mode,'mode','class="inputbox" size="1" disabled="disabled" ','id','name',$pred_project->mode);
//		unset($mode);
//
//		// build the html dropdown for Prediction game mode
//		$overview=array(	JHtmlSelect::option('1',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_TIPP_HALF'),'id','name'),
//							JHtmlSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_TIPP_COMPLETE'),'id','name'));
//		$lists['overview']=JHtmlSelect::genericlist($overview,'overview','class="inputbox" size="1" disabled="disabled" ','id','name',$pred_project->overview);
//		unset($overview);
//
//		// build the html radio for usage of tipp joker
//		$lists['use_joker']=JHtmlSelect::booleanlist('joker','class="inputbox" onclick="change_joker(); " disabled="disabled" ',$pred_project->joker);
//
//		// build the html radio for limitation of tipp joker
//		$joker_limit=($pred_project->joker_limit > 0);
//		$lists['joker_limit']=JHtmlSelect::booleanlist('joker_limit_select','class="inputbox" onclick="change_jokerlimit(); " disabled="disabled" ',$joker_limit);
//
//		// build the html radio for usage of tipp champ
//		$lists['use_champ']=JHtmlSelect::booleanlist('champ','class="inputbox" onclick="change_champ(); " disabled="disabled" ',$pred_project->champ);
//
//    
//    
//    $league_teams[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_SET_CHAMPION'),'id','name');
//		if($allLeagues =& $model->getProjectTeams($pred_project->project_id)) 
//    {
//			$league_teams=array_merge($league_teams,$allLeagues);
//		}
//		$lists['league_teams']=JHtmlSelect::genericlist($league_teams,'league_champ','class="inputbox" size="'.sizeof($allLeagues).'"','id','name',$pred_project->league_champ);                            
//
//		#echo '<pre>'.print_r($projects,true).'</pre>';
//		unset($allLeagues);
//    
//    
//		$this->assignRef('lists',$lists);
//		$this->assignRef('prediction',$prediction);
//		$this->assignRef('pred_project',$pred_project);
//		parent::display($tpl);
//	}
    
    
//    /**
//	 * Setting the toolbar
//	 */
//	protected function addToolBar() 
//	{
//	// Get a refrence of the page instance in joomla
//        $document = JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
//		JFactory::getApplication()->input->setVar('hidemainmenu', true);
//		$user = JFactory::getUser();
//		$userId = $user->id;
//		$isNew = $this->item->id == 0;
//		$canDo = sportsmanagementHelper::getActions($this->item->id);
//		JToolbarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_PREDGAME_NEW') : JText::_('COM_SPORTSMANAGEMENT_PREDGAME_EDIT'), 'predproject');
//		// Built the actions for new and existing records.
//		if ($isNew) 
//		{
//			// For new records, check the create permission.
//			if ($canDo->get('core.create')) 
//			{
//				JToolbarHelper::apply('predictiongame.apply', 'JTOOLBAR_APPLY');
//				JToolbarHelper::save('predictiongame.save', 'JTOOLBAR_SAVE');
//				JToolbarHelper::custom('predictiongame.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
//			}
//			JToolbarHelper::cancel('predictiongame.cancel', 'JTOOLBAR_CANCEL');
//		}
//		else
//		{
//			if ($canDo->get('core.edit'))
//			{
//				// We can save the new record
//				JToolbarHelper::apply('predictiongame.apply', 'JTOOLBAR_APPLY');
//				JToolbarHelper::save('predictiongame.save', 'JTOOLBAR_SAVE');
// 
//				// We can save this record, but check the create permission to see if we can return to make a new one.
//				if ($canDo->get('core.create')) 
//				{
//					JToolbarHelper::custom('predictiongame.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
//				}
//			}
//			if ($canDo->get('core.create')) 
//			{
//				JToolbarHelper::custom('predictiongame.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
//			}
//			JToolbarHelper::cancel('predictiongame.cancel', 'JTOOLBAR_CLOSE');
//		}
//	}
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$isNew = $this->item->id == 0;
        //$this->name = $this->item->name;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : JText::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
    
}
?>
