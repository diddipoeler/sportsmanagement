<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allprojectrounds
 * @file       allprojectrounds.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper');

/**
 * sportsmanagementModelallprojectrounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelallprojectrounds extends BaseDatabaseModel
{
	var $projectid = 0;

	var $project_ids = 0;

	var $project_ids_array = array();

	var $round = 0;

	var $rounds = array(0);

	// 	var $part = 0;
	// 	var $type = 0;
	// 	var $from = 0;
	// 	var $to = 0;
	// 	var $divLevel = 0;
	var $ProjectTeams = array();

	var $previousRanking = array();

	// 	var $homeRank = array();
	// 	var $awayRank = array();
	var $colors = array();

	var $result = array();

	var $projectteam_id = 0;

	var $matchid = 0;

	var $_playersevents = array();

	/**
	 * parameters
	 *
	 * @var array
	 */
	var $_params = null;

	/**
	 * sportsmanagementModelallprojectrounds::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput                                    = $app->input;
		$this->projectid                           = $jinput->request->get('p', 0, 'INT');
		sportsmanagementModelProject::$projectid   = $this->projectid;
		sportsmanagementModelProject::$projectslug = $this->projectid;

		$this->_params['Itemid']             = $jinput->request->get('Itemid', 0, 'INT');
		$this->_params['show_columns']       = $jinput->request->get('show_columns', 0, 'INT');
		$this->_params['show_sectionheader'] = $jinput->request->get('show_sectionheader', 0, 'INT');

		$this->_params['show_firstroster']  = $jinput->request->get('show_firstroster', 0, 'INT');
		$this->_params['show_firstsubst']   = $jinput->request->get('show_firstsubst', 0, 'INT');
		$this->_params['show_firstevents']  = $jinput->request->get('show_firstevents', 0, 'INT');
		$this->_params['show_secondroster'] = $jinput->request->get('show_secondroster', 0, 'INT');
		$this->_params['show_secondsubst']  = $jinput->request->get('show_secondsubst', 0, 'INT');
		$this->_params['show_secondevents'] = $jinput->request->get('show_secondevents', 0, 'INT');

		$this->_params['s']           = $jinput->request->get('s', 0, 'INT');
		$this->_params['p']           = $jinput->request->get('p', 0, 'INT');
		$this->_params['table_class'] = $jinput->request->get('table_class', 'table', 'STR');

		$this->_params['view']   = $jinput->request->get('view', 'allprojectrounds', 'STR');
		$this->_params['option'] = $jinput->request->get('option', 'com_sportsmanagement', 'STR');

		parent::__construct();
	}


	/**
	 * sportsmanagementModelallprojectrounds::getProjectMatches()
	 *
	 * @return
	 */
	function getProjectMatches()
	{
		$app = Factory::getApplication();

		// Get a db connection.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$result = array();

		$query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present');
		$query->select('playground.name AS playground_name');
		$query->select('playground.short_name AS playground_short_name');
		$query->select('r.name as round_name,r.roundcode as roundcode');
		$query->select('t1.name as home_name,t1.short_name as home_short_name,t1.middle_name as home_middle_name');
		$query->select('t2.name as away_name,t2.short_name as away_short_name,t2.middle_name as away_middle_name');
		$query->select('tt1.project_id, d1.name as divhome, d2.name as divaway');
		$query->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug ');
		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', ' #__sportsmanagement_round AS r ON m.round_id = r.id ');

		$query->join('LEFT', ' #__sportsmanagement_project_team AS tt1 ON m.projectteam1_id = tt1.id');
		$query->join('LEFT', ' #__sportsmanagement_project_team AS tt2 ON m.projectteam2_id = tt2.id ');

		$query->join('LEFT', ' #__sportsmanagement_season_team_id as st1 ON st1.id = tt1.team_id ');
		$query->join('LEFT', ' #__sportsmanagement_season_team_id as st2 ON st2.id = tt2.team_id ');

		$query->join('LEFT', ' #__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		$query->join('LEFT', ' #__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
		$query->join('LEFT', ' #__sportsmanagement_division AS d1 ON m.division_id = d1.id');
		$query->join('LEFT', ' #__sportsmanagement_division AS d2 ON m.division_id = d2.id');
		$query->join('LEFT', ' #__sportsmanagement_playground AS playground ON playground.id = m.playground_id');

		$query->where('m.published = 1');
		$query->where('r.project_id =' . (int) $this->projectid);
		$query->group('m.id ');
		$query->order('r.roundcode ASC,m.match_date ASC,m.match_number');

		$db->setQuery($query);

		if (!$result = $db->loadObjectList())
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES'), Log::INFO, 'jsmerror');
		}

		$this->result = $result;

		return $result;

	}

	/**
	 * sportsmanagementModelallprojectrounds::getProjectTeamID()
	 *
	 * @param   mixed  $favteams
	 *
	 * @return
	 */
	function getProjectTeamID($favteams)
	{
		$app = Factory::getApplication();

		// Get a db connection.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		foreach ($favteams as $key => $value)
		{
			$query->clear();
			$query->select('id');
			$query->from('#__sportsmanagement_project_team ');
			$query->where('project_id =' . $this->projectid);
			$query->where('team_id =' . $value);

			try
			{
				$db->setQuery($query);
				$this->ProjectTeams[$value] = $db->loadResult();
			}
			catch (Exception $e)
			{
				Log::add(Text::_('COM_SPORTSMANAGEMENT_RANKING_NO_FAVTEAM'), Log::INFO, 'jsmerror');
			}
		}

		return $this->ProjectTeams;
	}

	/**
	 * sportsmanagementModelallprojectrounds::getAllRoundsParams()
	 *
	 * @return
	 */
	function getAllRoundsParams()
	{
		return $this->_params;
	}

	/**
	 * sportsmanagementModelallprojectrounds::getRoundsColumn()
	 *
	 * @param   mixed  $rounds
	 * @param   mixed  $config
	 *
	 * @return
	 */
	function getRoundsColumn($rounds, $config)
	{
		$app = Factory::getApplication();

		if (count($rounds) % 2)
		{
			//   echo "Zahl ist ungrade<br>";
		}
		else
		{
			//   echo "Zahl ist gerade<br>";
			$countrows = count($rounds) / 2;

			//   echo 'wir haben '.$countrows.' spieltage pro spalte<br>';
		}

		if ($config['show_columns'] == 1)
		{
		}
		else
		{
			$countrows = count($rounds);
		}

		$lfdnumber   = 0;
		$htmlcontent = array();
		$content     = '<table class="' . $this->_params['table_class'] . '">';

		for ($a = 0; $a < $countrows; $a++)
		{
			if ($config['show_columns'] == 1)
			{
				// Zwei spalten
				$secondcolumn = $a + $countrows;

				$htmlcontent[$a]['header'] = '';
				$htmlcontent[$a]['first']  = '<table class="' . $this->_params['table_class'] . '">';
				$htmlcontent[$a]['second'] = '<table class="' . $this->_params['table_class'] . '">';
				$htmlcontent[$a]['header'] = '<thead><tr><th colspan="" >' . $rounds[$a]->name . '</th><th colspan="" >' . $rounds[$secondcolumn]->name . '</th></tr></thead>';

				$roundcode       = $a + 1;
				$secondroundcode = $a + 1 + $countrows;

				foreach ($this->result as $match)
				{
					if ((int) $match->roundcode === (int) $roundcode)
					{
						$htmlcontent[$a]['first'] .= '<tr><td>' . $match->home_name . '</td>';
						$htmlcontent[$a]['first'] .= '<td>' . $match->team1_result . '</td>';
						$htmlcontent[$a]['first'] .= '<td>' . $match->team2_result . '</td>';
						$htmlcontent[$a]['first'] .= '<td>' . $match->away_name . '</td></tr>';

						foreach ($this->ProjectTeams as $key => $value)
						{
							if ((int) $match->projectteam1_id === (int) $value || (int) $match->projectteam2_id === (int) $value)
							{
								if ($config['show_firstroster'])
								{
									$htmlcontent[$a]['firstroster'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE-UP') . ' : </b>';
									$this->matchid                  = $match->id;
									$this->projectteam_id           = $value;
									$htmlcontent[$a]['firstroster'] .= implode(",", self::getMatchPlayers());
									$htmlcontent[$a]['firstroster'] .= '';
								}

								if ($config['show_firstsubst'])
								{
									$htmlcontent[$a]['firstsubst'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES') . ' : </b>';
									$this->matchid                 = $match->id;
									$this->projectteam_id          = $value;
									$htmlcontent[$a]['firstsubst'] .= implode(",", self::getSubstitutes());
									$htmlcontent[$a]['firstsubst'] .= '';
								}

								if ($config['show_firstevents'])
								{
									$htmlcontent[$a]['firstevents'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS') . ' : </b>';
									$this->matchid                  = $match->id;
									$this->projectteam_id           = $value;
									$htmlcontent[$a]['firstevents'] .= implode(",", self::getPlayersEvents());
									$htmlcontent[$a]['firstevents'] .= '';
								}
							}
						}
					}

					if ((int) $match->roundcode === (int) $secondroundcode)
					{
						$htmlcontent[$a]['second'] .= '<tr><td>' . $match->home_name . '</td>';
						$htmlcontent[$a]['second'] .= '<td>' . $match->team1_result . '</td>';
						$htmlcontent[$a]['second'] .= '<td>' . $match->team2_result . '</td>';
						$htmlcontent[$a]['second'] .= '<td>' . $match->away_name . '</td></tr>';

						foreach ($this->ProjectTeams as $key => $value)
						{
							if ((int) $match->projectteam1_id === (int) $value || (int) $match->projectteam2_id === (int) $value)
							{
								if ($config['show_secondroster'])
								{
									$htmlcontent[$a]['secondroster'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE-UP') . ' : </b>';
									$this->matchid                   = $match->id;
									$this->projectteam_id            = $value;
									$htmlcontent[$a]['secondroster'] .= implode(",", $this->getMatchPlayers());
									$htmlcontent[$a]['secondroster'] .= '';
								}

								if ($config['show_secondsubst'])
								{
									$htmlcontent[$a]['secondsubst'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES') . ' : </b>';
									$this->matchid                  = $match->id;
									$this->projectteam_id           = $value;
									$htmlcontent[$a]['secondsubst'] .= implode(",", $this->getSubstitutes());
									$htmlcontent[$a]['secondsubst'] .= '';
								}

								if ($config['show_secondevents'])
								{
									$htmlcontent[$a]['secondevents'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS') . ' : </b>';
									$this->matchid                   = $match->id;
									$this->projectteam_id            = $value;
									$htmlcontent[$a]['secondevents'] .= implode(",", $this->getPlayersEvents());
									$htmlcontent[$a]['secondevents'] .= '';
								}
							}
						}
					}
				}

				$htmlcontent[$a]['first']  .= '</table>';
				$htmlcontent[$a]['second'] .= '</table>';
			}
			else
			{
				// Nur eine spalte
				$htmlcontent[$a]['header'] = '';
				$htmlcontent[$a]['first']  = '<table class="' . $this->_params['table_class'] . '">';
				$htmlcontent[$a]['header'] = '<thead><tr><th colspan="" >' . $rounds[$a]->name . '</th></tr></thead>';
				$roundcode                 = $a + 1;

				foreach ($this->result as $match)
				{
					if ((int) $match->roundcode === (int) $roundcode)
					{
						$htmlcontent[$a]['first'] .= '<tr><td width="45%">' . $match->home_name . '</td>';
						$htmlcontent[$a]['first'] .= '<td width="5%">' . $match->team1_result . '</td>';
						$htmlcontent[$a]['first'] .= '<td width="5%">' . $match->team2_result . '</td>';
						$htmlcontent[$a]['first'] .= '<td width="45%">' . $match->away_name . '</td></tr>';

						foreach ($this->ProjectTeams as $key => $value)
						{
							if ((int) $match->projectteam1_id === (int) $value || (int) $match->projectteam2_id === (int) $value)
							{
								$htmlcontent[$a]['firstroster'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE-UP') . ' : </b>';
								$this->matchid                  = $match->id;
								$this->projectteam_id           = $value;
								$htmlcontent[$a]['firstroster'] .= implode(",", $this->getMatchPlayers());
								$htmlcontent[$a]['firstroster'] .= '';

								$htmlcontent[$a]['firstsubst'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES') . ' : </b>';
								$this->matchid                 = $match->id;
								$this->projectteam_id          = $value;
								$htmlcontent[$a]['firstsubst'] .= implode(",", $this->getSubstitutes());
								$htmlcontent[$a]['firstsubst'] .= '';

								$htmlcontent[$a]['firstevents'] = '<b>' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS') . ' : </b>';
								$this->matchid                  = $match->id;
								$this->projectteam_id           = $value;
								$htmlcontent[$a]['firstevents'] .= implode(",", $this->getPlayersEvents());
								$htmlcontent[$a]['firstevents'] .= '';
							}
						}
					}
				}

				$htmlcontent[$a]['first'] .= '</table>';
			}
		}

		if ($htmlcontent)
		{
			foreach ($htmlcontent as $key => $value)
			{
				if ($config['show_columns'] == 1)
				{
					$content .= $value['header'];
					$content .= '<tr><td>' . $value['first'] . '</td>';
					$content .= '<td>' . $value['second'] . '</td></tr>';

					if (array_key_exists('firstroster', $value))
					{
						$content .= '<tr><td>' . $value['firstroster'] . '</td>';
					}

					if (array_key_exists('secondroster', $value))
					{
						$content .= '<td>' . $value['secondroster'] . '</td></tr>';
					}

					if (array_key_exists('firstsubst', $value))
					{
						$content .= '<tr><td>' . $value['firstsubst'] . '</td>';
					}

					if (array_key_exists('secondsubst', $value))
					{
						$content .= '<td>' . $value['secondsubst'] . '</td></tr>';
					}

					if (array_key_exists('firstevents', $value))
					{
						$content .= '<tr><td>' . $value['firstevents'] . '</td>';
					}

					if (array_key_exists('secondevents', $value))
					{
						$content .= '<td>' . $value['secondevents'] . '</td></tr>';
					}
				}
				else
				{
					$content .= $value['header'];
					$content .= '<tr><td>' . $value['first'] . '</td></tr>';

					if (array_key_exists('firstroster', $value))
					{
						$content .= '<tr><td>' . $value['firstroster'] . '</td></tr>';
					}

					if (array_key_exists('firstsubst', $value))
					{
						$content .= '<tr><td>' . $value['firstsubst'] . '</td></tr>';
					}

					if (array_key_exists('firstevents', $value))
					{
						$content .= '<tr><td>' . $value['firstevents'] . '</td></tr>';
					}
				}
			}
		}

		$content .= '</table>';

		return $content;
	}

	/**
	 * sportsmanagementModelallprojectrounds::getMatchPlayers()
	 *
	 * @return
	 */
	function getMatchPlayers()
	{
		$app = Factory::getApplication();

		// Get a db connection.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$projectteamplayer = array();

		$query->select('pt.id');
		$query->select('stp1.person_id,stp1.jerseynumber,stp1.picture');
		$query->select('p.firstname,p.nickname,p.lastname');
		$query->select('ppos.position_id,ppos.id AS pposid,p.picture AS ppic');
		$query->select('pt.team_id,pt.id as ptid');
		$query->select('mp.teamplayer_id,mp.out,mp.in_out_time');
		$query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
		$query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');

		$query->from('#__sportsmanagement_match_player AS mp ');
		$query->join('INNER', ' #__sportsmanagement_season_team_person_id as stp1 ON stp1.id = mp.teamplayer_id');
		$query->join('INNER', ' #__sportsmanagement_season_team_id as st1 ON st1.team_id = stp1.team_id ');
		$query->join('INNER', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
		$query->join('INNER', ' #__sportsmanagement_person AS p ON stp1.person_id = p.id AND p.published = 1 ');

		$query->join('LEFT', ' #__sportsmanagement_project_position AS ppos ON ppos.id = mp.project_position_id');
		$query->join('LEFT', ' #__sportsmanagement_position AS pos ON ppos.position_id = pos.id');

		$query->join('INNER', ' #__sportsmanagement_team AS t ON t.id = st1.team_id');

		$query->where('mp.match_id = ' . (int) $this->matchid);
		$query->where('mp.came_in = 0');
		$query->where('pt.id = ' . $this->projectteam_id);
		$query->where('p.published = 1');

		$query->order('mp.ordering, stp1.jerseynumber, p.lastname');

		$db->setQuery($query);
		$matchplayers = $db->loadObjectList();

		if (!$matchplayers)
		{
			Log::add(Text::_('Keine Spieler vorhanden'), Log::WARNING, 'jsmerror');
		}

		foreach ($matchplayers as $row)
		{
			$query->clear();
			$query->select('in_out_time');
			$query->from('#__sportsmanagement_match_player');
			$query->where('match_id = ' . (int) $this->matchid);
			$query->where('in_for = ' . (int) $row->teamplayer_id);
			$db->setQuery($query);
			$row->in_out_time = $db->loadResult();

			if ($row->in_out_time)
			{
				$projectteamplayer[] = $row->firstname . ' ' . $row->lastname . ' (' . $row->in_out_time . ')';
			}
			else
			{
				$projectteamplayer[] = $row->firstname . ' ' . $row->lastname;
			}
		}

		return $projectteamplayer;

	}

	/**
	 * sportsmanagementModelallprojectrounds::getSubstitutes()
	 *
	 * @return
	 */
	function getSubstitutes()
	{
		$app = Factory::getApplication();

		// Get a db connection.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$projectteamplayer = array();

		$query->select('mp.in_out_time,mp.teamplayer_id,mp.in_for');
		$query->select('pt.team_id,pt.id AS ptid');
		$query->select('p.firstname,p.nickname,p.lastname');

		// $query->select('tp.person_id,tp.jerseynumber');
		$query->select('stp1.person_id,stp1.jerseynumber');

		// $query->select('tp2.person_id AS out_person_id');
		$query->select('stp2.person_id AS out_person_id');
		$query->select('p2.id AS out_ptid,p2.firstname AS out_firstname,p2.nickname AS out_nickname,p2.lastname AS out_lastname');
		$query->select('pos.name AS in_position');
		$query->select('pos2.name AS out_position');
		$query->select('ppos.id AS pposid1');
		$query->select('ppos2.id AS pposid2');
		$query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
		$query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');

		$query->from('#__sportsmanagement_match_player AS mp ');
		$query->join('LEFT', ' #__sportsmanagement_season_team_person_id as stp1 ON stp1.id = mp.teamplayer_id');
		$query->join('LEFT', ' #__sportsmanagement_season_team_id as st1 ON st1.team_id = stp1.team_id ');
		$query->join('LEFT', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
		$query->join('LEFT', ' #__sportsmanagement_person AS p ON stp1.person_id = p.id AND p.published = 1 ');

		$query->join('LEFT', ' #__sportsmanagement_season_team_person_id as stp2 ON stp2.id = mp.in_for');
		$query->join('LEFT', ' #__sportsmanagement_season_team_id as st2 ON st2.team_id = stp2.team_id ');
		$query->join('LEFT', ' #__sportsmanagement_person AS p2 ON stp2.person_id = p2.id AND p2.published = 1 ');

		$query->join('LEFT', ' #__sportsmanagement_project_position AS ppos ON ppos.id = mp.project_position_id');
		$query->join('LEFT', ' #__sportsmanagement_position AS pos ON ppos.position_id = pos.id');
		$query->join('LEFT', ' #__sportsmanagement_match_player AS mp2 ON mp.match_id = mp2.match_id and mp.in_for = mp2.teamplayer_id');
		$query->join('LEFT', ' #__sportsmanagement_project_position AS ppos2 ON ppos2.id = mp2.project_position_id');
		$query->join('LEFT', ' #__sportsmanagement_position AS pos2 ON ppos2.position_id = pos2.id');

		$query->join('INNER', ' #__sportsmanagement_team AS t ON t.id = st1.team_id');

		$query->where('mp.match_id = ' . (int) $this->matchid);
		$query->where('pt.id = ' . $this->projectteam_id);
		$query->where('mp.came_in > 0');

		$query->group('mp.in_out_time+mp.teamplayer_id+pt.team_id');
		$query->order('(mp.in_out_time+0)');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result)
		{
			Log::add(Text::_('Keine Auswechselungen vorhanden'), Log::WARNING, 'jsmerror');
		}
		else
		{
			foreach ($result as $row)
			{
				$projectteamplayer[] = $row->firstname . ' ' . $row->lastname . ' (' . $row->in_out_time . ')';
			}
		}

		return $projectteamplayer;

	}

	/**
	 * sportsmanagementModelallprojectrounds::getPlayersEvents()
	 *
	 * @return
	 */
	function getPlayersEvents()
	{
		$app = Factory::getApplication();

		// Get a db connection.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$playersevents = array();

		$query->select('ev.*');
		$query->select('p.firstname,p.nickname,p.lastname');
		$query->select('et.name as etname,et.icon as eticon');
		$query->from('#__sportsmanagement_match_event as ev  ');
		$query->join('INNER', ' #__sportsmanagement_eventtype AS et ON et.id = ev.event_type_id ');
		$query->join('INNER', ' #__sportsmanagement_season_team_person_id as stp1 ON stp1.id = ev.teamplayer_id');
		$query->join('INNER', ' #__sportsmanagement_season_team_id as st1 ON st1.team_id = stp1.team_id ');
		$query->join('INNER', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
		$query->join('INNER', ' #__sportsmanagement_person AS p ON stp1.person_id = p.id AND p.published = 1 ');

		$query->where('ev.match_id = ' . (int) $this->matchid);
		$query->where('ev.projectteam_id = ' . $this->projectteam_id);

		$db->setQuery($query);
		$res = $db->loadObjectList();

		if (!$res)
		{
			Log::add(Text::_('Keine Ereignisse vorhanden'), Log::WARNING, 'jsmerror');
		}

		foreach ($res as $row)
		{
			$playersevents[] = HTMLHelper::_('image', $row->eticon, Text::_($row->eticon), null) . Text::_($row->etname) . ' ' . $row->notice . ' ' . $row->firstname . ' ' . $row->lastname . ' (' . $row->event_time . ')';
		}

		// 			$this->_playersevents = $events;

		return $playersevents;

	}

}
