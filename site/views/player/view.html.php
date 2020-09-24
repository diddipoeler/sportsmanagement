<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage player
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewPlayer
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPlayer extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPlayer::init()
	 *
	 * @return void
	 */
	function init()
	{
		$model                = $this->model;
		$model::$projectid    = $this->jinput->getInt('p', 0);
		$model::$personid     = $this->jinput->getInt('pid', 0);
		$model::$teamplayerid = $this->jinput->getInt('pt', 0);

		sportsmanagementModelProject::setProjectID($this->jinput->getInt('p', 0), $model::$cfg_which_database);

		$person   = sportsmanagementModelPerson::getPerson(0, $model::$cfg_which_database, 1);
		$nickname = isset($person->nickname) ? $person->nickname : "";

		if (!empty($nickname))
		{
			$nickname = "'" . $nickname . "'";
		}

		$this->isContactDataVisible = sportsmanagementModelPerson::isContactDataVisible($this->config['show_contact_team_member_only']);
		$this->person               = $person;
		$this->nickname             = $nickname;
		$this->teamPlayers          = $model->getTeamPlayers($model::$cfg_which_database);

		if (!isset($this->config['show_players_layout']))
		{
			$this->config['show_players_layout'] = 'no_tabs';
		}

		if (isset($this->overallconfig['person_events']))
		{
			/** alles ok */
		}
		else
		{
			$person_events = sportsmanagementModelEventtypes::getEvents($this->project->sports_type_id);

			if (is_array($person_events) || is_object($person_events))
			{
				foreach ($person_events as $events)
				{
					$this->overallconfig['person_events'][] = $events->value;
				}
			}
		}

		$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend', $model::$cfg_which_database);

		if ($this->checkextrafields)
		{
			$this->extrafields = sportsmanagementHelper::getUserExtraFields($person->id, 'frontend', $model::$cfg_which_database);
		}

		/** Select the teamplayer that is currently published (in case the player played in multiple teams in the project) */
		$teamPlayer = null;

		if (count($this->teamPlayers))
		{
			$currentProjectTeamId = 0;

			foreach ($this->teamPlayers as $teamPlayer)
			{
				if ($teamPlayer->published == 1)
				{
					$currentProjectTeamId = $teamPlayer->projectteam_id;
					break;
				}
			}

			if ($currentProjectTeamId)
			{
				$teamPlayer = $this->teamPlayers[$currentProjectTeamId];
			}
		}

		$sportstype = $this->config['show_plcareer_sportstype'] ? sportsmanagementModelProject::getSportsType($model::$cfg_which_database) : 0;

		$this->teamPlayer         = $teamPlayer;
		$this->historyPlayer      = $model->getPlayerHistory($sportstype, $this->config['historyorder'], 1, $model::$cfg_which_database);
		$this->historyPlayerStaff = $model->getPlayerHistory($sportstype, $this->config['historyorder'], 2, $model::$cfg_which_database);
		$this->AllEvents          = $model->getAllEvents($sportstype);
		$this->showediticon       = sportsmanagementModelPerson::getAllowed($this->config['edit_own_player']);
		$this->stats              = sportsmanagementModelProject::getProjectStats(0, 0, $model::$cfg_which_database);

		/** Get events and stats for current project */
		if ($this->config['show_gameshistory'])
		{
			$this->games       = $model->getGames();
			$this->teams       = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', $model::$cfg_which_database);
			$this->gamesevents = $model->getGamesEvents();
			$this->gamesstats  = $model->getPlayerStatsByGame();
		}

		/** Get events and stats for all projects where player played in (possibly restricted to sports type of current project) */
		if ($this->config['show_career_stats'])
		{
			$this->stats        = $model->getStats();
			$this->projectstats = $model->getPlayerStatsByProject($sportstype);
		}

		$this->extended = sportsmanagementHelper::getExtended($person->extended, 'player');
		unset($form_value);

		if ($this->extended)
		{
			$form_value = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_PARENT_POSITIONS');
		}

		/** nebenposition vorhanden ? */
		$this->person_parent_positions = $form_value;

		unset($form_value);

		if ($this->extended)
		{
			$form_value = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_POSITION');
		}

		if ($form_value)
		{
		}
		else
		{
			/** wenn beim spieler noch nichts gesetzt wurde dann nehmen wir die standards */
			switch ($this->teamPlayer->position_name)
			{
				case 'COM_SPORTSMANAGEMENT_SOCCER_P_DEFENDER':
					$form_value = 'hp2';
					break;
				case 'COM_SPORTSMANAGEMENT_SOCCER_P_FORWARD':
					$form_value = 'hp14';
					break;
				case 'COM_SPORTSMANAGEMENT_SOCCER_P_GOALKEEPER':
					$form_value = 'hp1';
					break;
				case 'COM_SPORTSMANAGEMENT_SOCCER_P_MIDFIELDER':
					$form_value = 'hp7';
					break;
			}
		}

		$this->person_position = $form_value;
		$this->hasDescription  = $this->teamPlayer->notes;

		foreach ($this->extended->getFieldsets() as $fieldset)
		{
			$hasData = false;
			$fields  = $this->extended->getFieldset($fieldset->name);

			foreach ($fields as $field)
			{
				$value = $field->value;

				if (!empty($value))
				{
					$hasData = true;
					break;
				}
			}
		}

		$this->hasExtendedData = $hasData;

		$hasStatus = false;

		if ((isset($this->teamPlayer->injury) && $this->teamPlayer->injury > 0)
			|| (isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension > 0)
			|| (isset($this->teamPlayer->away) && $this->teamPlayer->away > 0)
		)
		{
			$hasStatus = true;
		}

		$this->hasStatus = $hasStatus;

		if (isset($person))
		{
			$name = sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]);
		}

		$this->playername = $name;
		$this->document->setTitle(Text::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', $name));

		$view      = $this->jinput->getVar("view");
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $this->option . '/assets/css/' . $view . '.css' . '" type="text/css" />' . "\n";
		$this->document->addCustomTag($stylelink);

		if (!isset($this->config['table_class']))
		{
			$this->config['table_class'] = 'table';
		}

		if (!isset($this->config['show_players_layout']))
		{
			$this->config['show_players_layout'] = 'no_tabs';
		}

	}

}

