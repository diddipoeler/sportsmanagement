<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage eventsranking
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewEventsRanking
 * 
 * @package 
 * @author diddi
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementViewEventsRanking extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewEventsRanking::init()
	 * 
	 * @return void
	 */
	function init()
	{

$this->document->addScript ( JUri::root(true).'/components/'.$this->option.'/assets/js/smsportsmanagement.js' );

        sportsmanagementModelProject::setProjectID($this->jinput->getInt('p',0),$this->jinput->getInt('cfg_which_database',0));

		$this->division = sportsmanagementModelProject::getDivision(0,$this->jinput->getInt('cfg_which_database',0));
		$this->matchid = sportsmanagementModelEventsRanking::$matchid;
		$this->teamid = $this->model->getTeamId();
		$this->teams = sportsmanagementModelProject::getTeamsIndexedById(0,'name',$this->jinput->getInt('cfg_which_database',0));
		$this->favteams = sportsmanagementModelProject::getFavTeams($this->jinput->getInt('cfg_which_database',0));
		$this->eventtypes = sportsmanagementModelProject::getEventTypes(sportsmanagementModelEventsRanking::$eventid,$this->jinput->getInt('cfg_which_database',0));
		$this->limit = $this->model->getLimit();
		$this->limitstart = $this->model->getLimitStart();
		$this->pagination = $this->get('Pagination');
		$this->eventranking = $this->model->getEventRankings($this->limit,$this->limitstart);
		$this->multiple_events = count($this->eventtypes) > 1 ;
        
        $prefix = JText::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_PAGE_TITLE');
		if ( $this->multiple_events )
		{
			$prefix .= " - " . JText::_( 'COM_SPORTSMANAGEMENT_EVENTSRANKING_TITLE' );
		}
		else
		{
			// Next query will result in an array with exactly 1 statistic id
			$evid = array_keys($this->eventtypes);

			// Selected one valid eventtype, so show its name
			$prefix .= " - " . JText::_($this->eventtypes[$evid[0]]->name);
		}

		// Set page title
		$titleInfo = sportsmanagementHelper::createTitleInfo($prefix);
		if (!empty($this->teamid) && array_key_exists($this->teamid, $this->teams))
		{
			$titleInfo->team1Name = $this->teams[$this->teamid]->name;
		}
		if (!empty($this->project))
		{
			$titleInfo->projectName = $this->project->name;
			$titleInfo->leagueName = $this->project->league_name;
			$titleInfo->seasonName = $this->project->season_name;
		}
		if (!empty( $this->division ) && $this->division->id != 0)
		{
			$titleInfo->divisionName = $this->division->name;
		}
		$this->pagetitle = sportsmanagementHelper::formatTitle($titleInfo, $this->config["page_title_format"]);
		$this->document->setTitle($this->pagetitle);
        
        $this->headertitle = $this->pagetitle;
if ( !isset($this->config['table_class']) )
	{
	$this->config['table_class'] = 'table';
	}

	}

}
?>
