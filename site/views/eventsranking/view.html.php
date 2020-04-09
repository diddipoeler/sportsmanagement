<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage eventsranking
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewEventsRanking
 *
 * @package
 * @author    diddi
 * @copyright 2015
 * @version   $Id$
 * @access    public
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

		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');

		sportsmanagementModelProject::setProjectID($this->jinput->getInt('p', 0), $this->jinput->getInt('cfg_which_database', 0));

		$this->division   = sportsmanagementModelProject::getDivision(0, $this->jinput->getInt('cfg_which_database', 0));
		$this->matchid    = sportsmanagementModelEventsRanking::$matchid;
		$this->teamid     = $this->model->getTeamId();
		$this->teams      = sportsmanagementModelProject::getTeamsIndexedById(0, 'name', $this->jinput->getInt('cfg_which_database', 0));
		$this->favteams   = sportsmanagementModelProject::getFavTeams($this->jinput->getInt('cfg_which_database', 0));
		$this->eventtypes = sportsmanagementModelProject::getEventTypes(sportsmanagementModelEventsRanking::$eventid, $this->jinput->getInt('cfg_which_database', 0));
		$this->limit      = $this->model->getLimit();
		$this->limitstart = $this->model->getLimitStart();
		$this->pagination = $this->get('Pagination');

		if ($this->project->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART')
		{
			$this->eventranking = $this->model->getEventRankings($this->limit, $this->limitstart, null, true);
		}
		else
		{
			$this->eventranking = $this->model->getEventRankings($this->limit, $this->limitstart, null, false);
		}

		$this->multiple_events = count($this->eventtypes) > 1;

		$prefix = Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_PAGE_TITLE');

		if ($this->multiple_events)
		{
			$prefix .= " - " . Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_TITLE');
		}
		else
		{
			// Next query will result in an array with exactly 1 statistic id
			$evid = array_keys($this->eventtypes);

			// Selected one valid eventtype, so show its name
			$prefix .= " - " . Text::_($this->eventtypes[$evid[0]]->name);
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

		if (!isset($this->config['table_class']))
		{
			$this->config['table_class'] = 'table';
		}

	}

}
