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


/**
 * sportsmanagementViewTemplates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTemplates extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		$uri = JFactory::getURI();
        $model	= $this->getModel();
        
        
		//$templates =& $this->get('Data');
//		$total =& $this->get('Total');
//		$pagination =& $this->get('Pagination');
        
        $templates = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
		//$projectws =& $this->get('Data','projectws');
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
		
		//$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($project,true).'</pre>'),'');
        
		if ($project->master_template)
		{
			
            $model->set('_getALL',1);
			$allMasterTemplates = $model->getMasterTemplatesList();
			$model->set('_getALL',0);
			$masterTemplates = $model->getMasterTemplatesList();
			$importlist = array();
			$importlist[] = JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_SELECT_FROM_MASTER'));
			$importlist = array_merge($importlist,$masterTemplates);
			$lists['mastertemplates'] = JHtml::_('select.genericlist',$importlist,'templateid');
			$master = $model->getMasterName();
			$this->assign('master',$master);
			$templates = array_merge($templates,$allMasterTemplates);
            
		}

		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_filter_order','filter_order','tmpl.template','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'tmpl_search_mode','search_mode','','string');
		$search				= JString::strtolower($search);

		// state filter
		$lists['state']=JHtml::_('grid.state',$filter_state);

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
		$lists['search_mode']=$search_mode;

		$this->assign('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('templates',$templates);
		$this->assignRef('projectws',$project);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
		
		
		$this->addToolbar();			
		parent::display($tpl);
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
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TITLE'),'templates');
		JToolBarHelper::editList('template.edit');
		JToolBarHelper::save('template.save');
		if ($this->projectws->master_template)
		{
			JToolBarHelper::deleteList('','template.remove');
		}
		else
		{
			JToolBarHelper::custom('template.reset','restore','restore',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_RESET'));
		}
		JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}	
}
?>
