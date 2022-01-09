<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       projectteam.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

/**
 * sportsmanagementModelprojectteam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelprojectteam extends JSMModelAdmin
{

	/**
	 * sportsmanagementModelprojectteam::set_playground_match()
	 *
	 * @param   mixed  $post
	 *
	 * @return void
	 */
	function set_playground_match($post)
	{
		$post       = Factory::getApplication()->input->post->getArray(array());
		$pks        = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$project_id = $post['pid'];
		$season_id  = $post['season_id'];

		for ($x = 0; $x < count($pks); $x++)
		{
			$projectteam_id = $pks[$x];
			$proTeam        = Table::getInstance('Projectteam', 'sportsmanagementTable');
			$proTeam->load($projectteam_id);
			$seasonteam_id = $proTeam->team_id;
			$playground_id = $this->getProjectTeamPlayground($seasonteam_id);

			if ($playground_id)
			{
				// Fields to update.
				$fields = array(
					$this->jsmdb->quoteName('playground_id') . ' = ' . $playground_id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$this->jsmdb->quoteName('projectteam1_id') . ' = ' . $projectteam_id
				);
				$this->jsmquery->clear();
				$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
				$this->jsmdb->setQuery($this->jsmquery);
				$result = $this->jsmdb->execute();
			}
		}

	}

	/**
	 * sportsmanagementModelprojectteam::getProjectTeamPlayground()
	 *
	 * @param   integer  $team_id
	 *
	 * @return
	 */
	function getProjectTeamPlayground($team_id = 0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('c.standard_playground');
		$this->jsmquery->from('#__sportsmanagement_club AS c');
		$this->jsmquery->join('INNER', '#__sportsmanagement_team AS t ON t.club_id = c.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id');
		$this->jsmquery->where('st.id = ' . $team_id);
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadResult();

		return $result;
	}

	/**
	 * sportsmanagementModelprojectteam::set_playground()
	 *
	 * @param   mixed  $post
	 *
	 * @return void
	 */
	function set_playground($post)
	{
		$post       = Factory::getApplication()->input->post->getArray(array());
		$pks        = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$project_id = $post['pid'];
		$season_id  = $post['season_id'];

		for ($x = 0; $x < count($pks); $x++)
		{
			$projectteam_id = $pks[$x];
			$proTeam        = Table::getInstance('Projectteam', 'sportsmanagementTable');
			$proTeam->load($projectteam_id);
			$seasonteam_id = $proTeam->team_id;
			$playground_id = $this->getProjectTeamPlayground($seasonteam_id);

			if ($playground_id)
			{
				$object                      = new stdClass;
				$object->id                  = $projectteam_id;
				$object->standard_playground = $playground_id;
				$result                      = Factory::getDbo()->updateObject('#__sportsmanagement_project_team', $object, 'id');
			}
		}
	}

	/**
	 * Method to update checked project teams
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	function saveshort()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		$pks  = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$post = Factory::getApplication()->input->post->getArray(array());

		$project_id     = $post['pid'];
		$new_project_id = $post['all_project_id'];
        
        $division_points = $post['division_points'];
        
		$this->jsmquery->clear();
		$this->jsmquery->select('l.associations');
		$this->jsmquery->from('#__sportsmanagement_league as l');
		$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p on p.league_id = l.id');
		$this->jsmquery->where('p.id = ' . $project_id);
		$this->jsmdb->setQuery($this->jsmquery);
		$associations = $this->jsmdb->loadResult();

		$result = true;

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblProjectteam                 = &$this->getTable();
			$tblProjectteam->id             = $pks[$x];
			$tblProjectteam->division_id    = $post['division_id' . $pks[$x]];
			$tblProjectteam->start_points   = $post['start_points' . $pks[$x]];
			$tblProjectteam->penalty_points = $post['penalty_points' . $pks[$x]];

			// Alle umsetzen
			if ($project_id != $new_project_id)
			{
				$tblProjectteam->project_id = $new_project_id;
			}
			else
			{
				$tblProjectteam->project_id = $post['new_project_id' . $pks[$x]];
			}

			$tblProjectteam->is_in_score = $post['is_in_score' . $pks[$x]];
			$tblProjectteam->use_finally = $post['use_finally' . $pks[$x]];
			$tblProjectteam->finaltablerank = $post['finaltablerank' . $pks[$x]];

			$tblProjectteam->points_finally     = $post['points_finally' . $pks[$x]];
			$tblProjectteam->neg_points_finally = $post['neg_points_finally' . $pks[$x]];
			$tblProjectteam->penalty_points     = $post['penalty_points' . $pks[$x]];
			$tblProjectteam->matches_finally    = $post['matches_finally' . $pks[$x]];
			$tblProjectteam->won_finally        = $post['won_finally' . $pks[$x]];
			$tblProjectteam->draws_finally      = $post['draws_finally' . $pks[$x]];
			$tblProjectteam->lost_finally       = $post['lost_finally' . $pks[$x]];
			$tblProjectteam->homegoals_finally  = $post['homegoals_finally' . $pks[$x]];
			$tblProjectteam->guestgoals_finally = $post['guestgoals_finally' . $pks[$x]];
			$tblProjectteam->diffgoals_finally  = $post['diffgoals_finally' . $pks[$x]];

			if (!$tblProjectteam->store())
			{
				$result = false;
			}

			/**
			 * hier werden noch die vereine aktualisiert
			 * wenn schon ein verband/kreis vorhanden ist, kein update
			 */
			$clubrow = Table::getInstance('club', 'sportsmanagementTable', array());
			$clubrow->load($post['club_id' . $pks[$x]]);

			if ($clubrow->associations)
			{
				$associations = '';
			}

			$object = new stdClass;
			$object->id           = $post['club_id' . $pks[$x]];
			$object->location     = $post['location' . $pks[$x]];
			$object->founded_year = $post['founded_year' . $pks[$x]];
			$object->unique_id    = $post['unique_id' . $pks[$x]];

			if ($associations)
			{
				$object->associations = $associations;
			}

			$result = Factory::getDbo()->updateObject('#__sportsmanagement_club', $object, 'id');
		}
        
        //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($division_points,true).'</pre>', 'error');
        for ($x = 0; $x < count($pks); $x++)
		{
          //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($pks[$x],true).'</pre>', 'error');
		foreach ( $division_points[$pks[$x]] as $division_id => $value )  
          {
            //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($division_id,true).'</pre>', 'error');
          //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($value,true).'</pre>', 'error');
// Fields to update.
$fields = array(
    $this->jsmdb->quoteName('start_points') . ' = ' . $value['start_points'],
    $this->jsmdb->quoteName('matches_finally') . ' = '.$value['matches_finally'],
    $this->jsmdb->quoteName('points_finally') . ' = '.$value['points_finally'],
    $this->jsmdb->quoteName('neg_points_finally') . ' = '.$value['neg_points_finally'],
    $this->jsmdb->quoteName('won_finally') . ' = '.$value['won_finally'],
    $this->jsmdb->quoteName('draws_finally') . ' = '.$value['draws_finally'],
    $this->jsmdb->quoteName('lost_finally') . ' = '.$value['lost_finally'],
    $this->jsmdb->quoteName('diffgoals_finally') . ' = '.$value['diffgoals_finally'],
    $this->jsmdb->quoteName('guestgoals_finally') . ' = '.$value['guestgoals_finally']
);
   
// Conditions for which records should be updated.
$conditions = array(
    $this->jsmdb->quoteName('team_id') . ' = ' . $pks[$x],
    $this->jsmdb->quoteName('division_id') . ' = ' . $division_id
);       
$this->jsmquery->clear();    
try
		{
$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_project_team_division'))->set($fields)->where($conditions);
$this->jsmdb->setQuery($this->jsmquery);
$resultupdate = $this->jsmdb->execute();           
        	}
		catch (Exception $e)
		{
//$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
//$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}   
            
          }
          
          }

		return $result;
	}

	/**
	 * sportsmanagementModelprojectteam::setseasonid()
	 *
	 * @return void
	 */
	function setseasonid()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// Get a db connection.
		$db         = Factory::getDbo();
		$post       = Factory::getApplication()->input->post->getArray(array());
		$pks        = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$project_id = $post['pid'];
		$season_id  = $post['season_id'];

		for ($x = 0; $x < count($pks); $x++)
		{
			$projectteam_id = $pks[$x];

			$proTeam = Table::getInstance('Projectteam', 'sportsmanagementTable');
			$proTeam->load($projectteam_id);
			$object            = new stdClass;
			$object->id        = $proTeam->team_id;
			$object->season_id = $season_id;
			$result            = Factory::getDbo()->updateObject('#__sportsmanagement_season_team_id', $object, 'id');

			if (!$result)
			{
			}
		}

	}

	/**
	 * sportsmanagementModelprojectteam::setusetable()
	 *
	 * @param   integer  $setzer
	 *
	 * @return void
	 */
	function setusetable($setzer = 0)
	{
		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');

		for ($x = 0; $x < count($pks); $x++)
		{
			$projectteam_id      = $pks[$x];
			$object              = new stdClass;
			$object->id          = $projectteam_id;
			$object->is_in_score = $setzer;
			$result              = Factory::getDbo()->updateObject('#__sportsmanagement_project_team', $object, 'id');

			if (!$result)
			{
			}
		}

	}

	/**
	 * sportsmanagementModelprojectteam::setusetablepoints()
	 *
	 * @param   integer  $setzer
	 *
	 * @return void
	 */
	function setusetablepoints($setzer = 0)
	{
		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');

		for ($x = 0; $x < count($pks); $x++)
		{
			$projectteam_id      = $pks[$x];
			$object              = new stdClass;
			$object->id          = $projectteam_id;
			$object->use_finally = $setzer;
			$result              = Factory::getDbo()->updateObject('#__sportsmanagement_project_team', $object, 'id');

			if (!$result)
			{
			}
		}

	}

	/**
	 * sportsmanagementModelprojectteam::matchgroups()
	 *
	 * @return void
	 */
	function matchgroups()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// Get a db connection.
		$db   = Factory::getDbo();
		$post = Factory::getApplication()->input->post->getArray(array());
		$pks  = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');

		for ($x = 0; $x < count($pks); $x++)
		{
			$projectteam_id          = $pks[$x];
			$projectteam_division_id = $post['division_id' . $pks[$x]];

			// Fields to update.
			$query = $db->getQuery(true);
			$query->clear();
			$fields = array(
				$db->quoteName('division_id') . '=' . $projectteam_division_id
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('projectteam1_id') . '=' . $projectteam_id
			);
			$query->update($db->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();

			if (!$result)
			{
			}

			$query->clear();
			$fields = array(
				$db->quoteName('division_id') . '=' . $projectteam_division_id
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('projectteam2_id') . '=' . $projectteam_id
			);
			$query->update($db->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();

			if (!$result)
			{
			}
		}

	}

	/**
	 * sportsmanagementModelprojectteam::storeAssign()
	 *
	 * @param   mixed  $post
	 *
	 * @return void
	 */
	function storeAssign($post)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// JInput object
		$jinput               = $app->input;
		$option               = $jinput->getCmd('option');
		$post                 = $jinput->post->getArray();
		$_pro_teams_to_delete = array();
		$query                = Factory::getDbo()->getQuery(true);

		if (ComponentHelper::getParams($option)->get('show_debug_info_backend'))
		{
		}

		$project_id  = $post['project_id'];
		$assign_id   = $post['project_teamslist'];
		$delete_team = $post['teamslist'];

		if ($delete_team)
		{
			$mdlProjectTeams = BaseDatabaseModel::getInstance("projectteams", "sportsmanagementModel");
			$project_teams   = $mdlProjectTeams->getAllProjectTeams($project_id, 0, $delete_team);

			foreach ($project_teams as $row)
			{
				$_pro_teams_to_delete[] = $row->projectteamid;
			}
		}

		foreach ($assign_id as $key => $value)
		{
			$query->clear();
			$query->select('id');
			$query->from('#__sportsmanagement_project_team');
			$query->where('team_id = ' . $value);
			$query->where('project_id = ' . $project_id);
			Factory::getDbo()->setQuery($query);
			$team_id = Factory::getDbo()->loadResult();

			if (!$team_id)
			{
				$profile             = new stdClass;
				$profile->project_id = $project_id;
				$profile->team_id    = $value;
				$result              = Factory::getDbo()->insertObject('#__sportsmanagement_project_team', $profile);
			}
		}

		if ($_pro_teams_to_delete)
		{
			self::delete($_pro_teams_to_delete);
		}

	}

	/**
	 * sportsmanagementModelprojectteam::delete()
	 *
	 * @param   mixed  $pks
	 *
	 * @return void
	 */
	public function delete(&$pks)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db     = Factory::getDbo();

		// $query    = $db->getQuery(true);

		// Als erstes die heimspiele
		if (count($pks))
		{
			$cids = implode(',', $pks);

			// Ein JDatabaseQuery Objekt beziehen

			$query = $db->getQuery(true);
			$query->delete()->from('#__sportsmanagement_match')->where('projectteam1_id IN (' . $cids . ')');
			$db->setQuery($query);
			$result = sportsmanagementModeldatabasetool::runJoomlaQuery();

			if ($result)
			{
				$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_HOME'), '');
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_HOME_ERROR'), 'Error');
			}
		}

		// Dann die auswärtsspiele
		if (count($pks))
		{
			$cids = implode(',', $pks);

			// Ein JDatabaseQuery Objekt beziehen

			$query = $db->getQuery(true);
			$query->delete()->from('#__sportsmanagement_match')->where('projectteam2_id IN (' . $cids . ')');
			$db->setQuery($query);
			$result = sportsmanagementModeldatabasetool::runJoomlaQuery();

			if ($result)
			{
				$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_AWAY'), '');
			}
			else
			{
				$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DELETE_MATCH_AWAY_ERROR'), 'Error');
			}
		}

		// Dann den eigentlichen eintrag an joomla standard
		return parent::delete($pks);

	}

	/**
	 * sportsmanagementModelprojectteam::getProjectTeam()
	 *
	 * @param   integer  $team_id
	 *
	 * @return
	 */
	function getProjectTeam($team_id = 0)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		// Select some fields
		$query->select('t.*');

		// From table
		$query->from('#__sportsmanagement_team t');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
		$query->where('st.team_id = ' . $team_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

}
