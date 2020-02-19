<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage resultsranking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

require_once(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'results' .DIRECTORY_SEPARATOR. 'view.html.php' );

jimport('joomla.filesystem.file');

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
        
        $this->document->addScript ( Uri::root(true).'/components/'.$this->option.'/assets/js/smsportsmanagement.js' );
        
/**
 * add the ranking model
 */
		$rankingmodel = new sportsmanagementModelRanking();
		$project = sportsmanagementModelProject::getProject($this->jinput->getInt('cfg_which_database',0),__METHOD__,1);
/**
 * add the ranking config file
 */
		$rankingconfig = sportsmanagementModelProject::getTemplateConfig('ranking',$this->jinput->getInt('cfg_which_database',0));
		$rankingmodel::$from = 0;
                $rankingmodel::$to = 0;
		$rankingmodel->computeRanking($this->jinput->getInt('cfg_which_database',0));
        
        $mdlProjectteams = BaseDatabaseModel::getInstance("Projectteams", "sportsmanagementModel");
        
/**
 * add the results model
 */		
		$resultsmodel	= new sportsmanagementModelResults();
/**
 * add the results config file
 */
		$mdlRound = BaseDatabaseModel::getInstance("Round", "sportsmanagementModel");
		$roundcode = $mdlRound->getRoundcode($rankingmodel::$round,$this->jinput->getInt('cfg_which_database',0));
        $this->paramconfig = $rankingmodel::$paramconfig;
        if ( $project )
	{
		$this->paramconfig['p'] = $project->slug;
	}
		$resultsconfig = sportsmanagementModelProject::getTemplateConfig('results',$this->jinput->getInt('cfg_which_database',0));
		if (!isset($resultsconfig['switch_home_guest'])){$resultsconfig['switch_home_guest']=0;}
		if (!isset($resultsconfig['show_dnp_teams_icons'])){$resultsconfig['show_dnp_teams_icons']=0;}
		if (!isset($resultsconfig['show_results_ranking'])){$resultsconfig['show_results_ranking']=0;}

/**
 * merge the 2 config files
 */
		$config = array_merge($rankingconfig, $resultsconfig);

		$this->config = array_merge($this->overallconfig, $config);
		
	if ( ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info_frontend') )
        {

        }
		$this->tableconfig = $rankingconfig;
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
		if ( $project )
	{
$routeparameter['p'] = $project->slug;
		}
$routeparameter['r'] = sportsmanagementModelProject::$roundslug;
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
		
if ( $this->params->get('show_allranking', 0) ) 
{
$this->previousRanking = $rankingmodel::$previousRanking;
if ($this->config['show_table_1']) {
$this->currentRanking = $rankingmodel::$currentRanking;
}	
if ($this->config['show_table_2']) {
$this->homeRank = $rankingmodel::$homeRank;
}	
if ($this->config['show_table_3']) {
$this->awayRank = $rankingmodel::$awayRank;
}
if ($this->config['show_table_4']) {
$rankingmodel::$part = 1;
$rankingmodel::computeRanking(sportsmanagementModelProject::$cfg_which_database);
$this->firstRank = $rankingmodel::$currentRanking;
}
if ($this->config['show_table_5']) {
$rankingmodel::$part = 2;
$rankingmodel::computeRanking(sportsmanagementModelProject::$cfg_which_database);
$this->secondRank = $rankingmodel::$currentRanking;
}	
}
else
{
$this->previousRanking = $rankingmodel::$previousRanking;
$this->currentRanking = $rankingmodel::$currentRanking;	
}

		$this->current_round = $rankingmodel::$current_round;
		$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$this->jinput->getInt('cfg_which_database',0));
		$this->previousgames = $rankingmodel->getPreviousGames($this->jinput->getInt('cfg_which_database',0));
        
/**
 * rankingcolors
 */
		if (!isset ($this->config['colors'])) {
			$this->config['colors'] = "";
		}
		$this->colors = sportsmanagementModelProject::getColors($this->config['colors'],$this->jinput->getInt('cfg_which_database',0));
        
/**
 * Set page title
 */
		$pageTitle = ($this->params->get('what_to_show_first', 0) == 0)
			? Text::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE').' & ' . Text :: _('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE')
			: Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE').' & ' . Text :: _('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');
            
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ' - ' . $this->project->name;
		}
		$this->document->setTitle($pageTitle);
        
        $stylelink = '<link rel="stylesheet" href="'.Uri::root().'components/'.$this->option.'/assets/css/'.$this->view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        
/**
 * diddipoeler
 */
		if ( $project )
		{
		$this->allteams = $mdlProjectteams->getAllProjectTeams($project->id,0,null,$this->jinput->getInt('cfg_which_database',0));
		}
      if ( $this->params->get('show_map', 0) )
	  {
	  $this->mapconfig = sportsmanagementModelProject::getTemplateConfig('map',$this->jinput->getInt('cfg_which_database',0)); 

//	  $this->geo = new JSMsimpleGMapGeocoder();
//	  $this->geo->genkml3($project->id,$this->allteams);
  
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
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['r'] = $r->slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = 0;
$routeparameter['layout'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking',$routeparameter);


			$options[] = HTMLHelper::_('select.option', $link, $r->text);
		}
		return $options;
	}
		
}
?>
