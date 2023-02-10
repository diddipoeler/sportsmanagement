<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       jlextindividualsportes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModeljlextindividualsportes
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeljlextindividualsportes extends JSMModelList
{
	var $_identifier = "jlextindividualsportes";

	/**
	 * sportsmanagementModeljlextindividualsportes::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'mc.id'
		);
		parent::__construct($config);
	}

	/**
	 * sportsmanagementModeljlextindividualsportes::checkGames()
	 *
	 * @param   mixed  $project
	 * @param   mixed  $match_id
	 * @param   mixed  $rid
	 * @param   mixed  $projectteam1_id
	 * @param   mixed  $projectteam2_id
	 *
	 * @return void
	 */
	function checkGames($project, $match_id, $rid, $projectteam1_id, $projectteam2_id)
	{
	   
       //echo __LINE__.' projekt <pre>'.print_r($project,true).'</pre>';
       
       switch ($project->sports_type_name)
		{
		case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
        $this->jsmquery->clear();
		$this->jsmquery->select('COUNT(mc.id)');
		$this->jsmquery->from('#__sportsmanagement_match_single AS mc');
		$this->jsmquery->where('mc.match_id = ' . $match_id);
		$this->jsmquery->where('mc.match_type = "SINGLE" ');
		$this->jsmdb->setQuery($this->jsmquery);
		$singleresult = $this->jsmdb->loadResult();
        if ($singleresult < $project->single_matches)
		{
			$insertmatch = $project->single_matches - $singleresult;

			for ($i = 0; $i < $insertmatch; $i++)
			{
				$temp                  = new stdClass;
				$temp->round_id        = $rid;
				$temp->projectteam1_id = $projectteam1_id;
				$temp->projectteam2_id = $projectteam2_id;
				$temp->match_id        = $match_id;
				$temp->match_type      = 'SINGLE';
				$temp->published       = 1;
                $temp->teamplayer1_id = 0;
                $temp->teamplayer2_id = 0;
                $temp->summary      = '';
                $temp->preview      = '';

				$result = $this->jsmdb->insertObject('#__sportsmanagement_match_single', $temp);
			}
		}
        
        break;
        case 'COM_SPORTSMANAGEMENT_ST_TENNIS':
            
		$this->jsmquery->clear();
		$this->jsmquery->select('COUNT(mc.id)');
		$this->jsmquery->from('#__sportsmanagement_match_single AS mc');
		$this->jsmquery->where('mc.match_id = ' . $match_id);
		$this->jsmquery->where('mc.match_type = "SINGLE" ');
		$this->jsmdb->setQuery($this->jsmquery);
		$singleresult = $this->jsmdb->loadResult();

		if ($singleresult < $project->tennis_single_matches)
		{
			$insertmatch = $project->tennis_single_matches - $singleresult;

			for ($i = 0; $i < $insertmatch; $i++)
			{
				$temp                  = new stdClass;
				$temp->round_id        = $rid;
				$temp->projectteam1_id = $projectteam1_id;
				$temp->projectteam2_id = $projectteam2_id;
				$temp->match_id        = $match_id;
				$temp->match_type      = 'SINGLE';
				$temp->published       = 1;
                $temp->teamplayer1_id = 0;
                $temp->teamplayer2_id = 0;
                $temp->summary      = '';
                $temp->preview      = '';

				$result = $this->jsmdb->insertObject('#__sportsmanagement_match_single', $temp);
			}
		}

		$this->jsmquery->clear();
		$this->jsmquery->select('COUNT(mc.id)');
		$this->jsmquery->from('#__sportsmanagement_match_single AS mc');
		$this->jsmquery->where('mc.match_id = ' . $match_id);
		$this->jsmquery->where('mc.match_type = "DOUBLE" ');
		$this->jsmdb->setQuery($this->jsmquery);
		$doubleresult = $this->jsmdb->loadResult();

		if ($doubleresult < $project->tennis_double_matches)
		{
			$insertmatch = $project->tennis_double_matches - $doubleresult;

			for ($i = 0; $i < $insertmatch; $i++)
			{
				$temp                  = new stdClass;
				$temp->round_id        = $rid;
				$temp->projectteam1_id = $projectteam1_id;
				$temp->projectteam2_id = $projectteam2_id;
				$temp->match_id        = $match_id;
				$temp->match_type      = 'DOUBLE';
				$temp->published       = 1;
                $temp->teamplayer1_id = 0;
                $temp->teamplayer2_id = 0;
                $temp->summary      = '';
                $temp->preview      = '';

				$result = $this->jsmdb->insertObject('#__sportsmanagement_match_single', $temp);
			}
		}
        break;
		}
	}

	/**
	 * Method to return the project teams array (id, name)
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function getProjectTeams($project_id)
	{
        $this->jsmquery->clear();
		$this->jsmquery->select('pt.id AS value');
		$this->jsmquery->select('t.name AS text,t.short_name AS short_name,t.notes');
		$this->jsmquery->from('#__sportsmanagement_team AS t');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$this->jsmquery->where('pt.project_id = ' . $project_id);
		$this->jsmquery->order('text ASC');

		$this->jsmdb->setQuery($this->jsmquery);

		if (!$result = $this->jsmdb->loadObjectList())
		{
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * @param   int iDivisionId
	 * return project teams as options
	 *
	 * @return unknown_type
	 */
	function getProjectTeamsOptions($iDivisionId = 0)
	{
		$option = Factory::getApplication()->input->getCmd('option');

		$app        =& Factory::getApplication();
		$project_id = $app->getUserState($option . 'project');

		$query = ' SELECT	pt.id AS value, '
			. ' CASE WHEN CHAR_LENGTH(t.name) < 25 THEN t.name ELSE t.middle_name END AS text '
			. ' FROM #__sportsmanagement_team AS t '
			. ' LEFT JOIN #__sportsmanagement_project_team AS pt ON pt.team_id = t.id '
			. ' WHERE pt.project_id = ' . $project_id;

		if ($iDivisionId > 0)
		{
			$query .= ' AND pt.division_id = ' . $iDivisionId;
		}

		$query .= ' ORDER BY text ASC ';

		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();

		if ($result === false)
		{
			Log::add($this->_db->getErrorMsg());

			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * sportsmanagementModeljlextindividualsportes::getMatchesByRound()
	 *
	 * @param   mixed  $roundId
	 *
	 * @return
	 */
	function getMatchesByRound($roundId)
	{
		$query = 'SELECT * FROM #__sportsmanagement_match_single WHERE round_id=' . $roundId;
		$this->_db->setQuery($query);

		// Echo($this->_db->getQuery());
		$result = $this->_db->loadObjectList();

		if ($result === false)
		{
			Log::add($this->_db->getErrorMsg());

			return false;
		}

		return $result;
	}

	/**
	 * sportsmanagementModeljlextindividualsportes::getPlayer()
	 *
	 * @param   mixed  $teamid
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	function getPlayer($teamid, $project_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// Create a new query object.
		$db        = sportsmanagementHelper::getDBConnection();
		$query     = $db->getQuery(true);
		$season_id = $app->getUserState("$option.season_id", '0');

		// Select some fields
		$query->select('tp.id AS value');
		$query->select('concat(pl.firstname," - ",pl.nickname," - ",pl.lastname) as text');
		$query->from('#__sportsmanagement_person AS pl');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = pl.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->where('pt.id =' . $db->Quote($teamid));
		$query->where('pt.project_id =' . $project_id);

		$query->where('tp.season_id =' . $season_id);
		$query->where('st.season_id =' . $season_id);

		$query->where('pl.published = 1');
		$query->order('pl.lastname ASC');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;

	}

	/**
	 * sportsmanagementModeljlextindividualsportes::getSportType()
	 *
	 * @param   mixed  $id
	 *
	 * @return
	 */
	function getSportType($id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    =& Factory::getApplication();
		$query  = 'SELECT name
					FROM #__sportsmanagement_sports_type
					WHERE id=' . $this->_db->Quote($id);
		$this->_db->setQuery($query);
		$sporttype = $this->_db->loadResult();
		$app->setUserState($option . 'sporttype', $sporttype);
		$app->enqueueMessage(Text::_('Sporttype: ' . $sporttype), '');

		switch (strtolower($sporttype))
		{
			case 'ringen':
				$this->_getSinglefile();
				break;
		}

		return $sporttype;

	}

	/**
	 * sportsmanagementModeljlextindividualsportes::_getSinglefile()
	 *
	 * @return void
	 */
	function _getSinglefile()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    =& Factory::getApplication();

		$match_id = $app->getUserState($option . 'match_id');
		$query    = 'SELECT match_number
					FROM #__sportsmanagement_match
					WHERE id=' . $this->_db->Quote($match_id);
		$this->_db->setQuery($query);
		$match_number = $this->_db->loadResult();

		$dir   = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'ringerdateien';
		$files = Folder::files($dir, '^MKEinzelkaempfe_Data_' . $match_number, false, false, array('^Termine_Schema'));

		if ($files)
		{
			$app->enqueueMessage(Text::_('Einzelk&auml;mpfe ' . $match_number . ' vorhanden'), 'Notice');
		}
		else
		{
			$app->enqueueMessage(Text::_('Einzelk&auml;mpfe ' . $match_number . ' nicht vorhanden'), 'Error');
		}

	}

	/**
	 * sportsmanagementModeljlextindividualsportes::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{
		$app        = Factory::getApplication();
		$option     = Factory::getApplication()->input->getCmd('option');
		$project_id = $app->getUserState("$option.pid", '0');
		$match_id   = Factory::getApplication()->input->getvar('id', 0);;
		$projectteam1_id = Factory::getApplication()->input->getvar('team1', 0);;
		$projectteam2_id = Factory::getApplication()->input->getvar('team2', 0);;

		// Create a new query object.
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('mc.*');

		// From the hello table
		$query->from('#__sportsmanagement_match_single AS mc');
		$query->where('mc.match_id = ' . $match_id);

		$query->order(
			$db->escape($this->getState('list.ordering', 'mc.id')) . ' ' .
			$db->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;
	}

}
