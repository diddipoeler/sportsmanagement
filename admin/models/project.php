<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       project.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;

/**
 * sportsmanagementModelProject
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelProject extends JSMModelAdmin
{
	static $db_num_rows = 0;

	var $_tables_to_delete = array();

	
	
	/**
	 * sportsmanagementModelProject::setleaguechampion()
	 * 
	 * @return
	 */
	public function setleaguechampion()
	{
		$app   = Factory::getApplication();
		$date  = Factory::getDate();
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Get the input
		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');

		if (!$pks)
		{
			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
		}

		$post = Factory::getApplication()->input->post->getArray(array());
		// $app->enqueueMessage('<pre>'.print_r($post,true).'</pre>'   , 'notice');
for ($x = 0; $x < count($pks); $x++)
		{
			$tblProject                  = &$this->getTable();
			$tblProject->id              = $pks[$x];

$tblProject->use_leaguechampion = $post['use_leaguechampion' . $pks[$x]] ? 0 : 1;

			$tblProject->modified           = $date->toSql();
			$tblProject->modified_timestamp = sportsmanagementHelper::getTimestamp($date->toSql());

			if (!$tblProject->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);

				return false;
			}

			
		}

		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE');		
		
		
		
		
	}
	
	
	/**
	 * sportsmanagementModelProject::getTemplateConfig()
	 * 
	 * @param mixed $project_id
	 * @param mixed $template
	 * @param integer $cfg_which_database
	 * @param string $call_function
	 * @return
	 */
	public static function getTemplateConfig($project_id, $template, $cfg_which_database = 0, $call_function = '')
	{
		$app           = Factory::getApplication();
		$option        = $app->input->getCmd('option');
		$view          = $app->input->getVar("view");
		$db            = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query         = $db->getQuery(true);
		$checktemplate = false;

		switch ($view)
		{
			case 'editmatch':
			case 'editprojectteam':
			case 'editteam':
			case 'editperson':
			case 'editclub':
			case 'jltournamenttree':
				break;
			default:
				$checktemplate = true;
				break;
		}

		/**
		 *
		 * first load the default settings from the default <template>.xml file
		 */
		$paramsdata          = "";
		$arrStandardSettings = array();

		$xmlfile = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $template . '.xml';

		if ($project_id == 0)
		{
			return $arrStandardSettings;
		}

		$query->select('t.params');
		$query->from('#__sportsmanagement_template_config AS t');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = t.project_id');
		$query->where('t.template LIKE ' . $db->Quote($template));
		$query->where('p.id = ' . (int) $project_id);

		$starttime = microtime();
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($checktemplate)
		{
			if (!$result)
			{
				$project = self::getProject($project_id);

				if (!empty($project) && $project->master_template > 0)
				{
					$query->clear();
					$query->select('t.params');
					$query->from('#__sportsmanagement_template_config AS t');
					$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = t.project_id');
					$query->where('t.template LIKE ' . $db->Quote($template));
					$query->where('p.id = ' . $project->master_template);

					$starttime = microtime();
					$db->setQuery($query);
					$result = $db->loadResult();

					if (!$result)
					{
						// self::$projectwarnings[] = Text::_('COM_SPORTSMANAGEMENT_MASTER_TEMPLATE_MISSING') . " " . $template;
						// self::$projectwarnings[] = Text::_('COM_SPORTSMANAGEMENT_MASTER_TEMPLATE_MISSING_PID') . $project->master_template;
						// self::$projectwarnings[] = Text::_('COM_SPORTSMANAGEMENT_TEMPLATE_MISSING_HINT');
					
						return $arrStandardSettings;
					}
				}
				else
				{
					/**
					 *
					 * there are no saved settings found, use the standard xml file default values
					 */
					return $arrStandardSettings;
				}
			}
		}

		$jRegistry = new Registry;

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$jRegistry->loadString($result);
		}
		else
		{
			$jRegistry->loadJSON($result);
		}

		$configvalues = $jRegistry->toArray();

		/**
		 *
		 * merge and overwrite standard settings with individual view settings
		 */
		$settings = array_merge($arrStandardSettings, $configvalues);
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $settings;

	}
	
	/**
	 * sportsmanagementModelProject::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

	}

	/**
	 * sportsmanagementModelProject::getProjectsbyCurrentProjectLeagueSeason()
	 * 
	 * @param mixed $season_id
	 * @param mixed $league_id
	 * @return
	 */
	public static function getProjectsbyCurrentProjectLeagueSeason($season_id, $league_id)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);
		$query->select('id as value,name as text,name as info,picture as picture');
		$query->from('#__sportsmanagement_project');
		$query->where('season_id = ' . $season_id);
		$query->where('league_id = ' . $league_id);
		$query->order('name');
		try {
		$db->setQuery($query);
		$result = $db->loadObjectList();
}
catch (RuntimeException $e)
				{
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>' . print_r($this->jsmquery->dump(), true) . '</pre>', 'Error');
				}
		return $result;
	}

	/**
	 * return
	 *
	 * @param   int project_id
	 *
	 * @return integer
	 */
	public static function getProject($project_id)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$query->select('p.*, st.name AS sport_type_name');
		$query->from('#__sportsmanagement_project as p');
		$query->join('INNER', '#__sportsmanagement_sports_type AS st ON p.sports_type_id = st.id ');
		$query->where('p.id = ' . $project_id);
		$db->setQuery($query);
		$result = $db->loadObject();
		
		try{
		$query->clear();
		$query->select('eventtime,name as sports_type_name');
		$query->from('#__sportsmanagement_sports_type');
		$query->where('id = ' . $result->sports_type_id);
		$db->setQuery($query);
		$usesportstype         = $db->loadObject();
		$result->useeventtime = $usesportstype->eventtime;
        $result->sports_type_name = $usesportstype->sports_type_name;
			}
