<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage roster
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * sportsmanagementViewRoster
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewRoster extends sportsmanagementView
{

	/**
	 * sportsmanagementViewRoster::init()
	 *
	 * @return void
	 */
	function init()
	{

		sportsmanagementModelRoster::$seasonid = $this->project->season_id;

		$this->projectteam    = $this->model->getProjectTeam($this->config['team_picture_which']);
		$this->lastseasondate = $this->model->getLastSeasonDate();
		$this->projectpositions = sportsmanagementModelProject::getProjectPositions();

		$type      = $this->jinput->getVar("type", 0);
		$typestaff = $this->jinput->getVar("typestaff", 0);

		if (!$type)
		{
			$type = $this->config['show_players_layout'];
		}

		if (!$typestaff)
		{
			$typestaff = $this->config['show_staff_layout'];
		}

		$this->type      = $type;
		$this->typestaff = $typestaff;

		$this->config['show_players_layout'] = $type;
		$this->config['show_staff_layout']   = $typestaff;

		if ($this->projectteam)
		{
			$this->team = $this->model->getTeam();
			$this->rows = $this->model->getTeamPlayers(1);

			/** Events */
			if ($this->config['show_events_stats'])
			{
				$this->positioneventtypes = $this->model->getPositionEventTypes();

				if ($this->project->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART')
				{
					$this->playereventstats     = $this->model->getPlayerEventStats(true, true);
					$this->playereventstatsdart = $this->model->getPlayerEventStats(true, false);
				}
				else
				{
					$this->playereventstats = $this->model->getPlayerEventStats(false, false);
				}
			}

			/** Stats */
			if ($this->config['show_stats'])
			{
				$this->stats       = sportsmanagementModelProject::getProjectStats(0, 0, sportsmanagementModelRoster::$cfg_which_database);
				$this->playerstats = $this->model->getRosterStats();
			}

			$this->stafflist = $this->model->getTeamPlayers(2);

			/** Set page title */
			$this->document->setTitle(Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE', $this->team->name));
		}
		else
		{
			/** Set page title */
			$this->document->setTitle(Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE', Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_TEAM')));
		}

		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $this->option . '/assets/css/' . $this->view . '.css' . '" type="text/css" />' . "\n";
		$this->document->addCustomTag($stylelink);

		/** Select roster view */
		$opp_arr   = array();
		$opp_arr[] = HTMLHelper::_('select.option', "player_standard", Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION1_PLAYER_STANDARD'));
		$opp_arr[] = HTMLHelper::_('select.option', "player_card", Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION2_PLAYER_CARD'));
		$opp_arr[] = HTMLHelper::_('select.option', "player_johncage", Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION3_PLAYER_CARD'));

		$lists['type'] = $opp_arr;

		/** Select staff view */
		$opp_arr   = array();
		$opp_arr[] = HTMLHelper::_('select.option', "staff_standard", Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION1_STAFF_STANDARD'));
		$opp_arr[] = HTMLHelper::_('select.option', "staff_card", Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION2_STAFF_CARD'));
		$opp_arr[] = HTMLHelper::_('select.option', "staff_johncage", Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION3_STAFF_CARD'));

		$lists['typestaff'] = $opp_arr;
		$this->lists        = $lists;

		if (!isset($this->config['table_class']))
		{
			$this->config['table_class'] = 'table';
		}
	
	}

}
