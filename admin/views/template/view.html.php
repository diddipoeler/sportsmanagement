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
class sportsmanagementViewTemplate extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTemplate::init()
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
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
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
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$script = $this->get('Script');
 
 /*       
        if (is_array($item->params))
        {
        $app->enqueueMessage(JText::_('sportsmanagementViewTemplate params<br><pre>'.print_r($item->params,true).'</pre>'),'Error');  
        $item->params = json_encode($item->params,true);  
        $app->enqueueMessage(JText::_('sportsmanagementViewTemplate new params<br><pre>'.print_r($item->params,true).'</pre>'),'Error');
        }
 */
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
        $this->project_id = $app->getUserState( "$option.pid", '0' );
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        
        
        $templatepath = JPATH_COMPONENT_SITE.DS.'settings';
        $xmlfile = $templatepath.DS.'default'.DS.$item->template.'.xml';
        
        //$app->enqueueMessage(JText::_('sportsmanagementViewTemplate xmlfile<br><pre>'.print_r($xmlfile,true).'</pre>'),'Notice');
        
        $form = JForm::getInstance($item->template, $xmlfile,array('control'=> 'params'));
		//$form->bind($jRegistry);
        $form->bind($item->params);
      
        $this->item = $item;
        // Assign the Data
		$this->form = $form;
		//$this->item = $item;
		$this->script = $script;
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->form->getName(),true).'</pre>'),'Notice');
        
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
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($teile,true).'</pre>'),'Notice');
            foreach($teile as $key => $value )
            {
            $teile2 = explode(",",$value);      
            list($colors_ranking[$count]['von'], $colors_ranking[$count]['bis'], $colors_ranking[$count]['color'], $colors_ranking[$count]['text'] ) = $teile2;  
            $count++;  
            }
            $this->form->setValue('colors_ranking',null,$colors_ranking);
            }
            //$this->form->setFieldAttribute('colors_ranking','default' , $iProjectTeamsCount);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($colors,true).'</pre>'),'Notice');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($colors_ranking,true).'</pre>'),'Notice');
            
            break;
        }

		$master_id = ($project->master_template) ? $project->master_template : '-1';
        $templates=array();
        $res = $model->getAllTemplatesList($project->id,$master_id);
        $templates=array_merge($templates,$res);
        $lists['templates'] = JHtml::_('select.genericlist',$templates,
														'new_id',
														'class="inputbox" size="1" onchange="javascript: Joomla.submitbutton(\'templates.changetemplate\');"',
														'value',
														'text',
														$item->id);
        
        
        $this->assign('request_url',$uri->toString());
		$this->assignRef('template',$item);
        
        $this->assign('templatename',$this->form->getName());
		$this->assignRef('project',$project);
		$this->assignRef('lists',$lists);
		$this->assignRef('user',$user);
        
        // Load the language files for the contact integration
		$jlang = JFactory::getLanguage();
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, null, true);

	}
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	
        JRequest::setVar('hidemainmenu', true);
        JRequest::setVar('pid', $this->project_id);
        $this->item->name = $this->item->template;
		//$isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_NEW');
        //$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_EDIT');
        $this->title = JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_EDIT',(JText::_($this->item->title)));
        $this->icon = 'template';
        
        parent::addToolbar();
	}
    

    		

}
?>
