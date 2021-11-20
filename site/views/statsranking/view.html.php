<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statsranking
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewStatsRanking
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
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
		$this->jsmstartzeit = $this->getStartzeit();
		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
		sportsmanagementModelProject::setProjectID($this->jinput->getInt('p', 0), $this->cfg_which_database);

		// $config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database,__METHOD__);

		// $this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database,__METHOD__);
		$this->division = sportsmanagementModelProject::getDivision(0, $this->cfg_which_database);
		$this->teamid   = $this->model->getTeamId();

		$teams = sportsmanagementModelProject::getTeamsIndexedById(0, 'name', $this->cfg_which_database);

		// $teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$model::$cfg_which_database);

		if ($this->teamid != 0)
		{
			foreach ($teams AS $k => $v)
			{
				if ($k != $this->teamid)
				{
					unset($teams[$k]);
				}
			}
		}

		$this->teams = $teams;

		// $this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
		// $this->config = $config;
		$this->favteams       = sportsmanagementModelProject::getFavTeams($this->cfg_which_database);
		$this->stats          = $this->model->getProjectUniqueStats();
		$this->playersstats   = $this->model->getPlayersStats();
		$this->limit          = $this->model->getLimit();
		$this->limitstart     = $this->model->getLimitStart();
		$this->multiple_stats = count($this->stats) > 1;

		$prefix = Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_PAGE_TITLE');

		if ($this->multiple_stats)
		{
			$prefix .= " - " . Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_TITLE');
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
			$titleInfo->leagueName  = $this->project->league_name;
			$titleInfo->seasonName  = $this->project->season_name;
		}

		if (!empty($this->division) && $this->division->id != 0)
		{
			$titleInfo->divisionName = $this->division->name;
		}

		$this->pagetitle = sportsmanagementHelper::formatTitle($titleInfo, $this->config["page_title_format"]);
		$this->document->setTitle($this->pagetitle);

		$this->headertitle = $this->pagetitle;

		// Parent::display( $tpl );
	}
}
