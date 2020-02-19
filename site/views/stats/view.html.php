<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage stats
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewStats
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewStats extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewStats::init()
	 * 
	 * @return void
	 */
	function init()
	{
		$js = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.js';
        $this->document->addScript($js);    

		$this->project = sportsmanagementModelProject::getProject($this->jinput->getint( "cfg_which_database", 0 ));
		if ( isset( $this->project ) )
		{
			$this->division = sportsmanagementModelProject::getDivision($this->jinput->getint( "division", 0 ),$this->jinput->getint( "cfg_which_database", 0 ));
			$this->overallconfig = sportsmanagementModelProject::getOverallConfig($this->jinput->getint( "cfg_which_database", 0 ));
			if ( !isset( $this->overallconfig['seperator'] ) )
			{
				$this->overallconfig['seperator'] = ":";
			}
			$this->actualround = sportsmanagementModelProject::getCurrentRoundNumber($this->jinput->getint( "cfg_which_database", 0 ));
            $this->highest_home = $this->model->getHighest('HOME');
			$this->highest_away = $this->model->getHighest('AWAY');
            $this->totals = $this->model->getSeasonTotals();
			$this->totalrounds = $this->model->getTotalRounds();
			$this->attendanceranking = $this->model->getAttendanceRanking();
			$this->bestavg = $this->model->getBestAvg();
			$this->bestavgteam = $this->model->getBestAvgTeam();
			$this->worstavg = $this->model->getWorstAvg();
			$this->worstavgteam = $this->model->getWorstAvgTeam();

			$limit = 3;

			$this->limit = $limit;
            
$rounds	= sportsmanagementModelProject::getRounds('ASC',$this->jinput->getint( "cfg_which_database", 0 ));
$this->round_labels = array();
foreach ($rounds as $r) 
{
$this->round_labels[] = '"'.$r->name.'"';
}
            
            
		$this->_setChartdata(array_merge(sportsmanagementModelProject::getTemplateConfig("flash",$this->jinput->getint( "cfg_which_database", 0 ) ),$this->config));
		}
		// Set page title
		$pageTitle = Text::_( 'COM_SPORTSMANAGEMENT_STATS_PAGE_TITLE' );
		if ( isset( $this->project ) )
		{
			$pageTitle .= ': ' . $this->project->name;
			if ( isset( $this->division ) )
			{
				$pageTitle .= ': ' . $this->division->name;
			}
		}
		$this->document->setTitle( $pageTitle );
        
        $view = $this->jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.Uri::root().'components/'.$this->option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_STATS_TITLE');

	}

	/**
	 * sportsmanagementViewStats::_setChartdata()
	 * 
	 * @param mixed $config
	 * @return
	 */
	function _setChartdata($config)
	{
		$data = $this->get('ChartData');
		// Calculate Values for Chart Object
		$homeSum = array();
		$awaySum = array();
		$matchDayGoalsCount = array();
        $matchDayGoalsCountMax = 0;

		foreach( $data as $rw )
		{
			if (!$rw->homegoalspd) $rw->homegoalspd = 0;
			if (!$rw->guestgoalspd) $rw->guestgoalspd = 0;
			$homeSum[] = (int)$rw->homegoalspd;
			$awaySum[] = (int)$rw->guestgoalspd;
			// check, if both results are missing and avoid drawing the flatline of "0" goals for not played games yet
			if ((!$rw->homegoalspd) && (!$rw->guestgoalspd))
			{
				$matchDayGoalsCount[] = null;
			}
			else
			{
				$matchDayGoalsCount[] = (int)$rw->homegoalspd + $rw->guestgoalspd;
			}
            $matchDayGoalsCountMax = (int)$rw->homegoalspd + $rw->guestgoalspd > $matchDayGoalsCountMax ? (int)$rw->homegoalspd + $rw->guestgoalspd : $matchDayGoalsCountMax;
		}
$this->matchDayGoalsCount = $matchDayGoalsCount;
$this->matchDayGoalsCountMax = $matchDayGoalsCountMax;
$this->homeSum = $homeSum;
$this->awaySum = $awaySum;

	}
}
?>
