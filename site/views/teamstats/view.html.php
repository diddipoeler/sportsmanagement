<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamstats
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewTeamStats
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTeamStats extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeamStats::init()
	 *
	 * @return void
	 */
	function init()
	{
		if ($this->config['show_goals_stats_flash'])
		{
			$js = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/'.$this->chart_version.'/Chart.js';
			$this->document->addScript($js);
		}

		if (isset($this->project))
		{
			if (!isset($this->overallconfig['seperator']))
			{
				$this->overallconfig['seperator'] = ":";
			}

			$this->actualround       = sportsmanagementModelProject::getCurrentRound(null, sportsmanagementModelTeamStats::$cfg_which_database);
			$this->team              = $this->model->getTeam();
			$this->highest_home      = $this->model->getHighest('HOME', 'WIN');
			$this->highest_away      = $this->model->getHighest('AWAY', 'WIN');
			$this->highestdef_home   = $this->model->getHighest('HOME', 'DEF');
			$this->highestdef_away   = $this->model->getHighest('AWAY', 'DEF');
			$this->highestdraw_home  = $this->model->getHighest('HOME', 'DRAW');
			$this->highestdraw_away  = $this->model->getHighest('AWAY', 'DRAW');
			$this->totalshome        = $this->model->getSeasonTotals('HOME');
			$this->totalsaway        = $this->model->getSeasonTotals('AWAY');
			$this->matchdaytotals    = $this->model->getMatchDayTotals();
			$this->totalrounds       = $this->model->getTotalRounds();
			$this->totalattendance   = $this->model->getTotalAttendance();
			$this->bestattendance    = $this->model->getBestAttendance();
			$this->worstattendance   = $this->model->getWorstAttendance();
			$this->averageattendance = $this->model->getAverageAttendance();
			$this->chart_url         = $this->model->getChartURL();
			$this->nogoals_against   = $this->model->getNoGoalsAgainst();
			$this->logo              = $this->model->getLogo();
			$this->results           = $this->model->getResults();

			if ($this->config['show_goals_stats_flash'])
			{
				$rounds             = sportsmanagementModelProject::getRounds('ASC', sportsmanagementModelTeamStats::$cfg_which_database);
				$this->round_labels = array();

				foreach ($rounds as $r)
				{
					$this->round_labels[] = '"' . $r->name . '"';
				}

				$this->_setChartdata(array_merge(sportsmanagementModelProject::getTemplateConfig("flash", sportsmanagementModelTeamStats::$cfg_which_database), $this->config));
			}
		}

		// Set page title
		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_PAGE_TITLE');

		if (isset($this->team))
		{
			$pageTitle .= ': ' . $this->team->name;
		}

		$this->document->setTitle($pageTitle);

		$this->headertitle = Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_TITLE') . " - " . $this->team->name;

	}

	/**
	 * assign the chartdata object for open flash chart library
	 *
	 * @param  $config
	 *
	 * @return unknown_type
	 */
	function _setChartdata($config)
	{
		$data = $this->get('ChartData');

		// Calculate Values for Chart Object
		$forSum             = array();
		$againstSum         = array();
		$matchDayGoalsCount = array();

		$matchDayGoalsCountMax = 0;

		foreach ($data as $rw)
		{
			if (!$rw->goalsfor)
			{
				$rw->goalsfor = 0;
			}

			if (!$rw->goalsagainst)
			{
				$rw->goalsagainst = 0;
			}

			$forSum[]     = intval($rw->goalsfor);
			$againstSum[] = intval($rw->goalsagainst);

			// Check, if both results are missing and avoid drawing the flatline of "0" goals for not played games yet
			if ((!$rw->goalsfor) && (!$rw->goalsagainst))
			{
				$matchDayGoalsCount[] = 0;
			}
			else
			{
				$matchDayGoalsCount[] = intval($rw->goalsfor + $rw->goalsagainst);
			}

			$matchDayGoalsCountMax = intval($rw->goalsfor + $rw->goalsagainst) > $matchDayGoalsCountMax ? intval($rw->goalsfor + $rw->goalsagainst) : $matchDayGoalsCountMax;
		}

		$this->matchDayGoalsCountMax = $matchDayGoalsCountMax;
		$this->forSum                = $forSum;
		$this->againstSum            = $againstSum;
	}
}
