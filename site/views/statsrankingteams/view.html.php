<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statsrankingteams
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewStatsRankingTeams
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewStatsRankingTeams extends sportsmanagementView
{

	function init()
	{
		   $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
		sportsmanagementModelProject::setProjectID($this->jinput->getInt('p', 0), $this->cfg_which_database);

		  $teams = sportsmanagementModelProject::getTeamsIndexedById(0, 'name', $this->cfg_which_database);
		  $this->teams = $teams;
		$this->stats = $this->model->getProjectUniqueStats();
		$this->playersstats = $this->model->getPlayersStats();
		$this->teamsstats = $this->model->getTeamsStats();
		$this->teamstotal = $this->model->getTeamsTotal($this->teamsstats);

	}

}
