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
 * sportsmanagementViewProjects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewProjects extends JView
{
	function display($tpl=null)
	{
		$option 	= JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$uri		= JFactory::getUri();
        $model	= $this->getModel();
        
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');

//		$filter_league		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_league','filter_league','','int');
//		$filter_sports_type	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_sports_type','filter_sports_type','','int');
//		$filter_season		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_season','filter_season','','int');
//		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_state','filter_state','','word');
//		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order','filter_order','p.ordering','cmd');
//		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
//		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.search','search','','string');
//		$search=JString::strtolower($search);
		
		// Get data from the model
		$items		= $this->get('Items');
		$total		= $this->get('Total');
		$pagination = $this->get('Pagination');
		$javascript = "onchange=\"$('adminForm').submit();\"";

//		// state filter
//		$lists['state'] = JHtml::_('grid.state',$filter_state);
//
//		// table ordering
//		$lists['order_Dir'] = $filter_order_Dir;
//		$lists['order'] = $filter_order;
//
//		// search filter
//		$lists['search'] = $search;

		//build the html select list for leagues
		$leagues[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUES_FILTER'),'id','name');
		$mdlLeagues = JModel::getInstance('Leagues','sportsmanagementModel');
		$allLeagues = $mdlLeagues->getLeagues();
		$leagues=array_merge($leagues,$allLeagues);
		$lists['leagues']=JHtml::_( 'select.genericList',
									$leagues,
									'filter_league',
									'class="inputbox" onChange="this.form.submit();" style="width:120px"',
									'id',
									'name',
									$this->state->get('filter.league'));
		unset($leagues);
		
		
		//build the html select list for sportstypes
		$sportstypes[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'),'id','name');
		$mdlSportsTypes = JModel::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes = array_merge($sportstypes,$allSportstypes);
		$lists['sportstypes']=JHtml::_( 'select.genericList',
										$sportstypes,
										'filter_sports_type',
										'class="inputbox" onChange="this.form.submit();" style="width:120px"',
										'id',
										'name',
										$this->state->get('filter.sports_type'));
		unset($sportstypes);
		
		
		//build the html select list for seasons
		$seasons[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'),'id','name');
        $mdlSeasons = JModel::getInstance('Seasons','sportsmanagementModel');
		$allSeasons= $mdlSeasons->getSeasons();
		$seasons=array_merge($seasons,$allSeasons);
        
		$lists['seasons']=JHtml::_( 'select.genericList',
									$seasons,
									'filter_season',
									'class="inputbox" onChange="this.form.submit();" style="width:120px"',
									'id',
									'name',
									$this->state->get('filter.season'));

		unset($seasons);
        
		$user = JFactory::getUser();
		$this->assignRef('user',  $user);
		$this->assignRef('lists', $lists);
		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);
		$url=$uri->toString();
		$this->assignRef('request_url',$url);
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolbar()
	{
	// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE'),'projects');
		JToolBarHelper::publishList('project.publish');
		JToolBarHelper::unpublishList('project.unpublish');
		JToolBarHelper::divider();
		
		JToolBarHelper::addNew('project.add');
		JToolBarHelper::editList('project.edit');
		JToolBarHelper::custom('project.import','upload','upload',Jtext::_('COM_SPORTSMANAGEMENT_GLOBAL_CSV_IMPORT'),false);
		JToolBarHelper::archiveList('project.export',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_XML_EXPORT'));
		JToolBarHelper::custom('project.copy','copy.png','copy_f2.png',JText::_('JTOOLBAR_DUPLICATE'),false);
		JToolBarHelper::deleteList('', 'projects.delete');
		JToolBarHelper::divider();
		
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>
