<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionusers
 * @file       predictionusers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelPredictionUsers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPredictionUsers extends BaseDatabaseModel
{
	static $config = null;

	/**
	 * sportsmanagementModelPredictionUsers::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$prediction = new sportsmanagementModelPrediction;

		sportsmanagementModelPrediction::$roundID = $jinput->getVar('r', '0');
		  sportsmanagementModelPrediction::$pjID = $jinput->getVar('pj', '0');
		  sportsmanagementModelPrediction::$from = $jinput->getVar('from', $jinput->getVar('r', '0'));
		  sportsmanagementModelPrediction::$to = $jinput->getVar('to', $jinput->getVar('r', '0'));

			 sportsmanagementModelPrediction::$predictionGameID = $jinput->getVar('prediction_id', '0');

			  sportsmanagementModelPrediction::$predictionMemberID = $jinput->getInt('uid', 0);
		sportsmanagementModelPrediction::$joomlaUserID = $jinput->getInt('juid', 0);

			  sportsmanagementModelPrediction::$pggroup = $jinput->getInt('pggroup', 0);
		sportsmanagementModelPrediction::$pggrouprank = $jinput->getInt('pggrouprank', 0);

			  sportsmanagementModelPrediction::$isNewMember = $jinput->getInt('s', 0);
		sportsmanagementModelPrediction::$tippEntryDone = $jinput->getInt('eok', 0);

			  sportsmanagementModelPrediction::$type = $jinput->getInt('type', 0);
		sportsmanagementModelPrediction::$page = $jinput->getInt('page', 1);

		parent::__construct();
	}

	/**
	 * sportsmanagementModelPredictionUsers::savememberdata()
	 *
	 * @return
	 */
	function savememberdata()
	{
		$document    = Factory::getDocument();
		  $option = Factory::getApplication()->input->getCmd('option');
		  $app = Factory::getApplication();
		  $jinput = $app->input;

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $result    = true;
		$post = $jinput->post->getArray();

		$predictionGameID = $post['prediction_id'];
		$joomlaUserID = $post['user_id'];
		$predictionMemberID    = $post['member_id'];
		$show_profile = $post['show_profile'];
		$fav_teams = $post['fav_team'];

		if (isset($post['champ_tipp']))
		{
			$champ_teams = $post['champ_tipp'];
		}

		$slogan    = $post['slogan'];
		$reminder = $post['reminder'];
		$receipt = $post['receipt'];
		$admintipp = $post['admintipp'];
		$group_id = $post['group_id'];
		$picture = $post['picture'];
		$aliasName = $post['aliasName'];
		$pRegisterDate = $post['registerDate'];
		$pRegisterTime = $post['registerTime'];

		$dFavTeams = '';

		foreach ($fav_teams AS $key => $value)
		{
			$dFavTeams .= $key . ',' . $value . ';';
		}

			$dFavTeams = trim($dFavTeams, ';');
		$dChampTeams = '';

		foreach ($champ_teams AS $key => $value)
		{
			$dChampTeams .= $key . ',' . $value . ';';
		}

			$dChampTeams = trim($dChampTeams, ';');

		$registerDate = sportsmanagementHelper::convertDate($pRegisterDate, 0) . ' ' . $pRegisterTime . ':00';

		// Must be a valid primary key value.
		$object = new stdClass;
		$object->id = $predictionMemberID;
		$object->registerDate = $registerDate;
		$object->show_profile = $show_profile;
		$object->group_id = $group_id;
		$object->fav_team = $dFavTeams;

		if ($dChampTeams)
		{
			$object->champ_tipp = $dChampTeams;
		}

		$object->slogan = $slogan;
		$object->aliasName = $aliasName;
		$object->reminder = $reminder;
		$object->receipt = $receipt;
		$object->admintipp = $admintipp;
		$object->picture = $picture;

		// Update their details in the table using id as the primary key.
		$resultquery = sportsmanagementHelper::getDBConnection()->updateObject('#__sportsmanagement_prediction_member', $object, 'id');

		if (!$resultquery)
		{
			$result = false;
		}

			return $result;
	}

	/**
	 * sportsmanagementModelPredictionUsers::showMemberPicture()
	 *
	 * @param   mixed   $outputUserName
	 * @param   integer $user_id
	 * @return void
	 */
	static function showMemberPicture($outputUserName, $user_id = 0)
	{

		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		  $query = $db->getQuery(true);
		$playerName = $outputUserName;
		$picture = '';

		if (self::$config['show_photo'])
		{
			/**
	 * von welcher komponente soll das bild kommen
	 * und ist die komponente installiert
	 */

			  $query->select('element');
			  $query->from('#__extensions');
			  $query->where("element LIKE '" . self::$config['show_image_from'] . "' ");

			$db->setQuery($query);
			$results = $db->loadResult();

			if (!$results)
			{
				  $app->enqueueMessage(Text::_('Die Komponente ' . self::$config['show_image_from'] . ' ist f&uuml;r das Profilbild nicht installiert'), 'Error');
			}

			  $query->select('avatar');
			  $query->where('userid = ' . (int) $user_id);

			switch (self::$config['show_image_from'])
			{
				case 'com_sportsmanagement':
				case 'prediction':
					$picture = sportsmanagementModelPrediction::$_predictionMember->picture;
				break;

				case 'com_cbe':
					$picture = 'components/com_cbe/assets/user.png';
					$query->from('#__cbe_users');
				break;

				case 'com_comprofiler':
					$query->clear('where');
					$query->from('#__comprofiler');
					$query->where('user_id = ' . (int) $user_id);
				break;

				case 'com_kunena':
					$query->from('#__kunena_users');
					$picture = 'media/kunena/avatars/resized/size200/nophoto.jpg';
				break;

				case 'com_community':
					$query->from('#__community_users');
				break;
			}

			switch (self::$config['show_image_from'])
			{
				case 'com_community':
				case 'com_cbe':
					if ($db->setQuery($query))
					{
						$results = $db->loadResult();

						if ($results)
						{
							$picture = $results;
						}
					}
					else
					{
					}
				break;
				case 'com_kunena':
					$db->setQuery($query);
					$results = $db->loadResult();

					if ($results)
					{
						$picture = 'media/kunena/avatars/' . $results;
					}
			  break;
				case 'com_comprofiler':
					$db->setQuery($query);
					$results = $db->loadResult();

					if ($results)
					{
						$picture = 'images/comprofiler/' . $results;
					}
			  break;
			}

			if (!file_exists($picture))
			{
				$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
			}

						echo sportsmanagementHelper::getPictureThumb($picture, $playerName, 0, 0);
		}
		else
		{
			echo '&nbsp;';
		}

	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app = Factory::getApplication('site');

		  // Get the form.
		$form = $this->loadForm(
			'com_sportsmanagement.' . $this->name, $this->name,
			array('load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * sportsmanagementModelPredictionUsers::memberPredictionData()
	 *
	 * @return
	 */
	static function memberPredictionData()
	{
		$dataObject = new stdClass;
		$dataObject->rankingAll        = 'X';
		$dataObject->lastTipp        = '';

		return $dataObject;
	}

	/**
	 * sportsmanagementModelPredictionUsers::getChampTippAllowed()
	 *
	 * @return
	 */
	function getChampTippAllowed()
	{
		$allowed = false;
		$user = & Factory::getUser();

		if ($user->id > 0)
		{
			$auth = & Factory::getACL();
			$aro_group = $auth->getAroGroup($user->id);

			if ((strtolower($aro_group->name) == 'super administrator') || (strtolower($aro_group->name) == 'administrator'))
			{
				$allowed = true;
			}
		}

		return $allowed;
	}

	/**
	 * sportsmanagementModelPredictionUsers::getPredictionProjectTeams()
	 *
	 * @param   mixed $project_id
	 * @return
	 */
	static function getPredictionProjectTeams($project_id)
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

			  $query->select('pt.id AS value,t.name AS text');
		$query->from('#__sportsmanagement_project_team AS pt');
		$query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
		$query->where('pt.project_id = ' . (int) $project_id);
		$query->order('text');

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}


	/**
	 * sportsmanagementModelPredictionUsers::getPointsChartData()
	 *
	 * @return
	 */
	static function getPointsChartData( )
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

			  $pgid    = $db->Quote(sportsmanagementModelPrediction::$predictionGameID);
		$uid    = $db->Quote(sportsmanagementModelPrediction::$predictionMemberID);

		$query->select('rounds.id,rounds.roundcode AS roundcode,rounds.name');
		$query->select('SUM(pr.points) AS points');
		$query->from('#__sportsmanagement_round AS rounds');
		$query->join('INNER', '#__sportsmanagement_match AS matches ON rounds.id = matches.round_id');
		$query->join('LEFT', '#__sportsmanagement_prediction_result AS pr ON pr.match_id = matches.id');
		$query->join('LEFT', '#__sportsmanagement_prediction_member AS prmem ON prmem.user_id = pr.user_id');

			  $query->where('pr.prediction_id = ' . $pgid);
		$query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
		$query->where('prmem.id = ' . $uid);
		$query->group('rounds.roundcode');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}



	/**
	 * sportsmanagementModelPredictionUsers::getRanksChartData()
	 *
	 * @param   integer $predictionGameID
	 * @param   integer $round_id
	 * @return
	 */
	static function getRanksChartData($predictionGameID=0,$round_id=0 )
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

		$query->select('rounds.id,rounds.roundcode AS roundcode,rounds.name,pr.user_id,prmem.id as member_id');
		$query->select('SUM(pr.points) AS points');
		$query->from('#__sportsmanagement_round AS rounds');
		$query->join('INNER', '#__sportsmanagement_match AS matches ON rounds.id = matches.round_id');
		$query->join('LEFT', '#__sportsmanagement_prediction_result AS pr ON pr.match_id = matches.id');
		$query->join('LEFT', '#__sportsmanagement_prediction_member AS prmem ON prmem.user_id = pr.user_id');

			  $query->where('pr.prediction_id = ' . $predictionGameID);
		$query->where('rounds.id = ' . (int) $round_id);
		$query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
		$query->order('points DESC');
		$query->group('rounds.roundcode,pr.user_id');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;

	}
}
