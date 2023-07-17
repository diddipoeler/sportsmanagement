<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionuser
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

JLoader::import('components.com_sportsmanagement.models.predictionusers', JPATH_SITE);

/**
 * sportsmanagementViewPredictionUser
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewPredictionUser extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPredictionUser::init()
	 *
	 * @return void
	 */
	function init()
	{
		
		$js = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.js';
		$this->document->addScript($js);
		sportsmanagementModelProject::$projectid = $this->jinput->getint("pj", 0);
		$this->predictionMemberID                = $this->jinput->getint('uid', '0');
		$this->predictionGameID                  = $this->jinput->getint('prediction_id', '0');
		$this->rounds                            = sportsmanagementModelProject::getRounds('ASC', $this->jinput->getint("cfg_which_database", 0));
		$this->round_labels                      = array();

		foreach ($this->rounds as $r)
		{
			$this->round_labels[] = '"' . $r->name . '"';
		}

		$mdlPredUsers = BaseDatabaseModel::getInstance("predictionusers", "sportsmanagementModel");

		$this->predictionGame = sportsmanagementModelPrediction::getPredictionGame();

		$tippAllowed = false;

		if (isset($this->predictionGame))
		{
			$config            = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
			$overallConfig     = sportsmanagementModelPrediction::getPredictionOverallConfig();
			$tipprankingconfig = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionranking');
			$configavatar = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');

			$this->model        = $mdlPredUsers;
			$this->roundID      = sportsmanagementModelPrediction::$roundID;
			$this->config       = array_merge($overallConfig, $tipprankingconfig, $config);
			$this->configavatar = $configavatar;

			$this->predictionMember = sportsmanagementModelPrediction::getPredictionMember($configavatar);

			if (!isset($this->predictionMember->id))
			{
				$this->predictionMember->id   = 0;
				$this->predictionMember->pmID = 0;
			}

			$this->predictionProjectS = sportsmanagementModelPrediction::getPredictionProjectS();

			$this->actJoomlaUser      = Factory::getUser();
			$this->isPredictionMember = sportsmanagementModelPrediction::checkPredictionMembership();
			$this->memberData         = sportsmanagementModelPredictionUsers::memberPredictionData();
			$this->allowedAdmin       = sportsmanagementModelPrediction::getAllowed();

			if (!empty($this->predictionMember->user_id))
			{
				$this->showediticon = sportsmanagementModelPrediction::getAllowed($this->predictionMember->user_id);
			}

			$this->_setPointsChartdata(array_merge($config));
			$this->_setRankingChartdata(array_merge($config));

			if (ComponentHelper::getParams($this->option)->get('show_debug_info_frontend'))
			{
			}

			$lists = array();

			if ($this->predictionMember->pmID > 0)
			{
				$dMemberID = $this->predictionMember->pmID;
			}
			else
			{
				$dMemberID = 0;
			}

			if (!$this->allowedAdmin)
			{
				$userID = $this->actJoomlaUser->id;
			}
			else
			{
				$userID = null;
			}

			$predictionMembers[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_MEMBER'), 'value', 'text');

			if ($res = sportsmanagementModelPrediction::getPredictionMemberList($this->config, $userID))
			{
				$predictionMembers = array_merge($predictionMembers, $res);
			}

			$lists['predictionMembers'] = HTMLHelper::_('select.genericList', $predictionMembers, 'uid', 'class="inputbox" onchange="this.form.submit(); "', 'value', 'text', $dMemberID);
			unset($res);
			unset($predictionMembers);

			/** Prepare FAV Team */
			if (empty($this->predictionMember->fav_team))
			{
				$this->predictionMember->fav_team = '0,0';
			}

			$sFavTeamsList = explode(';', $this->predictionMember->fav_team);

			foreach ($sFavTeamsList AS $key => $value)
			{
				$dFavTeamsList[] = explode(',', $value);
			}

			foreach ($dFavTeamsList AS $key => $value)
			{
				$favTeamsList[$value[0]] = $value[1];
			}

			/** Prepare Champ Team */
			if (empty($this->predictionMember->champ_tipp))
			{
				$this->predictionMember->champ_tipp = '0,0';
			}

			$sChampTeamsList = explode(';', $this->predictionMember->champ_tipp);

			foreach ($sChampTeamsList AS $key => $value)
			{
				$dChampTeamsList[] = explode(',', $value);
			}

			foreach ($dChampTeamsList AS $key => $value)
			{
				$champTeamsList[$value[0]] = $value[1];
			}

			/** Prepare Final4 Team */
			if (empty($this->predictionMember->final4_tipp))
			{
				$this->predictionMember->final4_tipp = '0,0';
			}

			$sFinal4TeamsList = explode(';', $this->predictionMember->final4_tipp);

			foreach ($sFinal4TeamsList AS $key => $value)
			{
				$dFinal4TeamsList[] = explode(',', $value);
			}

			foreach ($dFinal4TeamsList AS $key => $value)
			{
				$final4TeamsList[$value[0]][] = $value[1];
			}

			if ($this->getLayout() == 'edit')
			{
				/** schleife über die projekte */
				foreach ($this->predictionProjectS AS $predictionProject)
				{
					$projectteams[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_TEAM'), 'value', 'text');

					if ($res = sportsmanagementModelPredictionUsers::getPredictionProjectTeams($predictionProject->project_id))
					{
						$projectteams = array_merge($projectteams, $res);
					}

					if (!isset($favTeamsList[$predictionProject->project_id]))
					{
						$favTeamsList[$predictionProject->project_id] = 0;
					}

					$lists['fav_team'][$predictionProject->project_id] = HTMLHelper::_('select.genericList', $projectteams, 'fav_team[' . $predictionProject->project_id . ']', 'class="inputbox"', 'value', 'text', $favTeamsList[$predictionProject->project_id]);

					/** kann champion ausgewaehlt werden ? */
					if ($predictionProject->champ)
					{
						$disabled = '';
						/** ist überhaupt das startdatum gesetzt ? */
						if ($predictionProject->start_date == '0000-00-00')
						{
							$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING_STARTDATE'), 'Error');
							$disabled = ' disabled="disabled" ';
						}
						else
						{
							/** Wenn die Saison noch nicht angefangen hat, ist tippen erlaubt */
							$tippAllowed = !sportsmanagementModelPrediction::isProjectStarted($predictionProject);
							if (!$tippAllowed)
							{
								$disabled = ' disabled="disabled" ';
							}
							else
							{
								$disabled = '';
							}
						}
					}
					else
					{
						$disabled = ' disabled="disabled" ';
					}

					$predictionMembers[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PREDICTION_MEMBER_GROUP'), 'value', 'text');

					if ($res = sportsmanagementModelPrediction::getPredictionGroupList())
					{
						$predictionMembers = array_merge($predictionMembers, $res);
						/** Create selector only in case of having groups. View will hide selector automatically, if empty */
						$lists['grouplist'] = HTMLHelper::_('select.genericList', $predictionMembers, 'group_id', 'class="inputbox" ' . $disabled . 'onchange=""', 'value', 'text', $this->predictionMember->group_id);
					}

					unset($res);
					unset($predictionMembers);

					if (!isset($champTeamsList[$predictionProject->project_id]))
					{
						$champTeamsList[$predictionProject->project_id] = 0;
					}
					if (!isset($final4TeamsList[$predictionProject->project_id][0]))
					{
						$final4TeamsList[$predictionProject->project_id][0] = 0;
					}
					if (!isset($final4TeamsList[$predictionProject->project_id][1]))
					{
						$final4TeamsList[$predictionProject->project_id][1] = 0;
					}
					if (!isset($final4TeamsList[$predictionProject->project_id][2]))
					{
						$final4TeamsList[$predictionProject->project_id][2] = 0;
					}
					if (!isset($final4TeamsList[$predictionProject->project_id][3]))
					{
						$final4TeamsList[$predictionProject->project_id][3] = 0;
					}

					// NOT USED?? $lists['champ_tipp_disabled'][$predictionProject->project_id] = HTMLHelper::_('select.genericList', $projectteams, 'champ_tipp[' . $predictionProject->project_id . ']', 'class="inputbox"' . $disabled . '', 'value', 'text', $champTeamsList[$predictionProject->project_id]);
					$lists['champ_tipp_enabled'][$predictionProject->project_id]  = HTMLHelper::_('select.genericList', $projectteams, 'champ_tipp[' . $predictionProject->project_id . ']', 'class="inputbox"' . $disabled . '', 'value', 'text', $champTeamsList[$predictionProject->project_id]);

					$lists['final4_tipp_1'][$predictionProject->project_id]  = HTMLHelper::_('select.genericList', $projectteams, 'final4_tipp1[' . $predictionProject->project_id . ']', 'class="inputbox"' . $disabled . '', 'value', 'text', $final4TeamsList[$predictionProject->project_id][0]);
					$lists['final4_tipp_2'][$predictionProject->project_id]  = HTMLHelper::_('select.genericList', $projectteams, 'final4_tipp2[' . $predictionProject->project_id . ']', 'class="inputbox"' . $disabled . '', 'value', 'text', $final4TeamsList[$predictionProject->project_id][1]);
					$lists['final4_tipp_3'][$predictionProject->project_id]  = HTMLHelper::_('select.genericList', $projectteams, 'final4_tipp3[' . $predictionProject->project_id . ']', 'class="inputbox"' . $disabled . '', 'value', 'text', $final4TeamsList[$predictionProject->project_id][2]);
					$lists['final4_tipp_4'][$predictionProject->project_id]  = HTMLHelper::_('select.genericList', $projectteams, 'final4_tipp4[' . $predictionProject->project_id . ']', 'class="inputbox"' . $disabled . '', 'value', 'text', $final4TeamsList[$predictionProject->project_id][3]);

					unset($projectteams);
				}

				$this->form = $this->get('form');
				$this->form->setValue('picture', null, $this->predictionMember->picture);
			}
			else
			{
				$this->favTeams   = $favTeamsList;
				$this->champTeams = $champTeamsList;
				$this->final4Teams = $final4TeamsList;
			}

			$this->lists       = $lists;
			$this->tippallowed = $tippAllowed;

			/** Set page title */
			$pageTitle         = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE');
			$this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE');

			$this->document->setTitle($pageTitle);
		}
		else
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), Log::INFO, 'jsmerror');
		}

	}

	/**
	 * sportsmanagementViewPredictionUser::_setPointsChartdata()
	 *
	 * @param   mixed  $config
	 *
	 * @return
	 */
	function _setPointsChartdata($config)
	{
		$data = sportsmanagementModelPredictionUsers::getPointsChartData();

		/** Calculate Values for Chart Object */
		$userpoints     = array();
		$PointsCountMax = 0;

		foreach ($data as $rw)
		{
			if (!$rw->points)
			{
				$rw->points = 0;
			}

			$userpoints[]   = (int) $rw->points;
			$PointsCountMax = (int) $rw->points > $PointsCountMax ? (int) $rw->points : $PointsCountMax;
		}

		$this->PointsCountMax = $PointsCountMax;
		$this->userpoints     = $userpoints;

	}

	/**
	 * sportsmanagementViewPredictionUser::_setRankingChartdata()
	 *
	 * @param   mixed  $config
	 *
	 * @return
	 */
	function _setRankingChartdata($config)
	{
		$this->userranking                                 = array();
		sportsmanagementModelPrediction::$predictionGameID = $this->jinput->getint("prediction_id", 0);
		$memberlist                                        = sportsmanagementModelPrediction::getPredictionMemberList($this->config);
		$this->RankingCountMax                             = sizeof($memberlist);

		foreach ($this->rounds as $r)
		{
			$data = sportsmanagementModelPredictionUsers::getRanksChartData($this->predictionGameID, $r->id);

			foreach ($data as $key => $value)
			{
				if ($value->member_id == $this->predictionMemberID)
				{
					$this->userranking[] = $key + 1;
				}
			}
		}

	}
}
