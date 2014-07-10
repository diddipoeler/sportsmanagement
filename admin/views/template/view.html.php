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

jimport('joomla.application.component.view');
jimport('joomla.form.form');


/**
 * sportsmanagementViewTemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTemplate extends JViewLegacy
{
	/**
	 * sportsmanagementViewTemplate::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$model = $this->getModel();
		$lists = array();
        $starttime = microtime(); 

		//get template data
		//$template =& $this->get('data');
		//$isNew=($template->id < 1);
        
        // get the Data
		//$form = $this->get('Form');
		$item = $this->get('Item');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$script = $this->get('Script');
 
 /*       
        if (is_array($item->params))
        {
        $mainframe->enqueueMessage(JText::_('sportsmanagementViewTemplate params<br><pre>'.print_r($item->params,true).'</pre>'),'Error');  
        $item->params = json_encode($item->params,true);  
        $mainframe->enqueueMessage(JText::_('sportsmanagementViewTemplate new params<br><pre>'.print_r($item->params,true).'</pre>'),'Error');
        }
 */
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        
        
        $templatepath = JPATH_COMPONENT_SITE.DS.'settings';
        $xmlfile = $templatepath.DS.'default'.DS.$item->template.'.xml';
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewTemplate xmlfile<br><pre>'.print_r($xmlfile,true).'</pre>'),'Notice');
        
        $form = JForm::getInstance($item->template, $xmlfile,array('control'=> 'params'));
		//$form->bind($jRegistry);
        $form->bind($item->params);
      
        
        // Assign the Data
		$this->form = $form;
		//$this->item = $item;
		$this->script = $script;
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->form->getName(),true).'</pre>'),'Notice');
        
        switch ( $this->form->getName() )
        {
            case 'ranking':
            $mdlProjecteams = JModelLegacy::getInstance("Projectteams", "sportsmanagementModel");
	        $iProjectTeamsCount = $mdlProjecteams->getProjectTeamsCount($this->project_id);
            $this->assignRef('teamscount',$iProjectTeamsCount);
            $this->form->setFieldAttribute('colors_ranking','rankingteams' , $iProjectTeamsCount);
            $this->form->setFieldAttribute('colors','type' , 'hidden');
            
            $colors = $this->form->getValue('colors');
            $colors_ranking = $this->form->getValue('colors_ranking');
            if ( empty($colors_ranking[1]['von']) )
            {
            $count = 1;    
            $teile = explode(";",$colors);    
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($teile,true).'</pre>'),'Notice');
            foreach($teile as $key => $value )
            {
            $teile2 = explode(",",$value);      
            list($colors_ranking[$count]['von'], $colors_ranking[$count]['bis'], $colors_ranking[$count]['color'], $colors_ranking[$count]['text'] ) = $teile2;  
            $count++;  
            }
            $this->form->setValue('colors_ranking',null,$colors_ranking);
            }
            //$this->form->setFieldAttribute('colors_ranking','default' , $iProjectTeamsCount);
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($colors,true).'</pre>'),'Notice');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($colors_ranking,true).'</pre>'),'Notice');
            
            break;
        }

		$master_id = ($project->master_template) ? $project->master_template : '-1';
        $templates=array();
        $res = $model->getAllTemplatesList($project->id,$master_id);
        $templates=array_merge($templates,$res);
        $lists['templates'] = JHtml::_('select.genericlist',	$templates,
														'new_id',
														'class="inputbox" size="1" onchange="javascript: Joomla.submitbutton(\'templates.changetemplate\');"',
														'value',
														'text',
														$item->id);
        
        
        $this->assign('request_url',$uri->toString());
		$this->assignRef('template',$item);
        
        
        
        $this->assignRef('templatename',$this->form->getName());
		$this->assignRef('project',$project);
		$this->assignRef('lists',$lists);
		$this->assignRef('user',$user);

		// Set the toolbar
		$this->addToolBar();
		// Display the template
		parent::display($tpl);
        // Set the document
		$this->setDocument();
	}
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JUri::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		
        JRequest::setVar('hidemainmenu', true);
        JRequest::setVar('pid', $this->project_id);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->template->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->template->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_NEW') : JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_EDIT'), 'template');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('template.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('template.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('template.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('template.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('template.apply', 'JTOOLBAR_APPLY');
                //JToolBarHelper::apply('templates.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('template.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('template.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('template.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
		}
        
        JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
        
        
	}
    
    /**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$isNew = $this->template->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_NEW') : JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_EDIT'));
		$document->addScript(JUri::root() . $this->script);
		$document->addScript(JUri::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
        //$document->addScript(JUri::root() . "/administrator/components/com_sportsmanagement/assets/js/jscolor/jscolor.js");
        $document->addScript(JUri::root() . "/administrator/components/com_sportsmanagement/assets/js/jscolor/jscolor.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
    		

}
?>
