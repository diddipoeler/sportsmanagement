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
		// Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a refrence of the page instance in joomla
		//$document = Factory::getDocument();

		$model = $this->getModel();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$jinput->getint( "cfg_which_database", 0 ));

		$tableconfig = sportsmanagementModelProject::getTemplateConfig( "ranking",$jinput->getint( "cfg_which_database", 0 ) );
		$eventsconfig = sportsmanagementModelProject::getTemplateConfig( "eventsranking",$jinput->getint( "cfg_which_database", 0 ) );
//		$flashconfig = sportsmanagementModelProject::getTemplateConfig( "flash",$jinput->getint( "cfg_which_database", 0 ) );

		$this->project = sportsmanagementModelProject::getProject($jinput->getint( "cfg_which_database", 0 ));
		if ( isset( $this->project ) )
		{
			$this->division = sportsmanagementModelProject::getDivision($model::$divisionid,$jinput->getint( "cfg_which_database", 0 ));
			$this->overallconfig = sportsmanagementModelProject::getOverallConfig($jinput->getint( "cfg_which_database", 0 ));
			if ( !isset( $this->overallconfig['seperator'] ) )
			{
				$this->overallconfig['seperator'] = ":";
			}
			$this->config = $config;
			$this->model = $model;
			$this->tableconfig = $tableconfig;
			$this->eventsconfig = $eventsconfig;
			$this->actualround = sportsmanagementModelProject::getCurrentRoundNumber($jinput->getint( "cfg_which_database", 0 ));
            $this->highest_home = $model->getHighest('HOME');
			$this->highest_away = $model->getHighest('AWAY');
            $this->totals = $model->getSeasonTotals();
			$this->totalrounds = $model->getTotalRounds();
			$this->attendanceranking = $model->getAttendanceRanking();
			$this->bestavg = $model->getBestAvg();
			$this->bestavgteam = $model->getBestAvgTeam();
			$this->worstavg = $model->getWorstAvg();
			$this->worstavgteam = $model->getWorstAvgTeam();

			$limit = 3;

			$this->limit = $limit;
            
$rounds	= sportsmanagementModelProject::getRounds('ASC',$jinput->getint( "cfg_which_database", 0 ));
$this->round_labels = array();
foreach ($rounds as $r) 
{
$this->round_labels[] = '"'.$r->name.'"';
}
            
            
			$this->_setChartdata(array_merge($config));
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
        
        $view = $jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.Uri::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_STATS_TITLE');

//		parent::display( $tpl );
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