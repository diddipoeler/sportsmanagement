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

/**
 * sportsmanagementViewProjects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewProjects extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewProjects::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
		$option 	= JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$uri		= JFactory::getUri();
        $model	= $this->getModel();
        $inputappend = '';
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');


		$starttime = microtime(); 
		// Get data from the model
		$items		= $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		
        
        $total = $this->get('Total');
		$pagination = $this->get('Pagination');
		$javascript = "onchange=\"$('adminForm').submit();\"";
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' total<br><pre>'.print_r($total,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' state<br><pre>'.print_r($this->state,true).'</pre>'),'Notice');



		//build the html select list for userfields
		$userfields[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_USERFIELD_FILTER'),'id','name');
		$mdluserfields = JModelLegacy::getInstance('extrafields','sportsmanagementModel');
		$alluserfields = $mdluserfields->getExtraFields('project');
		$userfields = array_merge($userfields,$alluserfields);
        
        $this->assignRef('userfields',$alluserfields);
        
		$lists['userfields'] = JHtml::_( 'select.genericList',
									$userfields,
									'filter_userfields',
									'class="inputbox" onChange="this.form.submit();" style="width:120px"',
									'id',
									'name',
									$this->state->get('filter.userfields'));
		unset($leagues);
        
        //build the html select list for leagues
		$leagues[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUES_FILTER'),'id','name');
		$mdlLeagues = JModelLegacy::getInstance('Leagues','sportsmanagementModel');
		$allLeagues = $mdlLeagues->getLeagues();
		$leagues = array_merge($leagues,$allLeagues);
        
        $this->assignRef('league',$allLeagues);
        
		$lists['leagues'] = JHtml::_( 'select.genericList',
									$leagues,
									'filter_league',
									'class="inputbox" onChange="this.form.submit();" style="width:120px"',
									'id',
									'name',
									$this->state->get('filter.league'));
		unset($leagues);
		
		
		//build the html select list for sportstypes
		$sportstypes[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'),'id','name');
		$mdlSportsTypes = JModelLegacy::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes = array_merge($sportstypes,$allSportstypes);
        
        $this->assignRef('sports_type',$allSportstypes);
        
		$lists['sportstypes'] = JHtml::_( 'select.genericList',
										$sportstypes,
										'filter_sports_type',
										'class="inputbox" onChange="this.form.submit();" style="width:120px"',
										'id',
										'name',
										$this->state->get('filter.sports_type'));
		unset($sportstypes);
		
		
		//build the html select list for seasons
		$seasons[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'),'id','name');
        $mdlSeasons = JModelLegacy::getInstance('Seasons','sportsmanagementModel');
		$allSeasons = $mdlSeasons->getSeasons();
		$seasons = array_merge($seasons,$allSeasons);
        
        $this->assignRef('season',$allSeasons);
        
		$lists['seasons'] = JHtml::_( 'select.genericList',
									$seasons,
									'filter_season',
									'class="inputbox" onChange="this.form.submit();" style="width:120px"',
									'id',
									'name',
									$this->state->get('filter.season'));

		unset($seasons);
        
        //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ( $res = JSMCountries::getCountryOptions() )
        {
            $nation = array_merge($nation,$res);
            $this->assignRef('search_nation',$res);
        }
        
        $lists['nation'] = $nation;
        $lists['nation2']= JHtmlSelect::genericlist(	$nation,
																'filter_search_nation',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$this->state->get('filter.search_nation'));
        $myoptions = array();
        $myoptions[] = JHtml::_( 'select.option', '', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTTYPE_FILTER' ) );
		$myoptions[] = JHtml::_( 'select.option', 'SIMPLE_LEAGUE', JText::_( 'COM_SPORTSMANAGEMENT_SIMPLE_LEAGUE' ) );
		$myoptions[] = JHtml::_( 'select.option', 'DIVISIONS_LEAGUE', JText::_( 'COM_SPORTSMANAGEMENT_DIVISIONS_LEAGUE' ) );
        $myoptions[] = JHtml::_( 'select.option', 'TOURNAMENT_MODE', JText::_( 'COM_SPORTSMANAGEMENT_TOURNAMENT_MODE' ) );
        $myoptions[] = JHtml::_( 'select.option', 'FRIENDLY_MATCHES', JText::_( 'COM_SPORTSMANAGEMENT_FRIENDLY_MATCHES' ) );
		$lists['project_type'] = $myoptions;	
        
        $lists['project_types'] = JHtml::_( 'select.genericList',
									$myoptions,
									'filter_project_type',
									'class="inputbox" onChange="this.form.submit();" style="width:120px"',
									'value',
									'text',
									$this->state->get('filter.project_type'));
        unset($myoptions);
        
        $mdlProjectDivisions = JModelLegacy::getInstance("divisions", "sportsmanagementModel");
        $mdlRounds = JModelLegacy::getInstance("Rounds", "sportsmanagementModel");
      
		$user = JFactory::getUser();
        $this->assignRef('modeldivision',$mdlProjectDivisions);
        $this->assignRef('modelround',$mdlRounds);
		$this->assignRef('user',$user);
		$this->assignRef('lists',$lists);
		$this->assignRef('items',$items);
		$this->assignRef('pagination',$pagination);
		$url=$uri->toString();
		$this->assignRef('request_url',$url);
        


	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolbar()
	{
	//// Get a refrence of the page instance in joomla
//        $document = JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
//		// Set toolbar items for the page
//		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE'),'projects');
		JToolBarHelper::publishList('project.publish');
		JToolBarHelper::unpublishList('project.unpublish');
		JToolBarHelper::divider();
		
        JToolBarHelper::apply('projects.saveshort');
        
		JToolBarHelper::addNew('project.add');
		JToolBarHelper::editList('project.edit');
		JToolBarHelper::custom('project.import','upload','upload',Jtext::_('COM_SPORTSMANAGEMENT_GLOBAL_CSV_IMPORT'),false);
		JToolBarHelper::archiveList('project.export',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_XML_EXPORT'));
		JToolBarHelper::custom('project.copy','copy.png','copy_f2.png',JText::_('JTOOLBAR_DUPLICATE'),false);
		JToolBarHelper::deleteList('', 'projects.delete');
		
        parent::addToolbar();
	}
}
?>
