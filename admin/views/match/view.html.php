<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Log\Log;

jimport('joomla.environment.browser');
jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewMatch
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewMatch extends sportsmanagementView
{

	/**
	 * sportsmanagementViewMatch::init()
	 *
	 * @return
	 */
	public function init()
	{
		$browser      = JBrowser::getInstance();
		$this->config = ComponentHelper::getParams('com_media');

		$this->project_id = $this->app->getUserState("$this->option.pid", '0');

		//        $this->project_id = $project_id;
		$default_name_format = '';

		// Get the Data
		$this->form   = $this->get('Form');
		$this->item   = $this->get('Item');
		$this->script = $this->get('Script');

		$mdlProject      = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$this->projectws = $mdlProject->getProject($this->project_id);

		//        $this->projectws = $projectws;
		$this->eventsprojecttime = $this->projectws->game_regular_time;

		$this->match                = $this->model->getMatchData($this->item->id);
		$this->extended             = sportsmanagementHelper::getExtended($this->item->extended, 'match');
		$this->cfg_which_media_tool = ComponentHelper::getParams($this->option)->get('cfg_which_media_tool', 0);

		switch ($this->getLayout())
		{
			case 'pressebericht';
			case 'pressebericht_3';
			case 'pressebericht_4';
				$this->setLayout('pressebericht');
				break;
			case 'savepressebericht';
			case 'savepressebericht_3';
			case 'savepressebericht_4';
				$this->setLayout('savepressebericht');
				$this->_displaySavePressebericht();
				break;
			case 'readpressebericht';
			case 'readpressebericht_3';
			case 'readpressebericht_4';
				$this->setLayout('readpressebericht');
				$this->initPressebericht();
				break;
			case 'editreferees';
			case 'editreferees_3';
			case 'editreferees_4';
				$this->setLayout('editreferees');
				$this->initEditReferees();
				break;
			case 'editevents';
			case 'editevents_3';
			case 'editevents_4';
				$this->setLayout('editevents');
				$this->initEditEevents();
				break;
			case 'editeventsbb';
			case 'editeventsbb_3';
			case 'editeventsbb_4';
				$this->setLayout('editeventsbb');
				$this->initEditEeventsBB();
				break;
			case 'editstats';
			case 'editstats_3';
			case 'editstats_4';
				$this->setLayout('editstats');
				$this->initEditStats();
				break;
			case 'editlineup';
			case 'editlineup_3';
			case 'editlineup_4';
				$this->setLayout('editlineup');
				$this->initEditLineup();
				break;
			case 'edit';
			case 'edit_3';
			case 'edit_4';
				$this->initEdit();
				break;
			case 'picture';
			case 'picture_3';
			case 'picture_4';
				$this->setLayout('picture');
				$this->initPicture();
				break;
		}

	}

	/**
	 * sportsmanagementViewMatch::_displaySavePressebericht()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return void
	 */
	function _displaySavePressebericht()
	{
		$app        = Factory::getApplication();
		$jinput     = $app->input;
		$option     = $jinput->getCmd('option');
		$post       = $app->input->post->getArray(array());
		$project_id = $app->getUserState("$option.pid", '0');;
		$model         = $this->getModel();
		$csv_file_save = $model->savePressebericht($post);

		$this->importData = $model->_success_text;

		// Parent::display($tpl);
	}

	/**
	 * sportsmanagementViewMatch::initPressebericht()
	 *
	 * @return
	 */
	public function initPressebericht()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$model  = $this->getModel();

		$csv_file          = $model->getPressebericht();
		$this->csv         = $csv_file;
		$matchnumber       = $model->getPresseberichtMatchnumber($csv_file);
		$this->matchnumber = $matchnumber;
		$lists             = Array();

		if ($matchnumber)
		{
			$readplayers       = $model->getPresseberichtReadPlayers($csv_file);
			$this->csvreferees = $model->csv_referee;
			$this->csvplayers  = $model->csv_player;
			$this->csvinout    = $model->csv_in_out;
			$this->csvcards    = $model->csv_cards;
			$this->csvstaff    = $model->csv_staff;
		}

		/**
		 *
		 * build the html options for referee positions
		 */
		$position_id[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));

		if ($res = $model->getProjectPositionsOptions(0, 3, $this->project_id))
		{
			foreach ($res as $pos)
			{
				$pos->text = Text::_($pos->text);
			}

			$position_id = array_merge($position_id, $res);
		}

		$lists['referee_project_position_id'] = $position_id;
		unset($position_id);

		/**
		 *
		 * build the html options for player position
		 */
		$position_id[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));

		if ($res = $model->getProjectPositionsOptions(0, 1, $this->project_id))
		{
			foreach ($res as $pos)
			{
				$pos->text = Text::_($pos->text);
			}

			$position_id = array_merge($position_id, $res);
		}

		$lists['player_project_position_id']       = $position_id;
		$lists['player_inout_project_position_id'] = $position_id;
		unset($position_id);

		/**
		 *
		 * build the html options for staff position
		 */
		$position_id[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));

		if ($res = $model->getProjectPositionsOptions(0, 2, $this->project_id))
		{
			foreach ($res as $pos)
			{
				$pos->text = Text::_($pos->text);
			}

			$position_id = array_merge($position_id, $res);
		}

		$lists['staff_project_position_id'] = $position_id;
		unset($position_id);

		/**
		 *
		 * events
		 */
		$events = $model->getEventsOptions($this->project_id);

		if (!$events)
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS'), 'Error');
		}

		$eventlist   = array();
		$eventlist[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT'));
		$eventlist   = array_merge($eventlist, $events);

		$lists['events'] = $eventlist;
		unset($eventlist);

		/**
		 *
		 * build the html select booleanlist
		 */
		$myoptions                 = array();
		$myoptions[]               = HTMLHelper::_('select.option', '0', Text::_('JNO'));
		$myoptions[]               = HTMLHelper::_('select.option', '1', Text::_('JYES'));
		$lists['startaufstellung'] = $myoptions;

		$this->projectteamid = $model->projectteamid;
		$this->lists         = $lists;
		$this->setLayout('readpressebericht');
	}

	/**
	 * sportsmanagementViewMatch::initEditReferees()
	 *
	 * @return
	 */
	public function initEditReferees()
	{
		$app                 = Factory::getApplication();
		$jinput              = $app->input;
		$option              = $jinput->getCmd('option');
		$model               = $this->getModel();
		$default_name_format = '';
		$lists             = Array();
		$projectpositions = Array();

		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/sm_functions.js');
		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/startinglineup.js');

		// Projekt schiedsrichter
		$allreferees = array();

		// $allreferees = $model->getRefereeRoster(0,$this->item->id);
		$allreferees      = $model->getRefereeRoster(0, $this->item->id);
		$inroster         = array();
		$projectreferees  = array();
		$projectreferees2 = array();

		if (isset($allreferees))
		{
			foreach ($allreferees AS $referee)
			{
				$inroster[] = $referee->value;
			}
		}

		// Projekt positionen
		$selectpositions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_FUNCTION'));

		if ($projectpositions = $model->getProjectPositionsOptions(0, 3, $this->project_id))
		{
			$selectpositions = array_merge($selectpositions, $projectpositions);
		}

		$lists['projectpositions'] = HTMLHelper::_('select.genericlist', $selectpositions, 'project_position_id', 'class="inputbox" size="1"', 'value', 'text');

		$squad = array();

		$mdlProject      = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$this->projectws = $mdlProject->getProject($this->project_id);

		if ($this->projectws->teams_as_referees == 1)
		{
			$divhomeid        = 0;
			$projectreferees2 = $mdlProject->getProjectTeamsOptions($this->project_id, $divhomeid);

			foreach ($projectpositions AS $key => $pos)
			{
				// Get referees assigned to this position
				$squad[$key] = $model->getTeamsRefereeRoster($this->item->id);

				foreach ($squad[$key] as $referee)
				{
					foreach ($projectreferees2 as $keySearch => $projectreferee)
					{
						if ($referee->value == $projectreferee->value)
						{
							unset($projectreferees2[$keySearch]);
						}
					}
				}
			}
		}
		else
		{
			$projectreferees = $model->getProjectReferees($inroster, $this->project_id);

			if (count($projectreferees) > 0)
			{
				foreach ($projectreferees AS $referee)
				{
					$projectreferees2[] = HTMLHelper::_(
						'select.option', $referee->value,
						sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format) .
						' - (' . strtolower(Text::_($referee->positionname)) . ')'
					);
				}
			}

			if (!$projectpositions)
			{
				Log::add('<br />' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_REF_POS') . '<br /><br />', Log::WARNING, 'jsmerror');

				return;
			}

			// Generate selection list for each position
			foreach ($projectpositions AS $key => $pos)
			{
				// Get referees assigned to this position
				$squad[$key] = $model->getRefereeRoster($pos->value, $this->item->id);
			}
		}

		$lists['team_referees'] = HTMLHelper::_(
			'select.genericlist', $projectreferees2, 'roster[]',
			'style="font-size:12px;height:auto;min-width:15em;" ' .
			'class="inputbox" multiple="true" size="' . max(10, count($projectreferees2)) . '"',
			'value', 'text'
		);

		if (count($squad) > 0)
		{
			foreach ($squad AS $key => $referees)
			{
				$temp[$key] = array();

				if (isset($referees))
				{
					foreach ($referees AS $referee)
					{
						if ($this->projectws->teams_as_referees == 1)
						{
							$temp[$key][] = HTMLHelper::_('select.option', $referee->value, $referee->name);
						}
						else
						{
							$temp[$key][] = HTMLHelper::_(
								'select.option', $referee->value,
								sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format)
							);
						}
					}
				}

				$lists['team_referees' . $key] = HTMLHelper::_(
					'select.genericlist', $temp[$key], 'position' . $key . '[]',
					' style="font-size:12px;height:auto;min-width:15em;" ' .
					'class="position-starters" multiple="true" ',
					'value', 'text'
				);
			}
		}

		$this->positions = $projectpositions;
		$this->lists     = $lists;

		$this->setLayout('editreferees');
	}

	/**
	 * sportsmanagementViewMatch::initEditEevents()
	 *
	 * @return
	 */
	public function initEditEevents()
	{
		$app                              = Factory::getApplication();
		$jinput                           = $app->input;
		$option                           = $jinput->getCmd('option');
		$this->useeventtime               = $jinput->get('useeventtime');
		$model                            = $this->getModel();
		$params                           = ComponentHelper::getParams($option);
		$default_name_dropdown_list_order = $params->get("cfg_be_name_dropdown_list_order", "lastname");
		$default_name_format              = $params->get("name_format", 14);

		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/sm_functions.js');
		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/diddioeler.js');
		$this->document->addStyleSheet(Uri::base() . '/components/' . $option . '/assets/css/sportsmanagement.css');

		$javascript = "\n";
		$javascript .= "var baseajaxurl = '" . Uri::root() . "administrator/index.php?option=com_sportsmanagement';" . "\n";
		$javascript .= "var matchid = " . $this->item->id . ";" . "\n";
		$javascript .= "var useeventtime = " . $this->useeventtime . ";" . "\n";
		$javascript .= "var projecttime = " . $this->eventsprojecttime . ";" . "\n";
		$javascript .= "var str_delete = '" . Text::_('JACTION_DELETE') . "';" . "\n";

		$javascript .= 'jQuery(document).ready(function() {' . "\n";
		$javascript .= "updatePlayerSelect();" . "\n";
		$javascript .= "jQuery('#team_id').change(updatePlayerSelect);" . "\n";
		$javascript .= '  });' . "\n";
		$javascript .= "\n";

		// Mannschaften der paarung
		$teams = $model->getMatchTeams($this->item->id);

		$teamlist       = array();
		$teamlist[]     = HTMLHelper::_('select.option', $teams->projectteam1_id, $teams->team1);
		$teamlist[]     = HTMLHelper::_('select.option', $teams->projectteam2_id, $teams->team2);
		$lists['teams'] = HTMLHelper::_('select.genericlist', $teamlist, 'team_id', 'class="inputbox select-team" ');

		// Events
		$events = $model->getEventsOptions($this->project_id, 0);

		if (!$events)
		{
			Log::add('<br />' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS') . '<br /><br />', Log::WARNING, 'jsmerror');

			return;
		}

		$eventlist = array();
		$eventlist = array_merge($eventlist, $events);

		$lists['events'] = HTMLHelper::_('select.genericlist', $eventlist, 'event_type_id', 'class="inputbox select-event"');

		$homeRoster = $model->getMatchPersons($teams->projectteam1_id, 0, $this->item->id, 'player');

		if (count($homeRoster) == 0)
		{
			// $homeRoster=$model->getGhostPlayer();
		}

		$awayRoster = $model->getMatchPersons($teams->projectteam2_id, 0, $this->item->id, 'player');

		if (count($awayRoster) == 0)
		{
			// $awayRoster=$model->getGhostPlayer();
		}

		$rosters = array('home' => $homeRoster, 'away' => $awayRoster);

		$matchCommentary = $model->getMatchCommentary($this->item->id);
		$matchevents     = $model->getMatchEvents($this->item->id);
		$this->document->addScriptDeclaration($javascript);

		$this->matchevents                      = $matchevents;
		$this->matchcommentary                  = $matchCommentary;
		$this->teams                            = $teams;
		$this->rosters                          = $rosters;
		$this->lists                            = $lists;
		$this->default_name_format              = $default_name_format;
		$this->default_name_dropdown_list_order = $default_name_dropdown_list_order;

		$this->setLayout('editevents');

	}

	/**
	 * sportsmanagementViewMatch::initEditEeventsBB()
	 *
	 * @return
	 */
	public function initEditEeventsBB()
	{
		$app                 = Factory::getApplication();
		$jinput              = $app->input;
		$option              = $jinput->getCmd('option');
		$project_id          = $app->getUserState("$option.pid", '0');
		$params              = ComponentHelper::getParams($option);
		$default_name_format = $params->get("name_format");

		$model = $this->getModel();
		$teams = $model->getMatchTeams($this->item->id);

		$homeRoster = $model->getTeamPersons($teams->projectteam1_id, false, 1);
		$awayRoster = $model->getTeamPersons($teams->projectteam2_id, false, 1);

		// Events
		$events = $model->getEventsOptions($project_id, $this->item->id);

		if (!$events)
		{
			Log::add('<br />' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS') . '<br /><br />', Log::WARNING, 'jsmerror');

			return;
		}

		$this->homeRoster = $homeRoster;
		$this->awayRoster = $awayRoster;
		$this->teams      = $teams;
		$this->events     = $events;

		$this->addToolbar_Editeventsbb();

		$this->setLayout('editeventsbb');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar_Editeventsbb()
	{
		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_EEBB_TITLE'), 'events');
		ToolbarHelper::apply('match.saveeventbb');
		ToolbarHelper::divider();
		ToolbarHelper::back('back', 'index.php?option=com_sportsmanagement&view=matches&task=match.display');

		// JLToolBarHelper::onlinehelp();
	}

	/**
	 * sportsmanagementViewMatch::initEditStats()
	 *
	 * @return
	 */
	public function initEditStats()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$model  = $this->getModel();
		$lists  = array();

		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/sm_functions.js');
		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/editmatchstats.js');
		$teams = $model->getMatchTeams($this->item->id);

		$positions      = $model->getProjectPositionsOptions(0, 1, $this->project_id);
		$staffpositions = $model->getProjectPositionsOptions(0, 2, $this->project_id);

		$homeRoster = $model->getMatchPersons($teams->projectteam1_id, 0, $this->item->id, 'player');

		if (count($homeRoster) == 0)
		{
			// $homeRoster=$model->getGhostPlayer();
		}

		$awayRoster = $model->getMatchPersons($teams->projectteam2_id, 0, $this->item->id, 'player');

		if (count($awayRoster) == 0)
		{
			// $awayRoster=$model->getGhostPlayer();
		}

		$homeStaff = $model->getMatchPersons($teams->projectteam1_id, 0, $this->item->id, 'staff');
		$awayStaff = $model->getMatchPersons($teams->projectteam2_id, 0, $this->item->id, 'staff');

		// Stats
		$stats = $model->getInputStats($this->project_id);

		if (!$stats)
		{
			Log::add('<br />' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_STATS_POS') . '<br /><br />', Log::WARNING, 'jsmerror');
		}

		$playerstats = $model->getMatchStatsInput($this->item->id, $teams->projectteam1_id, $teams->projectteam2_id);
		$staffstats  = $model->getMatchStaffStatsInput($this->item->id, $teams->projectteam1_id, $teams->projectteam2_id);

		$this->playerstats    = $playerstats;
		$this->staffstats     = $staffstats;
		$this->stats          = $stats;
		$this->homeStaff      = $homeStaff;
		$this->awayStaff      = $awayStaff;
		$this->positions      = $positions;
		$this->staffpositions = $staffpositions;
		$this->homeRoster     = $homeRoster;
		$this->awayRoster     = $awayRoster;
		$this->teams          = $teams;
		$this->lists          = $lists;

		$this->setLayout('editstats');
	}

	/**
	 * sportsmanagementViewMatch::initEditLineup()
	 *
	 * @return
	 */
	public function initEditLineup()
	{
		$app                 = Factory::getApplication();
		$jinput              = $app->input;
		$option              = $jinput->getCmd('option');
		$model               = $this->getModel();
		$default_name_format = '';
		$lists               = array();

		$this->document->addStyleSheet(Uri::base() . '/components/' . $option . '/assets/css/sportsmanagement.css');
		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/sm_functions.js');
		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/diddioeler.js');
		$tid                       = Factory::getApplication()->input->getVar('team', '0');
		$match                     = $model->getMatchTeams($this->item->id);
		$teamname                  = ($tid == $match->projectteam1_id) ? $match->team1 : $match->team2;
		$this->teamname            = $teamname;
		$this->preFillSuccess      = false;
		$this->positions           = false;
		$this->substitutions       = false;
		$this->staffpositions      = false;
		$lists['team_players']     = '';
		$lists['team_staffs']      = '';
		$lists['projectpositions'] = '';
		$playersoptionsout         = array();
		$playersoptionsout[]       = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_OUT'));
		$playersoptionsin          = array();
		$playersoptionsin[]        = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_IN'));

		// Get starters
		$starters    = $model->getMatchPersons($tid, 0, $this->item->id, 'player');
		$starters_id = array_keys($starters);

		// Get players not already assigned to starter
		$not_assigned = $model->getTeamPersons($tid, $starters_id, 1);

		if (!$not_assigned && !$starters_id)
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_PLAYERS_MATCH'), '');
			$this->playersoptionsin  = $playersoptionsin;
			$this->playersoptionsout = $playersoptionsout;
			$this->lists             = $lists;

			return;
		}

		$projectpositions = $model->getProjectPositionsOptions(0, 1, $this->project_id);

		if (!$projectpositions)
		{
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_POS'), '');
			$this->playersoptionsin  = $playersoptionsin;
			$this->playersoptionsout = $playersoptionsout;
			$this->lists             = $lists;

			return;
		}

		// Build select list for not assigned players
		$not_assigned_options = array();

		foreach ((array) $not_assigned AS $p)
		{
			$not_assigned_options[] = HTMLHelper::_(
				'select.option', $p->value, '[' . $p->jerseynumber . '] ' .
				sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) .
				' - (' . Text::_($p->positionname) . ')'
			);
		}

		$lists['team_players'] = HTMLHelper::_(
			'select.genericlist', $not_assigned_options, 'roster[]',
			'style="font-size:12px;height:auto;min-width:15em;" class="inputbox" multiple="true" size="18"',
			'value', 'text'
		);

		// Build position select
		$selectpositions[]         = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_IN_POSITION'));
		$selectpositions           = array_merge($selectpositions, $model->getProjectPositionsOptions(0, 1, $this->project_id));
		$lists['projectpositions'] = HTMLHelper::_('select.genericlist', $selectpositions, 'project_position_id', 'class="inputbox" size="1"', 'posid', 'text', null, false, true);

		// Build player select
		// $allplayers = $model->getTeamPlayers($tid);
		$allplayers = $model->getTeamPersons($tid, false, 1);

		foreach ((array) $starters AS $player) // Foreach ((array)$allplayers AS $player)
		{
			$playersoptionsout[] = HTMLHelper::_(
				'select.option', $player->value,
				sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format) . ' - (' . Text::_($player->positionname) . ')'
			);
		}

		foreach ((array) $not_assigned AS $player) // Foreach ((array)$allplayers AS $player)
		{
			$playersoptionsin[] = HTMLHelper::_(
				'select.option', $player->value,
				sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $default_name_format) . ' - (' . Text::_($player->positionname) . ')'
			);
		}

		// Generate selection list for each position
		$starters = array();

		foreach ($projectpositions AS $position_id => $pos)
		{
			// Get players assigned to this position
			$starters[$position_id] = $model->getRoster($tid, $pos->value, $this->item->id, $pos->text);
		}

		foreach ($starters AS $position_id => $players)
		{
			$options = array();

			foreach ((array) $players AS $p)
			{
				$options[] = HTMLHelper::_(
					'select.option', $p->value, '[' . $p->jerseynumber . '] ' .
					sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format)
				);
			}

			$lists['team_players' . $position_id] = HTMLHelper::_(
				'select.genericlist', $options, 'position' . $position_id . '[]',
				'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-starters" multiple="true" ',
				'value', 'text'
			);
		}

		$substitutions = $model->getSubstitutions($tid, $this->item->id);

		/**
		 * staff positions
		 */
		$staffpositions = $model->getProjectPositionsOptions(0, 2, $this->project_id);    // Get staff not already assigned to starter

		// Assigned staff
		$assigned    = $model->getMatchPersons($tid, 0, $this->item->id, 'staff');
		$assigned_id = array_keys($assigned);

		// Not assigned staff
		$not_assigned = $model->getTeamPersons($tid, $assigned_id, 2);

		// Build select list for not assigned
		$not_assigned_options = array();

		foreach ((array) $not_assigned AS $p)
		{
			$not_assigned_options[] = HTMLHelper::_(
				'select.option', $p->value,
				sportsmanagementHelper::formatName(null, $p->firstname, $p->nickname, $p->lastname, $default_name_format) . ' - (' . Text::_($p->positionname) . ')'
			);
		}

		$lists['team_staffs'] = HTMLHelper::_(
			'select.genericlist', $not_assigned_options, 'staff[]',
			'style="font-size:12px;height:auto;min-width:15em;" size="18" class="inputbox" multiple="true" size="18"',
			'value', 'text'
		);

		// Generate selection list for each position
		$options = array();

		foreach ($staffpositions AS $position_id => $pos)
		{
			// Get players assigned to this position
			$options = array();

			foreach ($assigned as $staff)
			{
				if ($staff->position_id == $pos->pposid) // If ($staff->pposid == $pos->pposid)
				{
					$options[] = HTMLHelper::_(
						'select.option', $staff->team_staff_id,
						sportsmanagementHelper::formatName(null, $staff->firstname, $staff->nickname, $staff->lastname, $default_name_format)
					);
				}
			}

			$lists['team_staffs' . $position_id] = HTMLHelper::_(
				'select.genericlist', $options, 'staffposition' . $position_id . '[]',
				'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-staff" multiple="true" ',
				'value', 'text'
			);
		}

		// Build the html select booleanlist
		$myoptions        = array();
		$myoptions[]      = HTMLHelper::_('select.option', '0', Text::_('JNO'));
		$myoptions[]      = HTMLHelper::_('select.option', '1', Text::_('JYES'));
		$lists['captain'] = $myoptions;

		$this->positions         = $projectpositions;
		$this->staffpositions    = $staffpositions;
		$this->substitutions     = $substitutions[$tid];
		$this->playersoptionsin  = $playersoptionsin;
		$this->playersoptionsout = $playersoptionsout;
		$this->tid               = $tid;

		// $this->teamname   = $teamname;
		$this->starters = $starters;
		$this->lists    = $lists;

		$javascript = "\n";
		$javascript .= "var baseajaxurl = '" . Uri::root() . "administrator/index.php?option=com_sportsmanagement';" . "\n";
		$javascript .= "var matchid = " . $this->item->id . ";" . "\n";
		$javascript .= "var teamid = " . $this->tid . ";" . "\n";
		$javascript .= "var projecttime = " . $this->eventsprojecttime . ";" . "\n";
		$javascript .= "var str_delete = '" . Text::_('JACTION_DELETE') . "';" . "\n";
		$this->document->addScriptDeclaration($javascript);

		$this->setLayout('editlineup');
	}

	/**
	 * sportsmanagementViewMatch::initEdit()
	 *
	 * @return void
	 */
	public function initEdit()
	{

		// Match relation tab
		$oldmatches [] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_OLD_MATCH'));
		$res           = array();
		$new_match_id  = ($this->item->new_match_id) ? $this->item->new_match_id : 0;

		if ($res = $this->model->getMatchRelationsOptions($this->project_id, $this->item->id . "," . $new_match_id))
		{
			foreach ($res as $m)
			{
				if (is_object($m->match_date))
				{
					$m->text = '(' . sportsmanagementHelper::getMatchStartTimestamp($m) . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
				}
				else
				{
					$m->text = '(' . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
				}
			}

			$oldmatches = array_merge($oldmatches, $res);
		}

		$lists ['old_match'] = HTMLHelper::_('select.genericlist', $oldmatches, 'old_match_id', 'class="inputbox" size="1"', 'value', 'text', $this->item->old_match_id);

		$newmatches [] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NEW_MATCH'));
		$res           = array();
		$old_match_id  = ($this->item->old_match_id) ? $this->item->old_match_id : 0;

		if ($res = $this->model->getMatchRelationsOptions($this->project_id, $this->item->id . "," . $old_match_id))
		{
			foreach ($res as $m)
			{
				if (is_object($m->match_date))
				{
					$m->text = '(' . sportsmanagementHelper::getMatchStartTimestamp($m) . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
				}
				else
				{
					$m->text = '(' . ') - ' . $m->t1_name . ' - ' . $m->t2_name;
				}
			}

			$newmatches = array_merge($newmatches, $res);
		}

		$lists ['new_match'] = HTMLHelper::_('select.genericlist', $newmatches, 'new_match_id', 'class="inputbox" size="1"', 'value', 'text', $this->item->new_match_id);

		//        /** build the html select booleanlist which team got the won */
		//        $myoptions = array();
		//        $myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NO_TEAM'));
		//        $myoptions[] = HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'));
		//        $myoptions[] = HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'));
		//        $myoptions[] = HTMLHelper::_('select.option', '3', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_LOSS_BOTH_TEAMS'));
		//        $myoptions[] = HTMLHelper::_('select.option', '4', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_WON_BOTH_TEAMS'));
		//        $lists['team_won'] = HTMLHelper::_('select.genericlist', $myoptions, 'team_won', 'class="inputbox" size="1"', 'value', 'text', $this->item->team_won);
$mdlPlayground      = BaseDatabaseModel::getInstance("Playgrounds", "sportsmanagementModel");
		$this->playgrounds = $mdlPlayground->getPlaygrounds(true);
		
		$this->lists = $lists;
		$this->setLayout('edit');

	}

	/**
	 * sportsmanagementViewMatch::initPicture()
	 *
	 * @return void
	 */
	public function initPicture()
	{
		$this->setLayout('picture');
	}

	/**
	 * sportsmanagementViewMatch::_displayPressebericht()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return
	 */
	function _displayPressebericht()
	{
		$app        = Factory::getApplication();
		$jinput     = $app->input;
		$option     = $jinput->getCmd('option');
		$project_id = $app->getUserState("$option.pid", '0');;
		$config       = ComponentHelper::getParams('com_media');
		$this->config = $config;

		$model             = $this->getModel();
		$csv_file          = $model->getPressebericht();
		$this->csv         = $csv_file;
		$matchnumber       = $model->getPresseberichtMatchnumber($csv_file);
		$this->matchnumber = $matchnumber;

		if ($matchnumber)
		{
			$readplayers      = $model->getPresseberichtReadPlayers($csv_file);
			$this->csvplayers = $model->csv_player;
			$this->csvinout   = $model->csv_in_out;
			$this->csvcards   = $model->csv_cards;
			$this->csvstaff   = $model->csv_staff;
		}

		// Build the html options for position
		$position_id[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));

		if ($res = $model->getProjectPositionsOptions(0, 1))
		{
			$position_id = array_merge($position_id, $res);
		}

		$lists['project_position_id'] = $position_id;
		$lists['inout_position_id']   = $position_id;
		unset($position_id);

		$position_id[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_POSITION'));

		if ($res = $model->getProjectPositionsOptions(0, 2))
		{
			$position_id = array_merge($position_id, $res);
		}

		$lists['project_staff_position_id'] = $position_id;
		unset($position_id);

		// Events
		$events = $model->getEventsOptions($project_id);

		if (!$events)
		{
			Log::add('<br />' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS') . '<br /><br />', Log::WARNING, 'jsmerror');

			return;
		}

		$eventlist   = array();
		$eventlist[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT'));
		$eventlist   = array_merge($eventlist, $events);

		$lists['events'] = $eventlist;
		unset($eventlist);

		// Build the html select booleanlist
		$myoptions                 = array();
		$myoptions[]               = HTMLHelper::_('select.option', '0', Text::_('JNO'));
		$myoptions[]               = HTMLHelper::_('select.option', '1', Text::_('JYES'));
		$lists['startaufstellung'] = $myoptions;

		$this->lists = $lists;

		parent::display($tpl);
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$isNew = $this->item->id == 0;
		$this->document->setTitle($isNew ? Text::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : Text::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
		$this->document->addScript(Uri::root() . $this->script);
		$this->document->addScript(Uri::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		Text::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		// Set toolbar items for the page
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
		$this->document->addCustomTag($stylelink);

		$jinput = Factory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user   = Factory::getUser();
		$userId = $user->id;
		$isNew  = $this->item->id == 0;
		$canDo  = sportsmanagementHelper::getActions($this->item->id);
		ToolbarHelper::title($isNew ? Text::_('COM_SPORTSMANAGEMENT_MATCH_NEW') : Text::_('COM_SPORTSMANAGEMENT_MATCH_EDIT'), 'match');

		// Built the actions for new and existing records.
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				ToolbarHelper::apply('match.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('match.save', 'JTOOLBAR_SAVE');
				ToolbarHelper::custom('match.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			ToolbarHelper::cancel('match.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				ToolbarHelper::apply('match.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('match.save', 'JTOOLBAR_SAVE');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					ToolbarHelper::custom('match.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}

			if ($canDo->get('core.create'))
			{
				ToolbarHelper::custom('match.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}

			ToolbarHelper::cancel('match.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}

