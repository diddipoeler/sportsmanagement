<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statsranking
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
//jimport( 'joomla.application.component.view' );

/**
 * sportsmanagementViewStatsRanking
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewStatsRanking extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewStatsRanking::init()
	 * 
	 * @return void
	 */
	function init()
	{
		// Get a refrence of the page instance in joomla
		//$document = JFactory::getDocument();
        // Reference global application object
        //$app = JFactory::getApplication();
        // JInput object
        //$jinput = $app->input;

		// read the config-data from template file
		//$model = $this->getModel();
		//$config = $model->getTemplateConfig($this->getName());
	$this->document->addScript(JUri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
        sportsmanagementModelProject::setProjectID($this->jinput->getInt('p',0),$this->cfg_which_database);
		//$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database,__METHOD__);
		
		//$this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database,__METHOD__);
		$this->division = sportsmanagementModelProject::getDivision(0,$this->cfg_which_database);
		$this->teamid = $this->model->getTeamId();
		
        $teams = sportsmanagementModelProject::getTeamsIndexedById(0,'name',$this->cfg_which_database);
        //$teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$model::$cfg_which_database);
        
		if ( $this->teamid != 0 )
		{
			foreach ( $teams AS $k => $v)
			{
				if ($k != $this->teamid)
				{
					unset( $teams[$k] );
				}
			}
		}

		$this->teams = $teams;
		//$this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
		//$this->config = $config;
		$this->favteams = sportsmanagementModelProject::getFavTeams($this->cfg_which_database);
		$this->stats = $this->model->getProjectUniqueStats();
		$this->playersstats = $this->model->getPlayersStats();
		$this->limit = $this->model->getLimit();
		$this->limitstart = $this->model->getLimitStart();
		$this->multiple_stats = count($this->stats) > 1 ;
        
//        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');    
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playersstats<br><pre>'.print_r($this->playersstats,true).'</pre>'),'');
//}
		$prefix = JText::_('COM_SPORTSMANAGEMENT_STATSRANKING_PAGE_TITLE');
		if ( $this->multiple_stats )
		{
			$prefix .= " - " . JText::_('COM_SPORTSMANAGEMENT_STATSRANKING_TITLE' );
		}
		else
		{
			// Next query will result in an array with exactly 1 statistic id
			$sid = array_keys($this->stats);
			// Take the first result then.
			$prefix .= " - " . $this->stats[$sid[0]]->name;
		}

		// Set page title
		$titleInfo = sportsmanagementHelper::createTitleInfo($prefix);
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
        
		//parent::display( $tpl );
	}
}
?>
