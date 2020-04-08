<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage prediction
 * @file       prediction.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;

jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper');
jimport('joomla.utilities.utility');
jimport('joomla.user.authorization');
jimport('joomla.access.access');
JLoader::import('components.com_sportsmanagement.models.rounds', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementModelPrediction
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPrediction extends BaseDatabaseModel
{
	static $_predictionGame        = null;

	static $predictionGameID        = 0;

	static $_roundNames = null;

	static $_predictionMember        = null;

	static $predictionMemberID        = 0;

	static $_projectRoundsCount        = 0;

	static $_predictionProjectS    = null;

	static $predictionProjectSIDs    = null;

	static $_predictionProject        = null;

	static $predictionProjectID    = null;

	static $show_debug_info    = false;

	static $joomlaUserID        = 0;

	static $roundID        = 0;

	static $pggroup        = 0;

	static $pggrouprank        = 0;

	static $pjID        = 0;

	static $isNewMember        = 0;

	static $tippEntryDone        = 0;

	static $from        = 0;

	static $to        = 0;

	static $type        = 0;

	static $page        = 0;

	static $cfg_which_database = 0;

	static $table_config = '';


	/**
	 * sportsmanagementModelPrediction::__construct()
	 *
	 * @return
	 */
	function __construct()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		self::$roundID = $jinput->getVar('r', '0');
		self::$pjID    = $jinput->getVar('pj', '0');
		self::$from    = $jinput->getVar('from', self::$roundID);
		self::$to = $jinput->getVar('to', self::$roundID);
		self::$predictionGameID    = $jinput->getVar('prediction_id', '0');
		self::$cfg_which_database = $jinput->getVar('cfg_which_database', '0');
		self::$predictionMemberID = $jinput->getVar('uid', '0');
		self::$joomlaUserID    = $jinput->getVar('juid', '0');
		self::$pggroup = $jinput->getVar('pggroup', '0');
		self::$pggrouprank = $jinput->getInt('pggrouprank', 0);
		self::$isNewMember = $jinput->getInt('s', 0);
		self::$tippEntryDone = $jinput->getInt('eok', 0);
		self::$type    = $jinput->getInt('type', 0);
		self::$page    = $jinput->getInt('page', 1);
		parent::__construct();
	}




	/**
	 * sportsmanagementModelPrediction::checkRoundID()
	 *
	 * @param   mixed $project_id
	 * @param   mixed $roundID
	 * @return void
	 */
	static function checkRoundID($project_id,$roundID)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$document    = Factory::getDocument();

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$query->select('roundcode');
		$query->from('#__sportsmanagement_round');
		$query->where('project_id = ' . (int) $project_id);
		$query->where('id = ' . (int) $roundID);
		$db->setQuery($query);

		$results = $db->loadResult();

		if (!$results)
		{
			$query->clear();
			$query->select('current_round');
			$query->from('#__sportsmanagement_project');
			$query->where('id = ' . (int) $project_id);
			$db->setQuery($query);
			$roundIDnew = $db->loadResult();

			$query->clear();
			$query->select("CONCAT_WS(':',id,alias) AS slug");
			$query->from('#__sportsmanagement_round');
			$query->where('id = ' . (int) $roundIDnew);
			$db->setQuery($query);
			$roundIDnew = $db->loadResult();

			self::$roundID = $roundIDnew;
		}

		if ((int) $roundID == 0)
		{
			$query->clear();
			$query->select('current_round');
			$query->from('#__sportsmanagement_project');
			$query->where('id = ' . (int) $project_id);
			$db->setQuery($query);
			$roundIDnew = $db->loadResult();

			$query->clear();
			$query->select("CONCAT_WS(':',id,alias) AS slug");
			$query->from('#__sportsmanagement_round');
			$query->where('id = ' . (int) $roundIDnew);
			$db->setQuery($query);
			$roundIDnew = $db->loadResult();

			self::$roundID = $roundIDnew;
		}

	}

	/**
	 * sportsmanagementModelPrediction::getChampionPoints()
	 *
	 * @param   mixed $champ_tipp
	 * @return
	 */
	static function getChampionPoints($champ_tipp)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $ChampPoints = 0;

		$resultchamp = 0;
		$resultchamppoints = 0;

		$sChampTeamsList = array();
		$dChampTeamsList = array();
		$champTeamsList = array();

		// Select champion from project

		$query->select('league_champ,points_tipp_champ');
		$query->from('#__sportsmanagement_prediction_project');
		$query->where('prediction_id = ' . (int) self::$predictionGameID);
		$query->where('project_id = ' . (int) self::$pjID);
		$query->where('champ = 1');
		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result)
		{
			$resultchamp = $result->league_champ;
			$resultchamppoints = $result->points_tipp_champ;
		}

			// User hat auch champion tip abgegeben
		if ($champ_tipp)
		{
			$sChampTeamsList = explode(';', $champ_tipp);

			foreach ($sChampTeamsList AS $key => $value)
			{
				$dChampTeamsList[] = explode(',', $value);
			}

			foreach ($dChampTeamsList AS $key => $value)
			{
				$champTeamsList[$value[0]] = $value[1];
			}

			if (isset($champTeamsList[(int) self::$pjID]))
			{
				if ($champTeamsList[(int) self::$pjID] == $resultchamp)
				{
					$ChampPoints = $resultchamppoints;
				}
			}
		}

				  $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $ChampPoints;
	}


	/**
	 * sportsmanagementModelPrediction::getPredictionGame()
	 *
	 * @return
	 */
	static function getPredictionGame($predictionGameID=0)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		if ($predictionGameID)
		{
			self::$predictionGameID = $predictionGameID;
		}

			   // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if (!self::$_predictionGame)
		{
			if (self::$predictionGameID > 0)
			{
							  $query->clear();
				 $query->select('*');
				 $query->select("CONCAT_WS(':',id,alias) AS slug");
				 $query->from('#__sportsmanagement_prediction_game');
				 $query->where('id = ' . (int) self::$predictionGameID);
				 $query->where('published = 1');

					  $db->setQuery($query, 0, 1);
				self::$_predictionGame = $db->loadObject();

				if (!self::$_predictionGame)
				{
				}
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$_predictionGame;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionMemberAvatar()
	 *
	 * @param   mixed $members
	 * @param   mixed $configavatar
	 * @return
	 */
	static function getPredictionMemberAvatar($members, $configavatar)
	{

		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $picture = '';
		$query->select('avatar');
		$query->where('userid = ' . (int) $members);

		switch ($configavatar)
		{
			case 'prediction':
				$picture = 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';

				$query->clear('select');
				$query->clear('where');
				$query->select('picture');
				$query->from('#__sportsmanagement_prediction_member');
				$query->where('user_id = ' . (int) $members);
				$query->where('prediction_id = ' . (int) self::$predictionGameID);
			break;

			case 'com_sportsmanagement':
				// Alles ok
			break;

			case 'com_cbe15':
				$picture = 'images/cbe/' . $members . '.png';
			break;

			case 'com_cbe25':
				$picture = 'components/com_cbe/assets/user.png';
				$query->from('#__cbe_users');
			break;

			case 'com_cbe':
				$picture = 'components/com_cbe/assets/user.png';
				$query->from('#__cbe_users');
			break;

			case 'com_kunena':
				$picture = 'media/kunena/avatars/resized/size200/nophoto.jpg';
				$query->from('#__kunena_users');
			break;

			case 'com_community':
				$query->from('#__community_users');
			break;

			case 'com_comprofiler':
				$query->clear('where');
				$query->from('#__comprofiler');
				$query->where('user_id = ' . (int) $members);
			break;
		}

		switch ($configavatar)
		{
			case 'prediction':
			case 'com_comprofiler':
			case 'com_community':
			case 'com_cbe':
			case 'com_cbe25':
			case 'prediction':
				try
				{
					   $db->setQuery($query);
					   $picture = $db->loadResult();
					   $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
				}
				catch (Exception $e)
				{
					$msg = $e->getMessage(); // Returns "Normally you would have other code...
					$code = $e->getCode(); // Returns
					$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
					Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');

					return false;
				}
			break;
			case 'com_kunena':
				try
				{
					   $db->setQuery($query);
					   $results = $db->loadResult();
					   $picture = 'media/kunena/avatars/' . $results;
					   $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
				}
				catch (Exception $e)
				{
					$msg = $e->getMessage(); // Returns "Normally you would have other code...
					$code = $e->getCode(); // Returns
					$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
					Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');

					return false;
				}
			break;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $picture;

	}


	/**
	 * sportsmanagementModelPrediction::getPredictionMember()
	 *
	 * @param   mixed $configavatar
	 * @return
	 */
	static function getPredictionMember($configavatar)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if (!self::$_predictionMember)
		{
			$query->clear();

				   // $query->select('pm.id AS pmID, pm.registerDate AS pmRegisterDate, pm.*');
			$query->select('CONCAT_WS(\':\',pm.id,u.username) AS pmID,CONCAT_WS(\':\',u.id,u.username) AS joomuserID, pm.registerDate AS pmRegisterDate, pm.*');
			$query->from('#__sportsmanagement_prediction_member AS pm');
			$query->join('LEFT', '#__users AS u ON u.id = pm.user_id');
			$query->where('pm.prediction_id = ' . (int) self::$predictionGameID);

			if ((int) self::$predictionMemberID > 0)
			{
				// $query->clear();
				$query->select('u.name, u.username');
				$query->select('pg.id as pg_group_id,pg.name as pg_group_name');
				$query->join('LEFT', '#__sportsmanagement_prediction_groups as pg ON pg.id = pm.group_id');
				$query->where('pm.id = ' . (int) self::$predictionMemberID);

				$db->setQuery($query, 0, 1);
				self::$_predictionMember = $db->loadObject();

				if (isset(self::$_predictionMember->pmID))
				{
					self::$predictionMemberID = self::$_predictionMember->pmID;
				}
				else
				{
				}
			}
			else
			{
				$user = Factory::getUser();

				if ($user->id > 0)
				{
					//	$query->clear();
					$query->select('u.*');
					$query->where('pm.user_id = ' . $user->id);

					$db->setQuery($query, 0, 1);
					self::$_predictionMember = $db->loadObject();

					if (isset(self::$_predictionMember->pmID))
					{
						self::$predictionMemberID = self::$_predictionMember->pmID;
						self::$joomlaUserID = self::$_predictionMember->joomuserID;
					}
					else
					{
											 self::$_predictionMember = new stdclass;
						self::$_predictionMember->id = 0;
						self::$_predictionMember->pmID = 0;
						self::$predictionMemberID = 0;
					}
				}
				else
				{
					self::$_predictionMember = new stdclass;
					self::$_predictionMember->id = 0;
					self::$_predictionMember->pmID = 0;
					self::$predictionMemberID = 0;
				}
			}
		}

		if (isset(self::$_predictionMember->user_id))
		{
			self::$_predictionMember->picture = self::getPredictionMemberAvatar(self::$_predictionMember->user_id, $configavatar['show_image_from']);
		}

			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return self::$_predictionMember;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionProjectS()
	 *
	 * @return
	 */
	static function getPredictionProjectS($predictionGameID=0)
	{
		 // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ($predictionGameID)
		{
			self::$predictionGameID = $predictionGameID;
		}

		if (!(int) self::$_predictionProjectS)
		{
			if ((int) self::$predictionGameID > 0)
			{
								 $query->clear();
				   $query->select('pp.*');
				   $query->select('p.name AS projectName, p.start_date');
				   $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
				   $query->from('#__sportsmanagement_prediction_project AS pp');
				   $query->join('LEFT', '#__sportsmanagement_project AS p ON p.id = pp.project_id');
				   $query->where('pp.prediction_id = ' . (int) self::$predictionGameID);
				   $query->where('pp.published = 1');

					  $db->setQuery($query);
					  self::$_predictionProjectS = $db->loadObjectList();
			}
		}

		// Das startdatum �berpr�fen
		foreach (self::$_predictionProjectS as $row)
		{
			if ($row->start_date == '0000-00-00')
			{
				$query->clear();
				$query->select('min(round_date_first)');
				$query->from('#__sportsmanagement_round');
				$query->where('project_id = ' . $row->project_id);

							$db->setQuery($query);
				  $row->start_date = $db->loadResult();
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$_predictionProjectS;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionOverallConfig()
	 *
	 * @return
	 */
	static function getPredictionOverallConfig()
	{
		return self::getPredictionTemplateConfig('predictionoverall');
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionTemplateConfig()
	 *
	 * @param   mixed $template
	 * @return
	 */
	static function getPredictionTemplateConfig($template)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$document = Factory::getDocument();

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		  $query->clear();
		  $query->select('t.params');
		  $query->from('#__sportsmanagement_prediction_template AS t');
		  $query->join('INNER', '#__sportsmanagement_prediction_game AS p ON p.id = t.prediction_id');
		  $query->where('t.template = ' . $db->Quote($template));
		  $query->where('p.id = ' . (int) self::$predictionGameID);

		$db->setQuery($query);

		if (!$result = $db->loadResult())
		{
			if (isset(self::$predictionGame) && (self::$predictionGame->master_template))
			{
				$query->clear('where');
					  $query->where('t.template = ' . $db->Quote($template));
					  $query->where('p.id = ' . $db->Quote(self::$predictionGame->master_template));

				$db->setQuery($query);

				if (!$result = $db->loadResult())
				{
					Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_PRED_MISSING_MASTER_TEMPLATE', $template, $predictionGame->master_template), Log::INFO, 'jsmerror');
					Log::add(Text::_('COM_SPORTSMANAGEMENT_PRED_MISSING_MASTER_TEMPLATE_HINT'), Log::INFO, 'jsmerror');
					echo '<br /><br />';

					return false;
				}
			}
			else
			{
				Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_PRED_MISSING_TEMPLATE', $template, self::$predictionGameID), Log::INFO, 'jsmerror');
				Log::add(Text::_('COM_SPORTSMANAGEMENT_PRED_MISSING_MASTER_TEMPLATE_HINT'), Log::INFO, 'jsmerror');
				echo '<br /><br />';

				return false;
			}
		}

		$jRegistry = new JRegistry;

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			  $jRegistry->loadString($result);
		}
		else
		{
			$jRegistry->loadJSON($result);
		}

		$configvalues = $jRegistry->toArray();

		// Check some defaults and init data for quicker access
		switch ($template)
		{
			case    'predictionoverall':    {
				if (!array_key_exists('sort_order_1', $configvalues))
												// For people updating,the ranking order won't be set until they edit
												// predictionoverall.xml. In that case,use a default sorting
				{
					$configvalues['sort_order_1'] = 'points';
					$configvalues['sort_order_2'] = 'correct_tipps';
					$configvalues['sort_order_3'] = 'correct_diffs';
					$configvalues['sort_order_4'] = 'correct_tend';
					$configvalues['sort_order_5'] = 'count_tipps_p';
				}

				break;
			}

			default:    {
				break;
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $configvalues;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionProject()
	 *
	 * @param   integer $project_id
	 * @return
	 */
	static function getPredictionProject($project_id=0)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ($project_id > 0)
		{
				 $query->clear();
			$query->select('*');
			$query->from('#__sportsmanagement_project');
			$query->where('id = ' . (int) $project_id);

			$db->setQuery($query);

			if (!$result = $db->loadObject())
			{
				return false;
			}

			if ($result->start_date == '0000-00-00')
			{
				$query->clear();
				$query->select('min(round_date_first)');
				$query->from('#__sportsmanagement_round');
				$query->where('project_id = ' . (int) $project_id);

				$db->setQuery($query);
				$result->start_date = $db->loadResult();
			}

			  return $result;
		}

			return false;
	}

	/**
	 * sportsmanagementModelPrediction::getMatchTeam()
	 *
	 * @param   integer $teamID
	 * @param   string  $teamName
	 * @return
	 */
	static function getMatchTeam($teamID=0,$teamName='name')
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  // $teamName='name';
		if ($teamID == 0)
		{
			return '#Error1 teamID==0 in ' . __METHOD__;
		}

		  $query->clear();
		$query->select('t.' . $teamName . ' as name');
		$query->from('#__sportsmanagement_team AS t');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt on pt.team_id = st.id');
		$query->where('pt.id = ' . (int) $teamID);

		$db->setQuery($query);
		$db->execute();

		if ($object = $db->loadObject())
		{
			return $object->name;
		}

		return '#Error2 teamname not found in ' . __METHOD__;

	}

	/**
	 * sportsmanagementModelPrediction::getMatchTeamClubLogo()
	 *
	 * @param   integer $teamID
	 * @return
	 */
	static function getMatchTeamClubLogo($teamID=0,$which_logo = 'logo_big')
	{
		 // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ($teamID == 0)
		{
			return '#Error1 in ' . __METHOD__;
		}

			   $query->select('c.' . $which_logo);
			$query->from('#__sportsmanagement_club AS c');
			$query->join('INNER', '#__sportsmanagement_team AS t on t.club_id = c.id');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt on pt.team_id = st.id');
			$query->where('pt.id = ' . (int) $teamID);

						  $db->setQuery($query);
			$db->execute();

		if ($object = $db->loadObject())
		{
					return $object->$which_logo;
		}

			return '#Error2 in ' . __METHOD__;

	}

	/**
	 * sportsmanagementModelPrediction::getMatchTeamClubFlag()
	 *
	 * @param   integer $teamID
	 * @return
	 */
	static function getMatchTeamClubFlag($teamID=0)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ($teamID == 0)
		{
			return '#Error1 in ' . __METHOD__;
		}

			   $query->select('c.country');
			$query->from('#__sportsmanagement_club AS c');
			$query->join('INNER', '#__sportsmanagement_team AS t on t.club_id = c.id');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt on pt.team_id = st.id');
			$query->where('pt.id = ' . (int) $teamID);

						  $db->setQuery($query);
			$db->execute();

		if ($object = $db->loadObject())
		{
					 return $object->country;
		}

			return '#Error2 in ' . __METHOD__;

	}


	/**
	 * sportsmanagementModelPrediction::getProjectSettings()
	 *
	 * @param   integer $pid
	 * @return
	 */
	static function getProjectSettings($pid=0)
	{
		 // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ($pid > 0)
		{
			  $query->select('CONCAT_WS(\':\',r.id,r.alias) AS slug');
			$query->from('#__sportsmanagement_round as r');
			$query->join('INNER', '#__sportsmanagement_project AS p ON p.current_round = r.id');
			$query->where('p.id = ' . (int) $pid);

			$db->setQuery($query, 0, 1);
			$result = $db->loadResult();

			if (!$result)
			{
				$query->clear();
				$query->select('CONCAT_WS(\':\',r.id,r.alias) AS slug');
				$query->from('#__sportsmanagement_round as r');
				$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');
				$query->where('p.id = ' . (int) $pid);

				$db->setQuery($query, 0, 1);
				$result = $db->loadResult();
			}

			return $result;
		}

			return false;
	}

	/**
	 * sportsmanagementModelPrediction::getProjectRounds()
	 *
	 * @param   integer $pid
	 * @return
	 */
	static function getProjectRounds($pid=0)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ($pid > 0)
		{
				$query->select('max(id)');
			$query->from('#__sportsmanagement_round');
			$query->where('project_id = ' . (int) $pid);

			$db->setQuery($query);
			self::$_projectRoundsCount = $db->loadResult();

			return self::$_projectRoundsCount;
		}

			return false;
	}

	/**
	 * sportsmanagementModelPrediction::checkPredictionMembership()
	 *
	 * @return
	 */
	static function checkPredictionMembership()
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('id');
		$query->from('#__sportsmanagement_prediction_member');
		$query->where('prediction_id = ' . (int) self::$predictionGameID);
		$query->where('user_id = ' . Factory::getUser()->id);
		$query->where('approved = 1');

		$db->setQuery($query, 0, 1);

		if (!$db->loadResult())
		{
			return false;
		}

		return true;
	}

	/**
	 * sportsmanagementModelPrediction::checkIsNotApprovedPredictionMember()
	 *
	 * @return
	 */
	static function checkIsNotApprovedPredictionMember()
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('user_id,approved');
		$query->from('#__sportsmanagement_prediction_member');
		$query->where('prediction_id = ' . (int) self::$predictionGameID);
		$query->where('user_id = ' . Factory::getUser()->id);

		$db->setQuery($query, 0, 1);

		if (!$result = $db->loadObject())
		{
			return 2;
		}

		if ($result->approved)
		{
			return 0;
		}

		return 1;
	}

	/**
	 * sportsmanagementModelPrediction::getAllowed()
	 *
	 * @param   integer $pmUID
	 * @return
	 */
	static function getAllowed($pmUID=0)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $allowed = false;
		$groupNames = '';

		// Application Instanz holen
		$app = Factory::getApplication();

		// JUserobjekt holen
		$user = Factory::getUser();

			  $authorised = JAccess::getAuthorisedViewLevels(Factory::getUser()->get('id'));

		$authorisedgroups = $user->getAuthorisedGroups();

		foreach ($user->groups as $groupId => $value)
		{
			$query->clear();
			$query->select('title');
			$query->from('#__usergroups');
			$query->where('id = ' . (int) $groupId);
			$db->setQuery($query);

			$groupNames .= $db->loadResult();
			$groupNames .= '<br/>';
		}

			  $groups = JAccess::getGroupsByUser($user->id, false);

		if ($user->id > 0)
		{
				  // $aro_group = $acl->getAroGroup($user->id);

			if (($groups[0] == 7) || ($groups[0] == 8))
			{
				$allowed = true;
			}
			else
			{
				if (($pmUID > 0) && ($pmUID == $user->id))
				{
					$allowed = true;
				}
				else
				{
					$predictionGame = self::getPredictionGame();
					$adminAllowed = $predictionGame->admin_tipp;

					if ($adminAllowed)
					{
							$predictionGameAdmins = self::getPredictionGameAdmins($predictionGame->id);

						foreach ($predictionGameAdmins AS $adminUserID)
						{
							if ($adminUserID == $user->id)
							{
								$allowed = true;
								break;
							}
						}
					}
				}
			}
		}

			return $allowed;
	}

	/**
	 * sportsmanagementModelPrediction::getSystemAdminsEMailAdresses()
	 *
	 * @return
	 */
	function getSystemAdminsEMailAdresses()
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('u.email');
		$query->from('#__users AS u');
		$query->where('u.sendEmail = 1');
		$query->where('u.block = 0');

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$query->join('INNER', '#__user_usergroup_map AS um ON um.user_id = u.id');
			$query->join('INNER', '#__usergroups AS ug ON ug.id = um.group_id');
			$query->where('ug.title LIKE ' . $db->Quote('' . 'Super Benutzer' . ''));
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$query->where('u.usertype LIKE ' . $db->Quote('' . 'Super Administrator' . ''));
		}

			  $query->order('u.email');

			$db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$res = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$res = $db->loadResultArray();
		}

			  return $res;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionGameAdminsEMailAdresses()
	 *
	 * @return
	 */
	function getPredictionGameAdminsEMailAdresses()
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('u.email');
		$query->from('#__users AS u');
		$query->join('INNER', '#__sportsmanagement_prediction_admin AS pa ON pa.user_id = u.id ');
		$query->where('u.block = 0');
		$query->where('u.sendEmail = 1');
		$query->where('pa.prediction_id = ' . (int) self::$predictionGameID);
		$query->order('u.email');

		$db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$res = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$res = $db->loadResultArray();
		}

			  return $res;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionGameAdmins()
	 *
	 * @param   mixed $predictionID
	 * @return
	 */
	static function getPredictionGameAdmins($predictionID)
	{
		 // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('user_id');
		$query->from('#__sportsmanagement_prediction_admin');
		$query->where('prediction_id = ' . (int) $predictionID);

		$db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$res = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$res = $db->loadResultArray();
		}

			  return $res;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionMemberEMailAdress()
	 *
	 * @param   mixed $predictionMemberID
	 * @return
	 */
	public static function getPredictionMemberEMailAdress($predictionMemberID)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('user_id');
		$query->from('#__sportsmanagement_prediction_member');
		$query->where('id = ' . (int) $predictionMemberID);

		$db->setQuery($query);

		if (!$user_id = $db->loadResult())
		{
			return false;
		}

			  $query->clear();
		$query->select('u.email');
		$query->from('#__users AS u');
		$query->where('u.block = 0');
		$query->where('u.id = ' . $user_id);
		$query->order('u.email');

		$db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$res = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$res = $db->loadResultArray();
		}

			return $res;
	}


	/**
	 * sportsmanagementModelPrediction::sendMemberTipResults()
	 *
	 * @param   mixed $predictionMemberID
	 * @param   mixed $predictionGameID
	 * @param   mixed $RoundID
	 * @param   mixed $ProjectID
	 * @param   mixed $joomlaUserID
	 * @return
	 */
	public static function sendMemberTipResults($predictionMemberID,$predictionGameID,$RoundID,$ProjectID,$joomlaUserID)
	{

		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$document    = Factory::getDocument();
		$app = Factory::getApplication();

		$configprediction    = self::getPredictionTemplateConfig('predictionentry');
		$overallConfig = self::getPredictionOverallConfig();
		$configprediction    = array_merge($overallConfig, $configprediction);
		$predictionProjectSettings = self::getPredictionProject($ProjectID);
		$predictionProject = self::getPredictionGame();
		$predictionProjectS = self::getPredictionProjectS();
		$match_ids = array();
		$round_ids = array();

		if ($configprediction['use_pred_select_matches'])
		{
			$match_ids = $configprediction['predictionmatchid'];
		}

		if ($configprediction['use_pred_select_rounds'])
		{
			$round_ids = $configprediction['predictionroundid'];
		}

			$roundResults = sportsmanagementModelPredictionEntry::getMatchesDataForPredictionEntry(
				(int) $predictionGameID,
				(int) $ProjectID,
				(int) $RoundID,
				$joomlaUserID,
				$match_ids,
				$round_ids
			);

												$predictionGameMemberMail = self::getPredictionMemberEMailAdress($predictionMemberID);

		// Fetch the mail object
		$mailer = Factory::getMailer();

		// Als html
		$mailer->isHTML(true);

		// Set a sender
		$config = Factory::getConfig();

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$sender = array($config->get('mailfrom'),$config->get('fromname'));
			$adminsenderemail = $config->get('mailfrom');
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$sender = array($config->getValue('config.mailfrom'),$config->getValue('config.fromname'));
			$adminsenderemail = $config->getValue('config.mailfrom');
		}

		// $sender = array($config->getValue('config.mailfrom'),$config->getValue('config.fromname'));
		$mailer->setSender($sender);

		$recipient = $predictionGameMemberMail;

		/**
 * zur sicherheit die tipeingaben auch dem admin zusenden
 * template: overall
 * parameter: send_admin_user_tipentry
 */
		if ($configprediction['send_admin_user_tipentry'])
		{
			$recipient[] = $sender[0];
		}

		// $mailer->addRecipient($predictionGameMemberMail);
		$mailer->addRecipient($recipient);

		/**
 * Create the mail
 */
		$mailer->setSubject(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAIL_TITLE'));

		foreach ($predictionProjectS AS $predictionProject)
		{
			$body = '';
			$totalPoints = 0;
			/**
 * jetzt die ergebnisse
 */
			$body .= "<html>";

			$body .= "<table class='blog' cellpadding='0' cellspacing='0' width='100%'>";
			$body .= "<tr>";
			$body .= "<td class='sectiontableheader'>";
			$body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_HEAD_ACTUAL_PRED_GAME', '<b><i>' . $predictionProject->projectName . '</i></b>');
			$body .= "</td>";
			$body .= "</tr>";
			$body .= "</table>";

					$body .= "<table width='100%' cellpadding='0' cellspacing='0'>";

			$body .= "<tr>";
			$body .= "<th class='sectiontableheader' style='text-align:center;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DATE_TIME') . "</th>";
			$body .= "<th class='sectiontableheader' style='text-align:center;' colspan='5' >" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MATCH') . "</th>";
			$body .= "<th class='sectiontableheader' style='text-align:center;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_RESULT') . "</th>";
			$body .= "<th class='sectiontableheader' style='text-align:center;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_YOURS') . "</th>";
			$body .= "<th class='sectiontableheader' style='text-align:center;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_POINTS') . "</th>";
			$body .= "</tr>";

			/**
 * schleife über die ergebnisse in der runde
 */
			foreach ($roundResults AS $result)
			{
				 $class = '';

				$resultHome = (isset($result->team1_result)) ? $result->team1_result : '-';

				if (isset($result->team1_result_decision))
				{
					   $resultHome = $result->team1_result_decision;
				}

				$resultAway = (isset($result->team2_result)) ? $result->team2_result : '-';

				if (isset($result->team2_result_decision))
				{
					   $resultAway = $result->team2_result_decision;
				}

				 $closingtime = $configprediction['closing_time'];// 3600=1 hour
				$matchTimeDate = sportsmanagementHelper::getTimestamp($result->match_date, 1, $predictionProjectSettings->timezone);
				$thisTimeDate = sportsmanagementHelper::getTimestamp(date("Y-m-d H:i:s"), 1, $predictionProjectSettings->timezone);
				$matchTimeDate = $matchTimeDate - $closingtime;

									   $body .= "<tr class='" . $class . "'>";
				$body .= "<td class='td_c'>";
				$jdate = Factory::getDate($result->match_date);
				$jdate->setTimezone(new DateTimeZone($predictionProjectSettings->timezone));
				$body .= $jdate->format('d.m.Y H:i');

				$body .= "</td>";

				 $homeName = self::getMatchTeam($result->projectteam1_id);
				$awayName = self::getMatchTeam($result->projectteam2_id);
				/**
 * clublogo oder vereinsflagge hometeam
 */
				$body .= "<td nowrap='nowrap' class='td_r'>";
				$body .= $homeName;
				$body .= "</td>";
				$body .= "<td nowrap='nowrap' class='td_c'>";

				if ($configprediction['show_logo_small'] == 1)
				{
					$logo_home = self::getMatchTeamClubLogo($result->projectteam1_id);

					if (($logo_home == '') || (!file_exists($logo_home)))
					{
						$logo_home = 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif';
					}

					$imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $homeName);
					$body .= HTMLHelper::image(Uri::root() . $logo_home, $imgTitle, array(' title' => $imgTitle));
					$body .= ' ';
				}

				if ($configprediction['show_logo_small'] == 2)
				{
					$country_home = self::getMatchTeamClubFlag($result->projectteam1_id);
					$body .= JSMCountries::getCountryFlag($country_home);
				}

				$body .= "</td>";

				$body .= "<td nowrap='nowrap' class='td_c'>";
				$body .= "<b>" . $configprediction['seperator'] . "</b>";
				$body .= "</td>";
				/**
 * clublogo oder vereinsflagge awayteam
 */
				$body .= "<td nowrap='nowrap' class='td_c'>";

				if ($configprediction['show_logo_small'] == 1)
				{
					$logo_away = self::getMatchTeamClubLogo($result->projectteam2_id);

					if (($logo_away == '') || (!file_exists($logo_away)))
					{
						$logo_away = 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif';
					}

					$imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $awayName);
					$body .= ' ';
					$body .= HTMLHelper::image(Uri::root() . $logo_away, $imgTitle, array(' title' => $imgTitle));
				}

				if ($configprediction['show_logo_small'] == 2)
				{
					$country_away = self::getMatchTeamClubFlag($result->projectteam2_id);
					$body .= JSMCountries::getCountryFlag($country_away);
				}

				$body .= "</td>";
				$body .= "<td nowrap='nowrap' class='td_l'>";
				$body .= $awayName;
				$body .= "</td>";

				/**
 * spielergebnisse
 */
				$body .= "<td class='td_c'>";
				$body .= $resultHome . $configprediction['seperator'] . $resultAway;
				$body .= "</td>";

				/**
 * tippergebnisse
 */
				$body .= "<td class='td_c'>";
				/**
 * Tipp in normal mode
 */
				if ($predictionProject->mode == '0')
				{
					$body .= $result->tipp_home . $configprediction['seperator'] . $result->tipp_away;
				}

				/**
 * Tipp in toto mode
 */
				if ($predictionProject->mode == '1')
				{
					$body .= $result->tipp;
				}

				$body .= "</td>";

				/**
 * punkte
 */
				$body .= "<td class='td_c'>";
				$points = self::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
				$totalPoints = $totalPoints + $points;
				$body .= $points;
				$body .= "</td>";
				$body .= "</tr>";
				/**
 * tendencen im tippspiel
 */
				if ($configprediction['show_tipp_tendence'])
				{
					$body .= "<tr class='tipp_tendence'>";
					$body .= "<td class='td_c'>";
					$body .= "&nbsp;";
					$body .= "</td>";

					$body .= "<td class='td_l' colspan='8'>";

					$totalCount = sportsmanagementModelPredictionEntry::getTippCount($ProjectID, $result->id, 3);
					$homeCount = sportsmanagementModelPredictionEntry::getTippCount($ProjectID, $result->id, 1);
					$awayCount = sportsmanagementModelPredictionEntry::getTippCount($ProjectID, $result->id, 2);
					$drawCount = sportsmanagementModelPredictionEntry::getTippCount($ProjectID, $result->id, 0);

					if ($totalCount > 0)
					{
						$percentageH = round(( $homeCount * 100 / $totalCount ), 2);
						$percentageD = round(( $drawCount * 100 / $totalCount ), 2);
						$percentageA = round(( $awayCount * 100 / $totalCount ), 2);
					}
					else
					{
						$percentageH = 0;
						$percentageD = 0;
						$percentageA = 0;
					}

					$body .= "<span style='color:" . $configprediction['color_home_win'] . "' >";
					$body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_HOME_WIN', $percentageH, $homeCount) . "</span><br />";
					$body .= "<span style='color:" . $configprediction['color_draw'] . "'>";
					$body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_DRAW', $percentageD, $drawCount) . "</span><br />";
					$body .= "<span style='color:" . $configprediction['color_guest_win'] . "'>";
					$body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_AWAY_WIN', $percentageA, $awayCount) . "</span>";
					$body .= "</td>";

					// $body .= "<td colspan='8'>&nbsp;</td>";
					$body .= "</tr>";
				}
				else
				{
					// $k = (1-$k);
				}
			}

			$body .= "<tr>";
			$body .= "<td colspan='8'>&nbsp;</td>";
			$body .= "<td class='td_c'>" . Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_TOTAL_POINTS_COUNT', $totalPoints) . "</td>";
			$body .= "</tr>";

			$body .= "<table>";

			if (($configprediction['show_help'] == 1)||($configprediction['show_help'] == 2))
			{
				$body .= sportsmanagementModelPredictionEntry::createHelptText($predictionProject->mode);
			}

			$body .= "</html>";
		}

		$mailer->setBody($body);

		if ($configprediction['admin_debug'])
		{
		}

		/**
 * Sending the mail
 */
		$send = $mailer->Send();

		if ($send !== true)
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAIL_SEND_ERROR'), 'Error');
		}
		else
		{
			 $emailadresses = implode(",", $predictionGameMemberMail);
			 $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAIL_SEND_OK', $emailadresses), 'notice');
		}

	}

	/**
	 * sportsmanagementModelPrediction::sendMembershipConfirmation()
	 *
	 * @param   mixed $cid
	 * @return
	 */
	function sendMembershipConfirmation($cid=array())
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		if (count($cid))
		{
			$cids = implode(',', $cid);

			// Create and send mail about registration in Prediction game
			$systemAdminsMails = self::getSystemAdminsEMailAdresses();
			$predictionGameAdminsMails = self::getPredictionGameAdminsEMailAdresses();

			foreach ($cid as $predictionMemberID)
			{
				$predictionGameMemberMail = self::getPredictionMemberEMailAdress($predictionMemberID);

				if (count($predictionGameMemberMail) > 0)
				{
					// Fetch the mail object
					$mailer = Factory::getMailer();

					// Set a sender
					$config = Factory::getConfig();

					if (version_compare(JVERSION, '3.0.0', 'ge'))
					{
						// Joomla! 3.0 code here
						$sender = array($config->get('mailfrom'),$config->get('fromname'));
					}
					elseif (version_compare(JVERSION, '2.5.0', 'ge'))
					{
						// Joomla! 2.5 code here
						$sender = array($config->getValue('config.mailfrom'),$config->getValue('config.fromname'));
					}

									  $mailer->setSender($sender);

					// Set Member as recipient
					$lastMailAdress = '';
					$recipient = array();

					foreach ($predictionGameMemberMail AS $predictionGameMember_EMail)
					{
						if ($lastMailAdress != $predictionGameMember_EMail)
						{
							$recipient[] = $predictionGameMember_EMail;
							$lastMailAdress = $predictionGameMember_EMail;
						}
					}

					$mailer->addRecipient($recipient);

					// Set system admins as BCC recipients
					$lastMailAdress = '';
					$recipientAdmins = array();

					foreach ($systemAdminsMails AS $systemAdminMail)
					{
						if ($lastMailAdress != $systemAdminMail)
						{
							$recipientAdmins[] = $systemAdminMail;
							$lastMailAdress = $systemAdminMail;
						}
					}

					$lastMailAdress = '';

					// Set predictiongame admins as BCC recipients
					foreach ($predictionGameAdminsMails AS $predictionGameAdminMail)
					{
						if ($lastMailAdress != $predictionGameAdminMail)
						{
							$recipientAdmins[] = $predictionGameAdminMail;
							$lastMailAdress = $predictionGameAdminMail;
						}
					}

					$mailer->addBCC($recipientAdmins);
					unset($recipientAdmins);

					// Create the mail
					$mailer->setSubject(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MEMBERSHIP_SUBJECT'));
					$body = Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MEMBERSHIP');

					$mailer->setBody($body);

									  // Optional file attached
					// $mailer->addAttachment(PATH_COMPONENT.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'document.pdf');

					// Sending the mail
					$send = $mailer->Send();

					if ($send !== true)
					{
						echo 'Error message: ' . $send->message;
					}
					else
					{
						echo 'Mail sent';
					}

					echo '<br /><br />';
				}
				else
				{
					// Joomla_user is blocked or has set sendEmail to off
					// can't send email
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * sportsmanagementModelPrediction::echoLabelTD()
	 *
	 * @param   mixed   $labelText
	 * @param   mixed   $labelTextHelp
	 * @param   integer $rowspan
	 * @return
	 */
	public static function echoLabelTD($labelText,$labelTextHelp,$rowspan=0)
	{
		?><td class='labelEdit'<?php echo ($rowspan > 1 ? ' rowspan="' . $rowspan . '"' : '')?> ><span class='hasTip' title="<?php echo Text::_($labelTextHelp); ?>"><?php echo Text::_($labelText); ?></span></td><?php
	}


	/**
	 * sportsmanagementModelPrediction::getPredictionGroupList()
	 *
	 * @return
	 */
	static function getPredictionGroupList()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('id AS value, name AS text');
		$query->from('#__sportsmanagement_prediction_groups ');
		$query->order('name ASC');

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}


	/**
	 * sportsmanagementModelPrediction::getPredictionMemberList()
	 *
	 * @param   mixed $config
	 * @param   mixed $actUserId
	 * @return
	 */
	static function getPredictionMemberList($config=null,$actUserId=null)
	{
		 // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ($config['show_full_name'] == 0)
		{
			$nameType = 'username';
		}
		else
		{
			$nameType = 'name';
		}

				  $query->select('pm.id AS value');
			$query->select('u.' . $nameType . ' AS text');
			$query->select('pg.id as pg_group_id,pg.name as pg_group_name');
			$query->from('#__sportsmanagement_prediction_member AS pm ');
			$query->join('LEFT', '#__users AS u ON u.id = pm.user_id');
			$query->join('LEFT', '#__sportsmanagement_prediction_groups as pg ON pg.id = pm.group_id');
			$query->where('prediction_id = ' . (int) self::$predictionGameID);

		if (isset($actUserId))
		{
			 $query->where('pm.approved = 1');
			$query->where('(pm.show_profile=1 OR pm.user_id=' . $actUserId . ')');
		}

			$db->setQuery($query);
			$results = $db->loadObjectList();

			return $results;
	}

	/**
	 * sportsmanagementModelPrediction::getMemberPredictionTotalCount()
	 *
	 * @param   mixed $user_id
	 * @return
	 */
	function getMemberPredictionTotalCount($user_id)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('count(*)');
		$query->from('#__sportsmanagement_prediction_result AS pr');
		$query->where('prediction_id = ' . (int) self::$predictionGameID);
		$query->where('user_id = ' . (int) $user_id);

		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	/**
	 * sportsmanagementModelPrediction::getMemberPredictionJokerCount()
	 *
	 * @param   mixed   $user_id
	 * @param   integer $project_id
	 * @return
	 */
	static function getMemberPredictionJokerCount($user_id,$project_id=0)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('count(id)');
		$query->from('#__sportsmanagement_prediction_result');
		$query->where('prediction_id = ' . (int) self::$predictionGameID);
		$query->where('user_id = ' . (int) $user_id);
		$query->where('joker = 1');

		if ($project_id > 0)
		{
			// $query .=     " AND project_id=$project_id";
			$query->where('project_id = ' . (int) $project_id);
		}

		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	/**
	 * sportsmanagementModelPrediction::createResultsObject()
	 *
	 * @param   mixed   $home
	 * @param   mixed   $away
	 * @param   mixed   $tipp
	 * @param   mixed   $tippHome
	 * @param   mixed   $tippAway
	 * @param   mixed   $joker
	 * @param   integer $homeDecision
	 * @param   integer $awayDecision
	 * @return
	 */
	static function createResultsObject($home,$away,$tipp,$tippHome,$tippAway,$joker,$homeDecision=0,$awayDecision=0)
	{
		$result = new stdClass;
		$result->team1_result            = $home;
		$result->team2_result            = $away;
		$result->team1_result_decision    = $homeDecision;
		$result->team2_result_decision    = $awayDecision;
		$result->tipp                    = $tipp;
		$result->tipp_home                = $tippHome;
		$result->tipp_away                = $tippAway;
		$result->joker                    = $joker;

		return $result;
	}

	/**
	 * sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch()
	 *
	 * @param   mixed $predictionProject
	 * @param   mixed $result
	 * @return
	 */
	static function getMemberPredictionPointsForSelectedMatch(&$predictionProject,&$result)
	{
		if ($predictionProject->mode == 0)    // Standard prediction Mode
		{
			if ((!isset($result->team1_result)) || (!isset($result->team2_result)) || (!isset($result->tipp_home)) || (!isset($result->tipp_away)))
			{
				return 0;
			}

			if (!$result->joker)    // No Joker was used for this prediction
			{
				// Prediction Result is the same as the match result / Top Tipp
				if (($result->team1_result == $result->tipp_home)&&($result->team2_result == $result->tipp_away))
				{
					return $predictionProject->points_correct_result;
				}

				// Prediction Result is not the same as the match result but the correct difference between home and
				// away result was tipped and the matchresult is draw
				if (($result->team1_result == $result->team2_result)
					&& ($result->team1_result - $result->team2_result) == ($result->tipp_home - $result->tipp_away)
				)
				{
					return $predictionProject->points_correct_draw;
				}

				// Prediction Result is not the same as the match result but the correct difference between home and
				// away result was tipped
				if (($result->team1_result - $result->team2_result) == ($result->tipp_home - $result->tipp_away))
				{
					return $predictionProject->points_correct_diff;
				}

				// Prediction Result is not the same as the match result but the tendence of the result is correct
				if (((($result->team1_result - $result->team2_result) > 0)&&(($result->tipp_home - $result->tipp_away) > 0))
					|| ((($result->team1_result - $result->team2_result) < 0)&&(($result->tipp_home - $result->tipp_away) < 0))
				)
				{
					return $predictionProject->points_correct_tendence;
				}

				// Prediction Result is totally wrong but we check if there is at least one point to give ;-)
				return $predictionProject->points_tipp;
			}
			else    // Member took a Joker for this prediction
			{
				// With Joker - Prediction Result is the same as the match result / Top Tipp
				if (($result->team1_result == $result->tipp_home)&&($result->team2_result == $result->tipp_away))
				{
					return $predictionProject->points_correct_result_joker;
				}

				// With Joker - Prediction Result is not the same as the match result but the correct difference between home and
				// away result was tipped and the matchresult is draw
				if (($result->team1_result == $result->team2_result)
					&& ($result->team1_result - $result->team2_result) == ($result->tipp_home - $result->tipp_away)
				)
				{
					return $predictionProject->points_correct_draw_joker;
				}

				// With Joker - Prediction Result is not the same as the match result but the correct difference between home and
				// away result was tipped
				if (($result->team1_result - $result->team2_result) == ($result->tipp_home - $result->tipp_away))
				{
					return $predictionProject->points_correct_diff_joker;
				}

				// Prediction Result is not the same as the match result but the tendence of the result is correct
				if (((($result->team1_result - $result->team2_result) > 0)&&(($result->tipp_home - $result->tipp_away) > 0))
					|| ((($result->team1_result - $result->team2_result) < 0)&&(($result->tipp_home - $result->tipp_away) < 0))
				)
				{
					return $predictionProject->points_correct_tendence_joker;
				}

				// With Joker - Prediction Result is totally wrong but we check if there is a point to give
				return $predictionProject->points_tipp_joker;
			}
		}
		else    // Toto Mode - No Joker is used here
		{
			if ((!isset($result->team1_result)) || (!isset($result->team2_result)))
			{
				return 0;
			}

			if (($result->team1_result > $result->team2_result) && ($result->tipp == "1"))
			{
				return $predictionProject->points_tipp;
			}

			if (($result->team1_result < $result->team2_result) && ($result->tipp == "2"))
			{
				return $predictionProject->points_tipp;
			}

			if (($result->team1_result == $result->team2_result) && ($result->tipp == "0"))
			{
				return $predictionProject->points_tipp;
			}

				return 0;
		}

		return 'ERROR';
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionMembersResultsList()
	 *
	 * @param   mixed   $project_id
	 * @param   mixed   $round1ID
	 * @param   integer $round2ID
	 * @param   integer $user_id
	 * @param   integer $type
	 * @return
	 */
	static function getPredictionMembersResultsList($project_id,$round1ID,$round2ID=0,$user_id=0,$type=0)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ((int) $round1ID == 0)
		{
			$round1ID = 1;
		}

				$query->select('m.id AS matchID,m.match_date,m.team1_result AS homeResult,m.team2_result AS awayResult,m.team1_result_decision AS homeDecision,m.team2_result_decision AS awayDecision');

			// Normale spielzeit
			$query->select('m.team1_result_split AS homeResultSplit,m.team2_result_split AS awayResultSplit');

			// Verlaengerung
			$query->select('m.team1_result_ot AS homeResultOT,m.team2_result_ot AS awayResultOT');

			// Elfmeter
			$query->select('m.team1_result_so AS homeResultSO,m.team2_result_so AS awayResultSO');
			$query->select('pr.id AS prID,pr.user_id AS prUserID,pr.tipp AS prTipp,pr.tipp_home AS prHomeTipp,pr.tipp_away AS prAwayTipp,pr.joker AS prJoker,pr.points AS prPoints,pr.top AS prTop,pr.diff AS prDiff,pr.tend AS prTend');
			$query->select('pm.id AS pmID');
			$query->from('#__sportsmanagement_match AS m');
			$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');

		if (isset($project_id) &&  (int) $project_id > 0)
		{
			$query->where('r.project_id = ' . (int) $project_id);
		}

			$query->where('r.id >= ' . (int) $round1ID);

		if ((isset($round2ID)) && ( (int) $round2ID > 0))
		{
				  $query->where('r.id <= ' . (int) $round2ID);
		}

			$query->join('LEFT', '#__sportsmanagement_prediction_result AS pr ON pr.match_id = m.id');

		if (isset($user_id) &&  (int) $user_id > 0)
		{
				  $query->where('pr.user_id = ' . (int) $user_id);
		}

			$query->join('INNER', '#__sportsmanagement_prediction_member AS pm ON pm.user_id = pr.user_id');

			$query->where('pm.prediction_id = ' . (int) self::$predictionGameID);
			$query->where('pr.prediction_id = ' . (int) self::$predictionGameID);
			$query->where('(m.cancel IS NULL OR m.cancel = 0)');

			  $query->order('pm.id,m.match_date,m.id ASC');

			 $db->setQuery($query);
			$results = $db->loadObjectList();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $results;
	}

	/**
	 * sportsmanagementModelPrediction::createProjectSelector()
	 *
	 * @param   mixed $predictionProjects
	 * @param   mixed $current
	 * @param   mixed $addTotalSelect
	 * @return
	 */
	static function createProjectSelector(&$predictionProjects,$current,$addTotalSelect=null)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

			 // $output='<select class="inputbox" name="set_pj" onchange="this.form.submit();" >';

				// $output='<select class="inputbox" id="p" name="p" onchange="document.forms[\'resultsRoundSelector\'].r.value=0;submit();" >';
		$output = '<select class="inputbox" id="pj" name="pj" onchange="this.form.submit();" >';

			  // $output='<select class="inputbox" id="pj" name="pj" onchange="this.form.submit();" >';

		if (isset($addTotalSelect))
		{
			$output .= '<option value="0"';

			if ($addTotalSelect == 0)
			{
				$output .= " selected='selected'";
			}

			$output .= '>' . Text::_('COM_SPORTSMANAGEMENT_PRED_TOTAL_RANKING') . '</option>';
		}
		else
		{
			$addTotalSelect = 1;
			$output .= '<option value="0"';
			$output .= '>' . Text::_('COM_SPORTSMANAGEMENT_ALL_PROJECTS') . '</option>';
		}

		foreach ($predictionProjects AS $predictionProject)
		{
			// $output .= '<option value="'.$predictionProject->project_id.'"';
			$output .= '<option value="' . $predictionProject->project_slug . '"';

			if (( $predictionProject->project_id == $current ) && ( $addTotalSelect > 0 ))
			{
				$output .= " selected='selected'";
			}

			$output .= '>' . $predictionProject->projectName . '</option>';
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionProjectNames()
	 *
	 * @param   mixed   $predictionID
	 * @param   string  $ordering
	 * @param   integer $limit
	 * @return
	 */
	static function getPredictionProjectNames($predictionID,$ordering='ASC',$limit=0)
	{
		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('ppj.id,pj.id AS prediction_id,pj.name AS pjName,CONCAT_WS(\':\',pj.id,pj.alias) AS slug');
		$query->from('#__sportsmanagement_project AS pj');
		$query->join('LEFT', '#__sportsmanagement_prediction_project AS ppj ON ppj.project_id = pj.id');
		$query->where('ppj.prediction_id = ' . (int) $predictionID);
		$query->order('ppj.id ' . $ordering);
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * sportsmanagementModelPrediction::savePredictionPoints()
	 *
	 * @param   mixed $memberResult
	 * @param   mixed $predictionProject
	 * @param   bool  $returnArray
	 * @return
	 */
	public static function savePredictionPoints(&$memberResult,&$predictionProject,$returnArray=false)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

				// $show_debug = $this->getDebugInfo();
		// [matchID] => 14501
		// [match_date] => 2010-08-21 15:30:00
		// [homeResult] => 5
		// [awayResult] => 5
		// [homeDecision] =>
		// [awayDecision] =>
		// [prID] => 3647
		// [prTipp] => 0
		// [prHomeTipp] => 5
		// [prAwayTipp] => 5
		// [prJoker] =>
		// [prPoints] => 7
		// [prTop] => 1
		// [prDiff] =>
		// [prTend] =>
		// [pmID] => 46

		$result = true;

			  $result_home = $memberResult->homeResult;
		$result_away = $memberResult->awayResult;

		$result_dHome = $memberResult->homeDecision;
		$result_dAway = $memberResult->awayDecision;

		$tipp_home = $memberResult->prHomeTipp;
		$tipp_away = $memberResult->prAwayTipp;

		$tipp = $memberResult->prTipp;
		$joker = $memberResult->prJoker;

		$points    = $memberResult->prPoints;
		$top = $memberResult->prTop;
		$diff = $memberResult->prDiff;
		$tend = $memberResult->prTend;

		if ($predictionProject->mode == 1)
		{
		}
		else
		{
			if ($tipp_home > $tipp_away)
			{
				$tipp = '1';
			}
			elseif ($tipp_home < $tipp_away)
			{
				$tipp = '2';
			}
			elseif (!is_null($tipp_home)&&!is_null($tipp_away))
			{
				$tipp = '0';
			}
			else
			{
				$tipp = null;
			}
		}

			$points        = 0;
			$top        = 0;
			$diff        = 0;
			$tend        = 0;

		if ($predictionProject->mode == 1)    // TOTO prediction Mode
		{
			// $points = $tipp;
			if (( $result_home > $result_away ) && ( $tipp == '1' ))
			{
				  $points = 1;
			}

			if (( $result_home < $result_away ) && ( $tipp == '2' ))
			{
					  $points = 1;
			}

			if (( $result_home == $result_away ) && ( $tipp == '0' ))
			{
				  $points = 1;
			}

			if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
			{
				$app->enqueueMessage(Text::_('toto points -> ' . $points . '<br>'), 'Notice');
			}
		}

		if (!is_null($tipp_home) && !is_null($tipp_away))
		{
			if ($predictionProject->mode == 1)    // TOTO prediction Mode
			{
				// $points = $tipp;
			}
			else    // Standard prediction Mode
			{
				if ($joker)    // Member took a Joker for this prediction
				{
					if (($result_home == $tipp_home)&&($result_away == $tipp_away))
					{
						// Prediction Result is the same as the match result / Top Tipp
						$points = $predictionProject->points_correct_result_joker;
						$top = 1;
					}
					elseif (($result_home == $result_away)&&($result_home - $result_away) == ($tipp_home - $tipp_away))
					{
						// Prediction Result is not the same as the match result but the correct difference between home and
						// away result was tipped and the matchresult is draw
						$points = $predictionProject->points_correct_draw_joker;

						// $diff = 1;
						// "$tend=1" should be used instead as "Prediction Result is not the same as the match result but the tendence of the result is correct"
								   $tend = 1; // updated 2018-08-25
					}
					elseif (($result_home - $result_away) == ($tipp_home - $tipp_away))
					{
						// Prediction Result is not the same as the match result but the correct difference between home and
						// away result was tipped
						$points = $predictionProject->points_correct_diff_joker;
						$diff = 1;
					}
					elseif (((($result_home - $result_away) > 0)&&(($tipp_home - $tipp_away) > 0))
						|| ((($result_home - $result_away) < 0)&&(($tipp_home - $tipp_away) < 0))
					)
					{
						// Prediction Result is not the same as the match result but the tendence of the result is correct
						$points = $predictionProject->points_correct_tendence_joker;
						$tend = 1;
					}
					else
					{
						// Prediction Result is totally wrong but we check if there is a point to give
						$points = $predictionProject->points_tipp_joker;
					}
				}
				else    // No Joker was used for this prediction
				{
					if (($result_home == $tipp_home)&&($result_away == $tipp_away))
					{
						// Prediction Result is the same as the match result / Top Tipp
						$points = $predictionProject->points_correct_result;
						$top = 1;
					}
					elseif (($result_home == $result_away)&&($result_home - $result_away) == ($tipp_home - $tipp_away))
					{
						// Prediction Result is not the same as the match result but the correct difference between home and
						// away result was tipped and the matchresult is draw
						$points = $predictionProject->points_correct_draw;

						// $diff = 1;
						// "$tend=1" should be used instead as "Prediction Result is not the same as the match result but the tendence of the result is correct"
								   $tend = 1; // updated 2018-08-25
					}
					elseif (($result_home - $result_away) == ($tipp_home - $tipp_away))
					{
						// Prediction Result is not the same as the match result but the correct difference between home and
						// away result was tipped
						$points = $predictionProject->points_correct_diff;
						$diff = 1;
					}
					elseif (((($result_home - $result_away) > 0)&&(($tipp_home - $tipp_away) > 0))
						|| ((($result_home - $result_away) < 0)&&(($tipp_home - $tipp_away) < 0))
					)
					{
						// Prediction Result is not the same as the match result but the tendence of the result is correct
						$points = $predictionProject->points_correct_tendence;
						$tend = 1;
					}
					else
					{
						// Prediction Result is totally wrong but we check if there is a point to give
						$points = $predictionProject->points_tipp;
					}
				}
			}
		}

			  // Must be a valid primary key value.
			$object = new stdClass;
			$object->id = $memberResult->prID;
			$object->tipp_home = $tipp_home;
			$object->tipp_away = $tipp_away;
			$object->tipp = $tipp;
			$object->joker = $joker;
			$object->points = $points;
			$object->top = $top;
			$object->diff = $diff;
			$object->tend = $tend;

			// Update their details in the table using id as the primary key.
			$result = sportsmanagementHelper::getDBConnection()->updateObject('#__sportsmanagement_prediction_result', $object, 'id');

		if ($returnArray)
		{
			$memberResult->tipp    = $tipp;
			$memberResult->points = $points;
			$memberResult->top = $top;
			$memberResult->diff    = $diff;
			$memberResult->tend    = $tend;

			return $memberResult;
		}

			  $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $result;
	}

	/**
	 * sportsmanagementModelPrediction::getRoundNames()
	 *
	 * @param   mixed  $project_id
	 * @param   string $ordering
	 * @return
	 */
	static function getRoundNames($project_id,$ordering='ASC', $round_ids = null)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$document    = Factory::getDocument();

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if (empty(self::$_roundNames))
		{
					 // $query->select('id AS value, name AS text');
			 $query->select("CONCAT_WS(':',id,alias) AS value");
			 $query->select('name AS text');
			 $query->from('#__sportsmanagement_round');
			 $query->where('project_id = ' . (int) $project_id);

			if ($round_ids)
			{
				$query->where('id IN (' . implode(',', $round_ids) . ')');
			}

			 $query->order('id ' . $ordering);

			$db->setQuery($query);
			self::$_roundNames = $db->loadObjectList();
		}

		return self::$_roundNames;
	}

	// General comparison of two tippers results
	// returns negative values for better tipper no 1
	// returns positive values for better tipper no 2
	// returns zero values for both tippers equal
	// 
	// ranking rules are described inside the code
	/**
	 * sportsmanagementModelPrediction::compare()
	 *
	 * @param   mixed $a
	 * @param   mixed $b
	 * @return
	 */
	static function compare($a,$b)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$res    = 0;
		$i        = 1;

		// While (array_key_exists('sort_order_'.$i,$this->table_config) and $res==0)
		while (array_key_exists('sort_order_' . $i, self::$table_config) && $res == 0)
		{
			switch (self::$table_config['sort_order_' . $i++])
			{
				// 1. decision: more points
				case 'points':
					$res = -($a['totalPoints'] - $b['totalPoints']);
				break;

				case 'correct_tips':
					$res = -($a['totalTop'] - $b['totalTop']);
				break;

				case 'correct_diffs':
					$res = -($a['totalDiff'] - $b['totalDiff']);
				break;

				case 'correct_tend':
					$res = -($a['totalTend'] - $b['totalTend']);
				break;

				case 'count_tips_p':
					$res = -($a['predictionsCount'] - $b['predictionsCount']);
					$res = strcasecmp($a['membernameAtoZ'], $b['membernameAtoZ']);
				break;

				case 'count_tips_m':
					$res = +($a['predictionsCount'] - $b['predictionsCount']);
					$res = strcasecmp($a['membernameAtoZ'], $b['membernameAtoZ']);
				break;

				default;
				break;
			}
		}

		return $res;
	}

	/**
	 * sportsmanagementModelPrediction::computeMembersRanking()
	 *
	 * @param   mixed $membersResultsArray
	 * @param   mixed $config
	 * @return
	 */
	static function computeMembersRanking($membersResultsArray,$config)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		self::$table_config = $config;
		$dummy = $membersResultsArray;

		uasort($dummy, array('self','compare'));

		$i = 1;
		$lfdnumber = 1;

		foreach ($dummy AS $key => $value)
		{
			if ($lfdnumber > 1 && ( $dummy[$array_pos]['totalPoints'] == $dummy[$key]['totalPoints'] ))
			{
				// Gleiche punkte
				$dummy[$key]['rank'] = '-';
			}
			else
			{
				$dummy[$key]['rank'] = $i;
			}

					$i++;

					  $lfdnumber++;
					$array_pos = $key;
		}

			  return $dummy;
	}

	/**
	 * sportsmanagementModelPrediction::getPredictionMembersList()
	 *
	 * @param   mixed $config
	 * @param   mixed $configavatar
	 * @param   bool  $total
	 * @param   mixed $limit
	 * @return
	 */
	static function getPredictionMembersList(&$config = null, &$configavatar = null, $total = false, $limit = null)
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if ($config['show_full_name'] == 0)
		{
			$nameType = 'username';
		}
		else
		{
			$nameType = 'name';
		}

				$query->select('pm.id AS pmID,pm.user_id AS user_id,pm.picture AS avatar,pm.show_profile AS show_profile,pm.champ_tipp AS champ_tipp,pm.aliasName as aliasName');
		  $query->select('u.' . $nameType . ' AS name');
		  $query->select('pg.id as pg_group_id,pg.name as pg_group_name');
		  $query->from('#__sportsmanagement_prediction_member AS pm');
		  $query->join('INNER', '#__users AS u ON u.id = pm.user_id');
		  $query->join('LEFT', '#__sportsmanagement_prediction_groups as pg on pg.id = pm.group_id');
		  $query->where('pm.prediction_id = ' . (int) self::$predictionGameID);

		switch ($configavatar['show_image_from'])
		{
			case 'com_cbe':

				$query->select('cbeu.latitude,cbeu.longitude');
				$query->join('INNER', '#__cbe_users AS cbeu ON cbeu.userid = u.id');
			break;
			case 'com_users':
			case 'prediction':
			break;

			case 'com_comprofiler':
				$query->select('cf.cb_streetaddress,cf.cb_city,cf.cb_state,cf.cb_zip,cf.cb_country');
				$query->join('INNER', '#__comprofiler AS cf ON cf.user_id = u.id');
			break;
			case 'com_kunena':
				$query->join('INNER', '#__kunena_users AS cf ON cf.userid = u.id');
			break;
		}

		if (self::$pggroup)
		{
			$query->where('pm.group_id = ' . (int) self::$pggroup);
		}

			$query->order('pm.id ASC');

		if ($total)
		{
			return $query;
		}
		else
		{
			try
			{
				$db->setQuery($query);
				$results = $db->loadObjectList();
				$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			}
			catch (Exception $e)
			{
					$msg = $e->getMessage(); // Returns "Normally you would have other code...
					$code = $e->getCode(); // Returns
					$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
					Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');

				return false;
			}

			foreach ($results as $row)
			{
						$picture = self::getPredictionMemberAvatar($row->user_id, $configavatar['show_image_from']);

				if ($picture)
				{
					$row->avatar = $picture;
				}
			}

			return $results;
		}

	}



}
