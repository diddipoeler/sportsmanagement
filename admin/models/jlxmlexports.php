<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       jlxmlexports.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModelJLXMLExports
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelJLXMLExports extends BaseDatabaseModel
{
	/**
	 * @var integer
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_project_id = 0;

	/**
	 * @var integer
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_update = 0;

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_project = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_projectteam = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5253
	 */
	private $_projectreferee = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5253
	 */
	private $_projectposition = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_team = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_teamplayer = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_teamstaff = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_teamtrainingdata = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_match = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_club = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_playground = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_matchplayer = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_matchstaff = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_matchreferee = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_person = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_matchevent = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_eventtype = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_position = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5262
	 */
	private $_parentposition = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5283
	 */
	private $_matchstaffstatistic = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5283
	 */
	private $_matchstatistic = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5283
	 */
	private $_positionstatistic = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5283
	 */
	private $_statistic = array();


	/**
	 * sportsmanagementModelJLXMLExports::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{

		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
		$this->app    = Factory::getApplication();
		$this->user   = Factory::getUser();
		$this->jinput = $this->app->input;
		$this->option = $this->jinput->getCmd('option');
		$this->jsmdb  = $this->getDbo();
		$this->query  = $this->jsmdb->getQuery(true);
	}

	/**
	 * exportData
	 *
	 * Export the active project data to xml
	 *
	 * @access public
	 * @return null
	 * @since  1.5.0a
	 *
	 */
	public function exportData()
	{
		$this->_project_id = $this->jinput->getVar('pid');
		$this->_update     = $this->jinput->getVar('update');

		if (empty($this->_project_id) || $this->_project_id == 0)
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EXPORT_MODEL_SELECT_PROJECT'));
		}
		else
		{
			//  das ist neu
			$filename = $this->_getIdFromData('name', $this->_project);

			if (empty($filename))
			{
				if (empty($this->_project_id) || $this->_project_id == 0)
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EXPORT_MODEL_SELECT_PROJECT'));
				}
				else
				{
					/**
					 *
					 * get the project datas
					 */
					$this->_getProjectData();
					$filename    = $this->_getIdFromData('name', $this->_project);
					$filename[0] = $filename[0] . "-" . $table;
				}
			}

			$l98filename = OutputFilter::stringURLSafe($filename[0]) . "-" . date("ymd-His");
			$file        = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $this->user->username . DIRECTORY_SEPARATOR . OutputFilter::stringURLSafe($filename[0]) . '.jlg';
			$userpath    = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $this->user->username;

			if (Folder::exists($userpath))
			{
			}
			else
			{
				Folder::create($userpath);
			}

			$output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";

			// Open the project
			$output .= "<project>\n";

			if ($this->_update)
			{
				// Get the matches data
				$output .= $this->_addToXml($this->_getMatchData());
			}
			else
			{
				// Get the version of SportsManagement
				$this->query->clear();
				$output .= $this->_addToXml($this->_getSportsManagementVersion());

				// Get the project datas
				$this->query->clear();
				$output .= $this->_addToXml($this->_getProjectData());

				// Get sportstype data of project
				$this->query->clear();
				$output .= $this->_addToXml($this->_getSportsTypeData());

				// Get league data of project
				$this->query->clear();
				$output .= $this->_addToXml($this->_getLeagueData());

				// Get season data of project
				$this->query->clear();
				$output .= $this->_addToXml($this->_getSeasonData());

				// Get the template data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getTemplateData());

				// Get divisions data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getDivisionData());

				// Get the projectteams data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getProjectTeamData());

				// Get referee data of project
				$this->query->clear();
				$output .= $this->_addToXml($this->_getProjectRefereeData());

				// Get position data of project
				$this->query->clear();
				$output .= $this->_addToXml($this->_getProjectPositionData());

				// Get the teams data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getTeamData());

				// Get the clubs data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getClubData());

				// Get the rounds data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getRoundData());

				// Get the matches data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getMatchData());

				// Get the playground data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getPlaygroundData());

				// Get the team player data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getTeamPlayerData());

				// Get the team staff data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getTeamStaffData());

				// Get the team training data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getTeamTrainingData());

				/*
				// get the match player data
				$output .= $this->_addToXml($this->_getMatchPlayerData());
				*/

				/*
				// get the match staff data
				$output .= $this->_addToXml($this->_getMatchStaffData());
				*/

				/*
				// get the match referee data
				$output .= $this->_addToXml($this->_getMatchRefereeData());
				*/

				// Get the positions data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getPositionData());

				// Get the positions parent data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getParentPositionData());

				// Get ALL persons data for Export
				$this->query->clear();
				$output .= $this->_addToXml($this->_getPersonData());

				// Get the match events data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getMatchEvent());

				// Get the event types data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getEventType());

				// Get the position eventtypes data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getPositionEventType());

				// Get the match_statistic data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getMatchStatistic());

				// Get the match_staff_statistic data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getMatchStaffStatistic());

				// Get the position_statistic data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getPositionStatistic());

				// Get the statistic data
				$this->query->clear();
				$output .= $this->_addToXml($this->_getStatistic());
			}

			// Close the project
			$output .= '</project>';

			// Mal als test
			$xmlfile = $xmlfile . $output;

			// Download the generated xml
			$this->downloadXml($output, "");

			// Close the application
			$app = Factory::getApplication();
			$app->close();
		}
	}

	/**
	 * _getIdFromData
	 *
	 * Get only the ids array from the full array
	 *
	 * @param   string  $id     field name what we find in the array
	 * @param   array   $array  the array where we find the field
	 *
	 * @access private
	 * @return void
	 * @since  1.5.0a
	 *
	 */
	private function _getIdFromData($id, $array)
	{
		if (is_array($array) && count($array) > 0)
		{
			$ids = array();

			foreach ($array as $key => $value)
			{
				if (array_key_exists($id, $value) && $value[$id] != '')
				{
					$ids[] = $value[$id];
				}
			}

			return $ids;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getProjectData()
	 *
	 * @return
	 */
	private function _getProjectData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_project');
		$this->query->where('id = ' . $this->_project_id);

		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'SportsManagement';
			$this->_project      = $result;

			return $result;
		}

		return false;
	}

	/**
	 * Add data to the xml
	 *
	 * @param   array  $data  data what we want to add in the xml
	 *
	 * @access private
	 * @return void
	 * @since  1.5.0a
	 *
	 */
	private function _addToXml($data)
	{
		if (is_array($data) && count($data) > 0)
		{
			$object = $data[0]['object'];
			$output = '';

			foreach ($data as $name => $value)
			{
				$output .= "<record object=\"" . $this->stripInvalidXml($object) . "\">\n";

				foreach ($value as $key => $data)
				{
					if (!is_null($data) && !(substr($key, 0, 1) == "_") && $key != "object")
					{
						$output .= "  <$key><![CDATA[" . $this->stripInvalidXml(trim($data)) . "]]></$key>\n";
					}
				}

				$output .= "</record>\n";
			}

			return $output;
		}

		return false;
	}

	/**
	 * Removes invalid XML
	 *
	 * @access public
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	private function stripInvalidXml($value)
	{
		$ret = '';
		$current;

		if (is_null($value))
		{
			return $ret;
		}

		$length = strlen($value);

		for ($i = 0; $i < $length; $i++)
		{
			$current = ord($value[$i]});

			if (($current == 0x9)
				|| ($current == 0xA)
				|| ($current == 0xD)
				|| (($current >= 0x20) && ($current <= 0xD7FF))
				|| (($current >= 0xE000) && ($current <= 0xFFFD))
				|| (($current >= 0x10000) && ($current <= 0x10FFFF))
			)
			{
				$ret .= chr($current);
			}
			else
			{
				$ret .= ' ';
			}
		}

		return $ret;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getMatchData()
	 *
	 * @return
	 */
	private function _getMatchData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_match as m');
		$this->query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
		$this->query->where('r.project_id = ' . $this->_project_id);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'Match';
			$this->_match        = $result;

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getSportsManagementVersion()
	 *
	 * @return
	 */
	private function _getSportsManagementVersion()
	{
		$exportRoutine = '2010-09-23 15:00:00';
		$this->query->select('manifest_cache');
		$this->query->from('#__extensions');
		$this->query->where('name LIKE ' . $this->jsmdb->Quote('' . 'com_sportsmanagement' . ''));
		$this->jsmdb->setQuery($this->query);
		$manifest_cache = json_decode($this->jsmdb->loadResult(), true);

		if ($manifest_cache['version'])
		{
			$result[0]['version']       = $manifest_cache['version'];
			$result[0]['exportversion'] = $manifest_cache['version'];
			$result[0]['exportRoutine'] = $exportRoutine;
			$result[0]['exportDate']    = date('Y-m-d');
			$result[0]['exportTime']    = date('H:i:s');

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$result[0]['exportSystem'] = Factory::getConfig()->get('sitename');
			}
			else
			{
				$result[0]['exportSystem'] = Factory::getConfig()->getValue('sitename');
			}

			$result[0]['object'] = 'SportsManagementVersion';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getSportsTypeData()
	 *
	 * @return
	 */
	private function _getSportsTypeData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_sports_type');
		$this->query->where('id = ' . $this->_project[0]['sports_type_id']);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'SportsType';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getLeagueData()
	 *
	 * @return
	 */
	private function _getLeagueData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_league');
		$this->query->where('id = ' . $this->_project[0]['league_id']);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'League';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getSeasonData()
	 *
	 * @return
	 */
	private function _getSeasonData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_season');
		$this->query->where('id = ' . $this->_project[0]['season_id']);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'Season';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getTemplateData()
	 *
	 * @return
	 */
	private function _getTemplateData()
	{
		// This is the master template
		if ($this->_project[0]['master_template'] == 0)
		{
			$master_template_id = $this->_project_id;
		}
		else
		{
			$master_template_id = $this->_project[0]['master_template'];
		}

		$this->query->select('*');
		$this->query->from('#__sportsmanagement_template_config');
		$this->query->where('project_id = ' . $master_template_id);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'Template';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getDivisionData()
	 *
	 * @return
	 */
	private function _getDivisionData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_division');
		$this->query->where('project_id = ' . $this->_project_id);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'LeagueDivision';

			return $result;
		}

		return false;
	}


	/**
	 * sportsmanagementModelJLXMLExports::_getProjectTeamData()
	 *
	 * @return
	 */
	private function _getProjectTeamData()
	{
		$this->query->select(
			'pt.id,
        pt.project_id,
        st.team_id,
        st.id as season_team_id,
        pt.start_points,
        pt.points_finally,
        pt.neg_points_finally,
        pt.matches_finally,
        pt.won_finally,
  pt.draws_finally,
  pt.lost_finally,
  pt.homegoals_finally,
  pt.guestgoals_finally,
  pt.diffgoals_finally,
  pt.is_in_score,
  pt.use_finally,
  pt.admin,
  pt.info,
  pt.notes,
  pt.reason,
  pt.checked_out,
  pt.checked_out_time'
		);
		$this->query->from('#__sportsmanagement_project_team as pt');
		$this->query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.id = pt.team_id');
		$this->query->where('pt.project_id = ' . $this->_project_id);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'ProjectTeam';
			$this->_projectteam  =& $result;

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getProjectRefereeData()
	 *
	 * @return
	 */
	private function _getProjectRefereeData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_project_referee');
		$this->query->where('project_id = ' . $this->_project_id);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result                = $this->jsmdb->loadAssocList();
			$result[0]['object']   = 'ProjectReferee';
			$this->_projectreferee =& $result;

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getProjectPositionData()
	 *
	 * @return
	 */
	private function _getProjectPositionData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_project_position');
		$this->query->where('project_id = ' . $this->_project_id);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result                 = $this->jsmdb->loadAssocList();
			$result[0]['object']    = 'ProjectPosition';
			$this->_projectposition =& $result;

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getTeamData()
	 *
	 * @return
	 */
	private function _getTeamData()
	{
		$team_ids = $this->_getIdFromData('season_team_id', $this->_projectteam);

		if (is_array($team_ids) && count($team_ids) > 0)
		{
			$ids = implode(",", array_unique($team_ids));
			$this->query->select('t.*');
			$this->query->from('#__sportsmanagement_team as t');
			$this->query->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
			$this->query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
			$this->query->where('st.id IN (' . $ids . ')');
			$this->query->where('pt.project_id = ' . $this->_project_id);
			$this->query->order('name');
			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'JL_Team';
				$this->_team         =& $result;

				return $result;
			}

			return false;
		}

		return false;
	}


	/**
	 * sportsmanagementModelJLXMLExports::_getClubData()
	 *
	 * @return
	 */
	private function _getClubData()
	{
		$cIDs         = array();
		$teamClub_ids = $this->_getIdFromData('club_id', $this->_team);

		if (is_array($teamClub_ids))
		{
			$cIDs = array_merge($cIDs, $teamClub_ids);
		}

		if (is_array($cIDs) && count($cIDs) > 0)
		{
			$ids = implode(",", array_unique($cIDs));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_club');
			$this->query->where('id IN (' . $ids . ')');
			$this->query->order('name');
			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'Club';
				$this->_club         = $result;

				return $result;
			}

			return false;
		}

		return false;
	}


	/**
	 * sportsmanagementModelJLXMLExports::_getRoundData()
	 *
	 * @return
	 */
	private function _getRoundData()
	{
		$this->query->select('*');
		$this->query->from('#__sportsmanagement_round');
		$this->query->where('project_id = ' . $this->_project_id);
		$this->jsmdb->setQuery($this->query);
		$this->jsmdb->execute();

		if ($this->jsmdb->getNumRows() > 0)
		{
			$result              = $this->jsmdb->loadAssocList();
			$result[0]['object'] = 'Round';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getPlaygroundData()
	 *
	 * @return
	 */
	private function _getPlaygroundData()
	{
		$pgIDs               = array();
		$clubsPlayground_ids = $this->_getIdFromData('standard_playground', $this->_club);

		if (is_array($clubsPlayground_ids))
		{
			$pgIDs = array_merge($pgIDs, $clubsPlayground_ids);
		}

		$projectTeamsPlayground_ids = $this->_getIdFromData('standard_playground', $this->_projectteam);

		if (is_array($projectTeamsPlayground_ids))
		{
			$pgIDs = array_merge($pgIDs, $projectTeamsPlayground_ids);
		}

		$matchPlayground_ids = $this->_getIdFromData('playground_id', $this->_match);

		if (is_array($matchPlayground_ids))
		{
			$pgIDs = array_merge($pgIDs, $matchPlayground_ids);
		}

		if (is_array($pgIDs) && count($pgIDs) > 0)
		{
			$ids = implode(",", array_unique($pgIDs));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_playground');
			$this->query->where('id IN (' . $ids . ')');
			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'Playground';
				$this->_playground   = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getTeamPlayerData()
	 *
	 * @return
	 */
	private function _getTeamPlayerData()
	{
		$teamplayer_ids = $this->_getIdFromData('id', $this->_projectteam);

		if (is_array($teamplayer_ids) && count($teamplayer_ids) > 0)
		{
			$ids = implode(",", array_unique($teamplayer_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_team_player');
			$this->query->where('projectteam_id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'TeamPlayer';
				$this->_teamplayer   = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getTeamStaffData()
	 *
	 * @return
	 */
	private function _getTeamStaffData()
	{
		$teamstaff_ids = $this->_getIdFromData('id', $this->_projectteam);

		if (is_array($teamstaff_ids) && count($teamstaff_ids) > 0)
		{
			$ids = implode(",", array_unique($teamstaff_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_team_staff');
			$this->query->where('project_team_id IN (' . $ids . ')');

			try
			{
				$this->jsmdb->setQuery($this->query);
				$this->jsmdb->execute();

				if ($this->jsmdb->getNumRows() > 0)
				{
					$result              = $this->jsmdb->loadAssocList();
					$result[0]['object'] = 'TeamStaff';
					$this->_teamstaff    = $result;

					return $result;
				}
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
				$this->app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getTeamTrainingData()
	 *
	 * @return
	 */
	private function _getTeamTrainingData()
	{
		$teamtraining_ids = $this->_getIdFromData('id', $this->_projectteam);

		if (is_array($teamtraining_ids) && count($teamtraining_ids) > 0)
		{
			$ids = implode(',', array_unique($teamtraining_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_team_trainingdata');
			$this->query->where('project_team_id IN (' . $ids . ')');

			try
			{
				$this->jsmdb->setQuery($this->query);
				$this->jsmdb->execute();

				if ($this->jsmdb->getNumRows() > 0)
				{
					$result                  = $this->jsmdb->loadAssocList();
					$result[0]['object']     = 'TeamTraining';
					$this->_teamtrainingdata = $result;

					return $result;
				}
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
				$this->app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getPositionData()
	 *
	 * @return
	 */
	private function _getPositionData()
	{
		$position_ids = $this->_getIdFromData('position_id', $this->_projectposition);

		if (is_array($position_ids) && count($position_ids) > 0)
		{
			$ids = implode(",", array_unique($position_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_position');
			$this->query->where('id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'Position';
				$this->_position     = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getParentPositionData()
	 *
	 * @return
	 */
	private function _getParentPositionData()
	{
		$position_ids = $this->_getIdFromData('parent_id', $this->_position);

		if (is_array($position_ids) && count($position_ids) > 0)
		{
			$ids = implode(",", array_unique($position_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_position');
			$this->query->where('id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result                = $this->jsmdb->loadAssocList();
				$result[0]['object']   = 'ParentPosition';
				$this->_parentposition = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getPersonData()
	 *
	 * @return
	 */
	private function _getPersonData()
	{
		$pgIDs = array();

		$teamPlayer_ids = $this->_getIdFromData('person_id', $this->_teamplayer);

		if (is_array($teamPlayer_ids))
		{
			$pgIDs = array_merge($pgIDs, $teamPlayer_ids);
		}

		$teamStaff_ids = $this->_getIdFromData('person_id', $this->_teamstaff);

		if (is_array($teamStaff_ids))
		{
			$pgIDs = array_merge($pgIDs, $teamStaff_ids);
		}

		$projectReferee_ids = $this->_getIdFromData('person_id', $this->_projectreferee);

		if (is_array($projectReferee_ids))
		{
			$pgIDs = array_merge($pgIDs, $projectReferee_ids);
		}

		if (is_array($pgIDs) && count($pgIDs) > 0)
		{
			$ids = implode(",", array_unique($pgIDs));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_person');
			$this->query->where('id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'Person';
				$this->_person       = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getMatchEvent()
	 *
	 * @return
	 */
	private function _getMatchEvent()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_match_event');
			$this->query->where('match_id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'MatchEvent';
				$this->_matchevent   = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getEventType()
	 *
	 * @return
	 */
	private function _getEventType()
	{
		$eventtype_ids = $this->_getIdFromData('event_type_id', $this->_matchevent);

		if (is_array($eventtype_ids) && count($eventtype_ids) > 0)
		{
			$ids = implode(",", array_unique($eventtype_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_eventtype');
			$this->query->where('id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'EventType';
				$this->_eventtype    = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getPositionEventType()
	 *
	 * @return
	 */
	private function _getPositionEventType()
	{
		$eventtype_ids = $this->_getIdFromData('id', $this->_eventtype);
		$position_ids  = $this->_getIdFromData('id', $this->_position);

		if (is_array($eventtype_ids) && count($eventtype_ids) > 0)
		{
			$event_ids    = implode(",", array_unique($eventtype_ids));
			$position_ids = implode(",", array_unique($position_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_position_eventtype');
			$this->query->where('eventtype_id IN (' . $event_ids . ')');
			$this->query->where('position_id IN (' . $position_ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'PositionEventType';

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getMatchStatistic()
	 *
	 * @return
	 */
	private function _getMatchStatistic()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_match_statistic');
			$this->query->where('match_id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result                = $this->jsmdb->loadAssocList();
				$result[0]['object']   = 'MatchStatistic';
				$this->_matchstatistic = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getMatchStaffStatistic()
	 *
	 * @return
	 */
	private function _getMatchStaffStatistic()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_match_staff_statistic');
			$this->query->where('match_id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result                     = $this->jsmdb->loadAssocList();
				$result[0]['object']        = 'MatchStaffStatistic';
				$this->_matchstaffstatistic = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getPositionStatistic()
	 *
	 * @return
	 */
	private function _getPositionStatistic()
	{
		$position_ids = $this->_getIdFromData('id', $this->_position);

		if (is_array($position_ids) && count($position_ids) > 0)
		{
			$ids = implode(",", array_unique($position_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_position_statistic');
			$this->query->where('position_id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result                   = $this->jsmdb->loadAssocList();
				$result[0]['object']      = 'PositionStatistic';
				$this->_positionstatistic = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getStatistic()
	 *
	 * @return
	 */
	private function _getStatistic()
	{
		$sIDs = array();

		$matchstatistic_ids = $this->_getIdFromData('statistic_id', $this->_matchstatistic);    // Get all ids of match statistics assigned to the actual project

		if (is_array($matchstatistic_ids))
		{
			$sIDs = array_merge($sIDs, $matchstatistic_ids);
		}

		$matchstaffstatistic_ids = $this->_getIdFromData('statistic_id', $this->_matchstaffstatistic);    // Get all ids of match staff statistic assigned to the actual project

		if (is_array($matchstaffstatistic_ids))
		{
			$sIDs = array_merge($sIDs, $matchstaffstatistic_ids);
		}

		$positionstatistic_ids = $this->_getIdFromData('statistic_id', $this->_positionstatistic);    // Get all ids of position statistic assigned to the actual project

		if (is_array($positionstatistic_ids))
		{
			$sIDs = array_merge($sIDs, $positionstatistic_ids);
		}

		if (is_array($sIDs) && count($sIDs) > 0)
		{
			$ids = implode(",", array_unique($sIDs));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_statistic');
			$this->query->where('id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'Statistic';
				$this->_person       = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * downloadXml
	 *
	 * Pop-up the browser's download window with the generated xml file
	 *
	 * @param   string  $data  generated xml data
	 *
	 * @return null
	 * @since 1.5.0a
	 *
	 */
	function downloadXml($data, $table)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput      = $app->input;
		$option      = $jinput->getCmd('option');
		$db          = $this->getDbo();
		$this->query = $this->jsmdb->getQuery(true);

		jimport('joomla.filter.output');
		$filename = $this->_getIdFromData('name', $this->_project);

		if (empty($filename))
		{
			$this->_project_id = $app->getUserState($option . 'project');

			if (empty($this->_project_id) || $this->_project_id == 0)
			{
				Log::add(Text::_('COM_SPORTSMANAGEMENT_XML_EXPORT_MODEL_SELECT_PROJECT'));
			}
			else
			{
				// Get the project datas
				$this->_getProjectData();
				$filename    = $this->_getIdFromData('name', $this->_project);
				$filename[0] = $filename[0] . "-" . $table;
			}
		}

		header('Content-type: "text/xml"; charset="utf-8"');
		header("Content-Disposition: attachment; filename=\"" . OutputFilter::stringURLSafe($filename[0]) . "-" . date("ymd-His") . ".jlg\"");
		header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		ob_clean();
		echo $data;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getMatchPlayerData()
	 *
	 * @return
	 */
	private function _getMatchPlayerData()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_match_player');
			$this->query->where('match_id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'MatchPlayer';
				$this->_matchplayer  = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getMatchStaffData()
	 *
	 * @return
	 */
	private function _getMatchStaffData()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_match_staff');
			$this->query->where('match_id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'MatchStaff';
				$this->_matchstaff   = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

	/**
	 * sportsmanagementModelJLXMLExports::_getMatchRefereeData()
	 *
	 * @return
	 */
	private function _getMatchRefereeData()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));
			$this->query->select('*');
			$this->query->from('#__sportsmanagement_match_referee');
			$this->query->where('match_id IN (' . $ids . ')');

			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();

			if ($this->jsmdb->getNumRows() > 0)
			{
				$result              = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'MatchReferee';
				$this->_matchreferee = $result;

				return $result;
			}

			return false;
		}

		return false;
	}

}
