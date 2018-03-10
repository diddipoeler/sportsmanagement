<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage projects
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
//	   $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' state<br><pre>'.print_r($this->state,true).'</pre>'),'Notice');
        $inputappend = '';

		$starttime = microtime(); 
                        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		
        JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
        $table = JTable::getInstance('project', 'sportsmanagementTable');
		$this->table = $table;
        
		$javascript = "onchange=\"$('adminForm').submit();\"";

		//build the html select list for userfields
		$userfields[] = JHtml::_('select.option', '0' ,JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_USERFIELD_FILTER'), 'id', 'name');
		$mdluserfields = JModelLegacy::getInstance('extrafields', 'sportsmanagementModel');
		$alluserfields = $mdluserfields->getExtraFields('project');
		$userfields = array_merge($userfields, $alluserfields);
        
        $this->userfields	= $alluserfields;
        
		$lists['userfields'] = JHtml::_( 'select.genericList', 
						$userfields, 
						'filter_userfields', 
						'class="inputbox" onChange="this.form.submit();" style="width:120px"', 
						'id', 
						'name', 
						$this->state->get('filter.userfields'));
		unset($userfields);
        
		foreach ( $this->items as $row )
		{
			$row->user_field = $mdluserfields->getExtraFieldsProject($row->id);
		}
        
        //build the html select list for leagues
		$leagues[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUES_FILTER'), 'id', 'name');
		$mdlLeagues = JModelLegacy::getInstance('Leagues','sportsmanagementModel');
		$allLeagues = $mdlLeagues->getLeagues();
		$leagues = array_merge($leagues, $allLeagues);
        
        $this->league	= $allLeagues;
        
		$lists['leagues'] = JHtml::_( 'select.genericList', 
						$leagues, 
						'filter_league', 
						'class="inputbox" onChange="this.form.submit();" style="width:120px"', 
						'id', 
						'name', 
						$this->state->get('filter.league'));
		unset($leagues);
		
		
		//build the html select list for sportstypes
		$sportstypes[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'), 'id', 'name');
		$mdlSportsTypes = JModelLegacy::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes = array_merge($sportstypes, $allSportstypes);
        
        $this->sports_type	= $allSportstypes;
        
        $lists['sportstype'] = $sportstypes;
		$lists['sportstypes'] = JHtml::_( 'select.genericList', 
						$sportstypes, 
						'filter_sports_type', 
						'class="inputbox" onChange="this.form.submit();" style="width:120px"', 
						'id', 
						'name', 
						$this->state->get('filter.sports_type'));
		unset($sportstypes);
		
		
		//build the html select list for seasons
		$seasons[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name');
        $mdlSeasons = JModelLegacy::getInstance('Seasons','sportsmanagementModel');
		$allSeasons = $mdlSeasons->getSeasons();
		$seasons = array_merge($seasons, $allSeasons);
        
        $this->season	= $allSeasons;
        
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
			$this->search_nation	= $res;
		}
        
		$lists['nation'] = $nation;
		$lists['nation2'] = JHtmlSelect::genericlist(	$nation, 
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

$myoptions[] = JHtml::_( 'select.option', '', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_UNIQUE_ID_FILTER' ) );
$myoptions[] = JHtml::_( 'select.option', '1', JText::_( 'JNO' ) );
$myoptions[] = JHtml::_( 'select.option', '2', JText::_( 'JYES' ) );        
$this->unique_id = $myoptions;        
unset($myoptions);
		
		$myoptions[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
		$mdlagegroup = JModelLegacy::getInstance('agegroups', 'sportsmanagementModel');
        if ( $res = $mdlagegroup->getAgeGroups() )
		{
			$myoptions = array_merge($myoptions,$res);
			$this->search_agegroup = $res;
		}
		$lists['agegroup'] = $myoptions;
		$lists['agegroup2'] = JHtmlSelect::genericlist($myoptions,
						'filter_search_agegroup',
						'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
						'value', 
						'text', 
						$this->state->get('filter.search_agegroup'));
		unset($myoptions);
        
		unset($nation);
		$nation[] = JHtml::_('select.option', '0' ,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
		$mdlassociation = JModelLegacy::getInstance('jlextassociations', 'sportsmanagementModel');
		if ( $res = $mdlassociation->getAssociations() )
		{
            $nation = array_merge($nation, $res);
            $this->search_association	= $res;
		}
        
		$lists['association'] = array();
		foreach( $res as $row)
		{
			if (array_key_exists($row->country, $lists['association'] )) 
			{
			$lists['association'][$row->country][] = $row;
            //echo "Das Element 'erstes' ist in dem Array vorhanden";
			}
            else
            {
            $lists['association'][$row->country][] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
            $lists['association'][$row->country][] = $row;    
            }
            
            
            
            //$lists['association'] = $nation;
        }
        //$lists['association'] = $nation;
        
        
		$lists['association2']= JHtmlSelect::genericlist(	$nation, 
																'filter_search_association', 
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 
																'value', 
																'text', 
																$this->state->get('filter.search_association'));
        
        
		$mdlProjectDivisions = JModelLegacy::getInstance('divisions', 'sportsmanagementModel');
		$mdlRounds = JModelLegacy::getInstance('Rounds', 'sportsmanagementModel');
      $mdlMatches = JModelLegacy::getInstance('Matches', 'sportsmanagementModel');
      
        $this->modeldivision	= $mdlProjectDivisions;
        $this->modelround	= $mdlRounds;
        $this->modelmatches	= $mdlMatches;
		$this->lists	= $lists;

	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolbar()
	{
		// Set toolbar items for the page
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE');
        $this->icon = 'projects';

		JToolbarHelper::publishList('projects.publish');
		JToolbarHelper::unpublishList('projects.unpublish');
		JToolbarHelper::divider();
		
        JToolbarHelper::apply('projects.saveshort');
        
		JToolbarHelper::addNew('project.add');
		JToolbarHelper::editList('project.edit');
		JToolbarHelper::custom('project.import','upload','upload',Jtext::_('COM_SPORTSMANAGEMENT_GLOBAL_CSV_IMPORT'),false);
		JToolbarHelper::archiveList('project.export',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_XML_EXPORT'));
		JToolbarHelper::custom('project.copy','copy.png','copy_f2.png',JText::_('JTOOLBAR_DUPLICATE'),false);

        parent::addToolbar();
	}
}
?>
