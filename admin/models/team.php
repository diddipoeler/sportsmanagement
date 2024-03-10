<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       team.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * sportsmanagementModelteam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelteam extends JSMModelAdmin
{
	static $change_training_date = false;


	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see   BaseDatabaseModel
	 * @since 3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app     = Factory::getApplication();
		$this->jinput  = $this->app->input;
		$this->option  = $this->jinput->getCmd('option');
		$this->club_id = $this->app->getUserState("$this->option.club_id", '0');
	}

/**
 * sportsmanagementModelteam::copysave()
 * 
 * @return
 */
public function copysave()
	{
		$this->jsmquery->clear();
		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		if (!$pks)
		{
			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE_NO_SELECT');
		}

		for ($x = 0; $x < count($pks); $x++)
		{
			$team = $this->getTeam($pks[$x]);
			$team->id = null;
			$team->name = $team->name . ' (Kopie)';
			try
			{
				parent::save(ArrayHelper::fromObject($team));
				$table      = $this->getTable();
	
				foreach ($table->getErrors() as $error)
				{
					$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $error), 'error');
				}
			}
			catch (Exception $e)
			{
				$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
				$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			}
		}

		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE');
	}
    
	/**
	 * sportsmanagementModelteam::getTeamLogo()
	 *
	 * @param   mixed  $team_id
	 *
	 * @return
	 */
	public static function getTeamLogo($team_id, $club_logo = 'small')
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		$query->select('c.logo_' . $club_logo . ' as logo_small,c.country,t.name,t.id as team_id');
		$query->from('#__sportsmanagement_team as t');
		$query->join('LEFT', '#__sportsmanagement_club c ON c.id = t.club_id');
		$query->where('t.id = ' . $team_id);

		$db->setQuery($query);
		try
		{
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			$result = false;
		}

		return $result;
	}

	/**
	 * return
	 *
	 * @param   int team_id
	 *
	 * @return int
	 */
	function getTeam($team_id = 0, $pro_team_id = 0)
	{
	   $result = array();
		//	   $app = Factory::getApplication();
		//        $option = Factory::getApplication()->input->getCmd('option');
		//		$db		= Factory::getDbo();
		//		$query	= $db->getQuery(true);
		$this->jsmquery->clear();
		// Select some fields
		$this->jsmquery->select('t.*');
		// From table
		$this->jsmquery->from('#__sportsmanagement_team t');

		if ($team_id)
		{
			$this->jsmquery->where('t.id = ' . $team_id);
		}
		else
		{
		  $this->jsmquery->select('st.logo_big');
			$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
			$this->jsmquery->where('st.id = ' . $pro_team_id);
		}

		$this->jsmdb->setQuery($this->jsmquery);

		try
		{
			$result = $this->jsmdb->loadObject();
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
			$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			//$result = false;
		}

		return $result;


	}

	/**
	 * Method to delete team trainingdata
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function DeleteTrainigData($id)
	{
		//        $option = Factory::getApplication()->input->getCmd('option');
		//		$app	= Factory::getApplication();
		//
		//    $db = Factory::getDbo();
		//
		//$query = $db->getQuery(true);
		$this->jsmquery->clear();
		// delete all custom keys
		$conditions = array(
			$this->jsmdb->quoteName('id') . '=' . $id
		);

		$this->jsmquery->delete($this->jsmdb->quoteName('#__sportsmanagement_team_trainingdata'));
		$this->jsmquery->where($conditions);

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->execute();
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			$result = false;
		}

		// store the variable that we would like to keep for next time
		// function syntax is setUserState( $key, $value );
		self::$change_training_date = $result;
		$this->jsmapp->setUserState("$this->jsmoption.change_training_date", self::$change_training_date);

		return $result;
	}

	/**
	 * Method to update team trainingdata
	 *
	 * @access public
	 * @return array
	 * @since  0.1
	 */
	function UpdateTrainigData($post)
	{
		$this->jsmquery->clear();
		for ($a = 0; $a < count($post['tdids']); $a++)
		{
			$rowtraining = Table::getInstance('TeamTrainingData', 'sportsmanagementTable');
			$rowtraining->load((int) $post['tdids'][$a]);

			$object = new stdClass();
			$object->id         = $post['tdids'][$a];
			$object->time_start = sportsmanagementHelper::time_to_sec($post['time_start'][$post['tdids'][$a]] . ':00');
			$object->time_end   = sportsmanagementHelper::time_to_sec($post['time_end'][$post['tdids'][$a]] . ':00');
			$object->place      = $post['place'][$post['tdids'][$a]];
			$object->notes      = $post['notes'][$post['tdids'][$a]];
			$object->dayofweek  = $post['dayofweek'][$post['tdids'][$a]];

			$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_team_trainingdata', $object, 'id', true);

			if ($object->time_start <> $rowtraining->time_start
				|| $object->time_end <> $rowtraining->time_end
				|| $object->place <> $rowtraining->place
				|| $object->notes <> $rowtraining->notes
				|| $object->dayofweek <> $rowtraining->dayofweek
			)
			{
				self::$change_training_date = true;
			}

		}

		// store the variable that we would like to keep for next time
		// function syntax is setUserState( $key, $value );
		$this->jsmapp->setUserState("$this->jsmoption.change_training_date", self::$change_training_date);

		return true;
	}


	/**
	 * sportsmanagementModelteam::getTrainigData()
	 * 
	 * @param integer $team_id
	 * @param integer $pro_team_id
	 * @return
	 */
	function getTrainigData($team_id = 0, $pro_team_id = 0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('tt.*');
		$this->jsmquery->from('#__sportsmanagement_team_trainingdata as tt');

		if ($team_id)
		{
			$this->jsmquery->where('tt.team_id = ' . $team_id);
		}
		elseif ($pro_team_id)
		{
			$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tt.team_id');
			$this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
			$this->jsmquery->where('pt.id = ' . $pro_team_id);
		}

		$this->jsmquery->order('dayofweek ASC');

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadObjectList();
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			$result = false;
		}

		if (!$result)
		{
			$this->jsmapp->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_NO_TRAINING'), 'Notice');
			return $result;
		}

		return $result;
	}

	
	/**
	 * sportsmanagementModelteam::addNewTrainigData()
	 * 
	 * @param integer $team_id
	 * @return
	 */
	function addNewTrainigData($team_id = 0)
	{
		$this->jsmquery->clear();
		$columns = array('team_id', 'notes');
		$values = array($team_id, $this->jsmdb->quote('-'));
		$this->jsmquery
			->insert($this->jsmdb->quoteName('#__sportsmanagement_team_trainingdata'))
			->columns($this->jsmdb->quoteName($columns))
			->values(implode(',', $values));
		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->execute();
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			$result = false;
		}

		$this->jsmapp->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_INSERT_TRAINING'), 'Notice');

		/** store the variable that we would like to keep for next time */
		self::$change_training_date = $result;
		$this->jsmapp->setUserState("$this->jsmoption.change_training_date", self::$change_training_date);

		return $result;
	}


	/**
	 * sportsmanagementModelteam::saveshort()
	 *
	 * @return
	 */
	public function saveshort()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		$this->jsmquery->clear();
		$pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		if (!$pks)
		{
			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE_NO_SELECT');
		}
		$post = Factory::getApplication()->input->post->getArray(array());

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblProject                 = &$this->getTable();
			$tblProject->id             = $pks[$x];
			$tblProject->sports_type_id = $post['sportstype' . $pks[$x]];
			$tblProject->agegroup_id    = $post['agegroup' . $pks[$x]];

			if (!$tblProject->store())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);

				return false;
			}
		}

		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_SAVE');
	}


}
