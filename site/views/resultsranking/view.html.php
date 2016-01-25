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

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'pagination.php');
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'ranking.php' );
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'results.php' );
require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'results' . DS . 'view.html.php' );

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');
//jimport('joomla.html.pane');

/**
 * sportsmanagementViewResultsranking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewResultsranking extends sportsmanagementView 
{


	/**
	 * sportsmanagementViewResultsranking::init()
	 * 
	 * @return void
	 */
	function init()
	{
		//// welche joomla version
//if(version_compare(JVERSION,'3.0.0','ge')) 
//{
//JHtml::_('behavior.framework', true);
//}
//else
//{
//JHtml::_( 'behavior.mootools' );    
//}
		//// Reference global application object
//        $app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        $option = $jinput->getCmd('option');
//		$params = $app->getParams();
//		// get a reference of the page instance in joomla
//		$document = JFactory :: getDocument();
//		$uri = JFactory :: getURI();
        
        $this->document->addScript ( JUri::root(true).'/components/'.$this->option.'/assets/js/smsportsmanagement.js' );
        
	//	$cfg_which_database = $jinput->getInt('cfg_which_database',0);
        
        /*
        // add the css files
		$version = urlencode(JoomleagueHelper::getVersion());
		$css		= 'components/com_sportsmanagement/assets/css/tabs.css?v='.$version;
		$document->addStyleSheet($css);
        */
        
        /*
		// add some javascript
		$version = urlencode(JoomleagueHelper::getVersion());
		$document->addScript( JURI::base(true).'/components/com_sportsmanagement/assets/js/results.js?v='.$version);
        */
        
		// add the ranking model
		$rankingmodel = new sportsmanagementModelRanking();
		$project = sportsmanagementModelProject::getProject($this->jinput->getInt('cfg_which_database',0),__METHOD__,1);
		// add the ranking config file
		$rankingconfig = sportsmanagementModelProject::getTemplateConfig('ranking',$this->jinput->getInt('cfg_which_database',0));
		$rankingmodel->computeRanking($this->jinput->getInt('cfg_which_database',0));
        
        $mdlProjectteams = JModelLegacy::getInstance("Projectteams", "sportsmanagementModel");
        
		// add the results model		
		$resultsmodel	= new sportsmanagementModelResults();
		// add the results config file

		$mdlRound = JModelLegacy::getInstance("Round", "sportsmanagementModel");
		$roundcode = $mdlRound->getRoundcode($rankingmodel::$round,$this->jinput->getInt('cfg_which_database',0));
        $this->paramconfig = $rankingmodel::$paramconfig;
        $this->paramconfig['p'] = $project->slug;
		//$rounds = sportsmanagementHelper::getRoundsOptions($project->id, 'ASC', true,NULL,$cfg_which_database);
		
		$resultsconfig = sportsmanagementModelProject::getTemplateConfig('results',$this->jinput->getInt('cfg_which_database',0));
		if (!isset($resultsconfig['switch_home_guest'])){$resultsconfig['switch_home_guest']=0;}
		if (!isset($resultsconfig['show_dnp_teams_icons'])){$resultsconfig['show_dnp_teams_icons']=0;}
		if (!isset($resultsconfig['show_results_ranking'])){$resultsconfig['show_results_ranking']=0;}

		// merge the 2 config files
		$config = array_merge($rankingconfig, $resultsconfig);

//		$this->assign('project',sportsmanagementModelProject::getProject($cfg_which_database));
//		$this->assign('overallconfig',sportsmanagementModelProject::getOverallConfig($cfg_which_database));

		$this->config = array_merge($this->overallconfig, $config);
		$this->tableconfig = $rankingconfig;
//		$this->assignRef('params',$params);
		$this->showediticon = $resultsmodel->getShowEditIcon();
		$this->division = $resultsmodel->getDivision();
		$this->divisions = sportsmanagementModelProject::getDivisions(0,$this->jinput->getInt('cfg_which_database',0));
		$this->divLevel = $rankingmodel::$divLevel;
		$this->matches = $resultsmodel->getMatches($this->jinput->getInt('cfg_which_database',0));
		$this->round = $resultsmodel::$roundid;
		$this->roundid = $resultsmodel::$roundid;
		$this->roundcode = $roundcode;
		
		$rounds = sportsmanagementModelProject::getRoundOptions('ASC',$this->jinput->getInt('cfg_which_database',0));
		$options = $this->getRoundSelectNavigation($rounds,$this->jinput->getInt('cfg_which_database',0));
		
		$this->matchdaysoptions = $options;
        $routeparameter = array();
$routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database',0);
$routeparameter['s'] = $this->jinput->getInt('s',0);
$routeparameter['p'] = $project->slug;
$routeparameter['r'] = $this->roundid;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = 0;
$routeparameter['layout'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking',$routeparameter);

		$this->currenturl = $link;
		$this->rounds = sportsmanagementModelProject::getRounds('ASC',$this->jinput->getInt('cfg_which_database',0));
		$this->favteams = sportsmanagementModelProject::getFavTeams($this->jinput->getInt('cfg_which_database',0));
		$this->projectevents = sportsmanagementModelProject::getProjectEvents(0,$this->jinput->getInt('cfg_which_database',0));
		$this->model = $resultsmodel;
		$this->isAllowed = $resultsmodel->isAllowed();
		$this->type = $rankingmodel::$type;
		$this->from = $rankingmodel::$from;
		$this->to = $rankingmodel::$to;
		$this->currentRanking = $rankingmodel::$currentRanking;
		$this->previousRanking = $rankingmodel::$previousRanking;
		$this->homeRank = $rankingmodel::$homeRank;
		$this->awayRank = $rankingmodel::$awayRank;
		$this->current_round = $rankingmodel::$current_round;
		$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$this->jinput->getInt('cfg_which_database',0));
		$this->previousgames = $rankingmodel->getPreviousGames($this->jinput->getInt('cfg_which_database',0));
//		$this->action',$uri->toString());
        
		//rankingcolors
		if (!isset ($this->config['colors'])) {
			$this->config['colors'] = "";
		}
		$this->colors = sportsmanagementModelProject::getColors($this->config['colors'],$this->jinput->getInt('cfg_which_database',0));
        
        //$this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
        //$this->assign('use_joomlaworks', JComponentHelper::getParams('com_sportsmanagement')->get('use_joomlaworks',0) );

		
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' what_to_show_first<br><pre>'.print_r($this->params->get('what_to_show_first'),true).'</pre>'),'');
        
        // Set page title
		$pageTitle = ($this->params->get('what_to_show_first', 0) == 0)
			? JText::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE').' & ' . JText :: _('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE')
			: JText::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE').' & ' . JText :: _('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');
            
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ' - ' . $this->project->name;
		}
		$this->document->setTitle($pageTitle);
        
        //$view = $jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$this->option.'/assets/css/'.$this->view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        
        // diddipoeler
		$this->allteams = $mdlProjectteams->getAllProjectTeams($project->id,0,null,$this->jinput->getInt('cfg_which_database',0));
        
        if ( $this->config['show_ranking_maps'] )
	  {
	  $this->geo = new JSMsimpleGMapGeocoder();
	  $this->geo->genkml3($project->id,$this->allteams);
  
  foreach ( $this->allteams as $row )
    {
    $address_parts = array();
		if (!empty($row->club_address))
		{
			$address_parts[] = $row->club_address;
		}
		if (!empty($row->club_state))
		{
			$address_parts[] = $row->club_state;
		}
		if (!empty($row->club_location))
		{
			if (!empty($row->club_zipcode))
			{
				$address_parts[] = $row->club_zipcode. ' ' .$row->club_location;
			}
			else
			{
				$address_parts[] = $row->club_location;
			}
		}
		if (!empty($row->club_country))
		{
			$address_parts[] = JSMCountries::getShortCountryName($row->club_country);
		}
		$row->address_string = implode(', ', $address_parts);

    }

	}
    
		/*
		//build feed links
		$feed = 'index.php?option=com_sportsmanagement&view=results&p='.$this->project->id.'&format=feed';
		$rss = array('type' => 'application/rss+xml', 'title' => JText::_('COM_SPORTSMANAGEMENT_RESULTS_RSSFEED'));

		// add the links
		$document->addHeadLink(JRoute::_($feed.'&type=rss'), 'alternate', 'rel', $rss);
		*/
		//JViewLegacy::display($tpl);
	}
	
	/**
	 * sportsmanagementViewResultsranking::getRoundSelectNavigation()
	 * 
	 * @param mixed $rounds
	 * @return
	 */
	function getRoundSelectNavigation(&$rounds,$cfg_which_database = 0)
	{
		$options = array();
		foreach ($rounds as $r)
		{
		  $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['r'] = $r->slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = 0;
$routeparameter['layout'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking',$routeparameter);


			$options[] = JHTML::_('select.option', $link, $r->text);
		}
		return $options;
	}
		
}
?>