catch (Exception $e)
{
//$app->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
}

		return $result;
	}

	
	/**
	 * sportsmanagementModelProject::getProjectTeam()
	 * 
	 * @param mixed $projectteam_id
	 * @return
	 */
	function getProjectTeam($projectteam_id)
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);

		$query->select('t.*');
		$query->from('#__sportsmanagement_team AS t');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->where('pt.id = ' . $projectteam_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	
	/**
	 * sportsmanagementModelProject::getProjectTeamsOptions()
	 * 
	 * @param mixed $project_id
	 * @param integer $iDivisionId
	 * @return
	 */
	function getProjectTeamsOptions($project_id, $iDivisionId = 0)
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput               = $app->input;
		$option               = $jinput->getCmd('option');
		$db                   = sportsmanagementHelper::getDBConnection();
		$query                = $db->getQuery(true);
		$this->project_art_id = $app->getUserState("$option.project_art_id", '0');

		if ($this->project_art_id == 3)
		{
			// Select some fields
			$query->select("pt.id AS value,concat(t.lastname,' - ',t.firstname,'' ) AS text,t.notes");

			// From table
			$query->from('#__sportsmanagement_person AS t');
			$query->join('LEFT', '#__sportsmanagement_season_person_id AS st on st.person_id = t.id');
			$query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		}
		else
		{
			// Select some fields
			$query->select('pt.id AS value');
			$query->select('t.name AS text');
			$query->from('#__sportsmanagement_team AS t');
			$query->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
			$query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		}

		$query->where('pt.project_id = ' . $project_id);

		if ($iDivisionId > 0)
		{
			$query->where('pt.division_id = ' . $iDivisionId);
		}

		$query->order('text ASC');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($result === false)
		{
			Log::add($db->getErrorMsg());

			return false;
		}
		else
		{
			return $result;
		}
	}


	
	/**
	 * sportsmanagementModelProject::delete()
	 * 
	 * @param mixed $pks
	 * @return
	 */
	public function delete(&$pks)
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput  = $app->input;
		$option  = $jinput->getCmd('option');
		$success = $this->deleteProjectsData($pks);

		if ($success)
		{
			$app->setUserState("$option.pid", 0);

			return parent::delete($pks);
		}

	}


	/**
	 * sportsmanagementModelProject::deleteProjectsData()
	 * 
	 * @param mixed $pk
	 * @return
	 */
	function deleteProjectsData($pk = array())
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$result = false;

		if (count($pk))
		{
			$cids = implode(',', $pk);

			/** Rounds */
			$query->clear();
			$query->select('r.id');
			$query->from('#__sportsmanagement_round as r');
			$query->where('r.project_id IN (' . implode(",", $pk) . ')');
			Factory::getDBO()->setQuery($query);
			$rounds = Factory::getDbo()->loadColumn();

			/** Matches */
			if ($rounds)
			{
				$query->clear();
				$query->select('m.id');
				$query->from('#__sportsmanagement_match as m');
				$query->where('m.round_id IN (' . implode(",", $rounds) . ')');
				Factory::getDBO()->setQuery($query);
				$matches = Factory::getDbo()->loadColumn();
			}

			// Project_teams
			$query->clear();
			$query->select('p.id');
			$query->from('#__sportsmanagement_project_team as p');
			$query->where('p.project_id IN (' . implode(",", $pk) . ')');
			Factory::getDBO()->setQuery($query);
			$project_teams = Factory::getDbo()->loadColumn();

			// Project_referee
			$query->clear();
			$query->select('p.id');
			$query->from('#__sportsmanagement_project_referee as p');
			$query->where('p.project_id IN (' . implode(",", $pk) . ')');
			Factory::getDBO()->setQuery($query);
			$project_referee = Factory::getDbo()->loadColumn();

			// Project_position
			$query->clear();
			$query->select('p.id');
			$query->from('#__sportsmanagement_project_position as p');
			$query->where('p.project_id IN (' . implode(",", $pk) . ')');
			Factory::getDBO()->setQuery($query);
			$project_position = Factory::getDbo()->loadColumn();

			// Zu löschende tabellen
			$field       = 'project_id';
			$id          = implode(",", $pk);
			$temp        = new stdClass;
			$temp->table = '_project_position';
			$temp->field = $field;
			$temp->id    = $id;
			$export[]    = $temp;

			$temp        = new stdClass;
			$temp->table = '_person_project_position';
			$temp->field = $field;
			$temp->id    = $id;
			$export[]    = $temp;

			$temp        = new stdClass;
			$temp->table = '_project_referee';
			$temp->field = $field;
			$temp->id    = $id;
			$export[]    = $temp;
			$temp        = new stdClass;
			$temp->table = '_project_team';
			$temp->field = $field;
			$temp->id    = $id;
			$export[]    = $temp;
			$temp        = new stdClass;
			$temp->table = '_round';
			$temp->field = $field;
			$temp->id    = $id;
			$export[]    = $temp;
			$temp        = new stdClass;
			$temp->table = '_division';
			$temp->field = $field;
			$temp->id    = $id;
			$export[]    = $temp;

			if ($rounds)
			{
				$field       = 'round_id';
				$id          = implode(",", $rounds);
				$temp        = new stdClass;
				$temp->table = '_match';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
			}

			if ($matches)
			{
				$field       = 'match_id';
				$id          = implode(",", $matches);
				$temp        = new stdClass;
				$temp->table = '_match_commentary';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp        = new stdClass;
				$temp->table = '_match_event';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp        = new stdClass;
				$temp->table = '_match_player';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp        = new stdClass;
				$temp->table = '_match_referee';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp        = new stdClass;
				$temp->table = '_match_single';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp        = new stdClass;
				$temp->table = '_match_staff';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp        = new stdClass;
				$temp->table = '_match_staff_statistic';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp        = new stdClass;
				$temp->table = '_match_statistic';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
			}

			$this->_tables_to_delete = array_merge($export);

			/** Jetzt starten wir das löschen */
			foreach ($this->_tables_to_delete as $row_to_delete)
			{
				$query->clear();
				$query->delete()->from('#__sportsmanagement' . $row_to_delete->table)->where($row_to_delete->field . ' IN (' . $row_to_delete->id . ')');
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				if (self::$db_num_rows)
				{
					$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT' . strtoupper($row_to_delete->table) . '_ITEMS_DELETED', self::$db_num_rows), '');
				}
			}
		}

		return true;
	}


	/**
	 * sportsmanagementModelProject::saveshort()
	 * 
	 * @return
	 */
	public function saveshort()
	{
		$app   = Factory::getApplication();
		$date  = Factory::getDate();
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');

		if (!$pks)
		{
			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
		}

		$post = Factory::getApplication()->input->post->getArray(array());

		$query->select('id');
		$query->from('#__sportsmanagement_user_extra_fields');
		$query->where('template_backend  LIKE ' . $this->jsmdb->Quote('' . 'project' . '') . ' ');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		for ($x = 0; $x < count($pks); $x++)
		{
			foreach ($result as $id => $value)
			{
				$temp           = new stdClass;
				$temp->field_id = $value->id;
				$temp->jl_id    = $pks[$x];
				/** Insert the object into the table. */
				try
				{
					$resultinsert = $db->insertObject('#__sportsmanagement_user_extra_fields_values', $temp);
				}
				catch (Exception $e)
				{
				}
			}
		}

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblProject                  = &$this->getTable();
			$tblProject->id              = $pks[$x];
			$tblProject->project_type    = $post['project_type' . $pks[$x]];
			$tblProject->agegroup_id     = $post['agegroup' . $pks[$x]];
			$tblProject->master_template = $post['master_template' . $pks[$x]];
			$tblProject->fast_projektteam = $post['fast_projektteam' . $pks[$x]];
            $tblProject->use_leaguechampion = $post['use_leaguechampion' . $pks[$x]];

			if ($post['league' . $pks[$x]])
			{
				$tblProject->league_id = $post['league' . $pks[$x]];
			}

			$tblProject->modified           = $date->toSql();
			$tblProject->modified_timestamp = sportsmanagementHelper::getTimestamp($date->toSql());

			if (!$tblProject->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);

				return false;
			}

			if ($post['user_field_id' . $pks[$x]])
			{
				$object = new stdClass;
				$object->id         = $post['user_field_id' . $pks[$x]];
				$object->fieldvalue = $post['user_field' . $pks[$x]];
				$result = Factory::getDbo()->updateObject('#__sportsmanagement_user_extra_fields_values', $object, 'id');
			}
		}

		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE');
	}


}
