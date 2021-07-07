<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionranking
 * @file       default_ranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die(Text::_('Restricted access'));
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
	HTMLHelper::_('behavior.keepalive');
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
	HTMLHelper::_('behavior.tooltip');
}

?>

<style type="text/css">

    .pred_ranking ul {
        list-style: none;
    }

    .pred_ranking ul li {
        display: inline;
    }
</style>

<?php
foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
{
	$gotSettings = $predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id);
	if ((((int) sportsmanagementModelPrediction::$pjID == $predictionProject->project_id) && ($gotSettings)) || ((int) sportsmanagementModelPrediction::$pjID == 0))
	{
		$showProjectID                         = (count(sportsmanagementModelPrediction::$_predictionProjectS) > 1) ? sportsmanagementModelPrediction::$pjID : $predictionProject->project_slug;
		sportsmanagementModelPrediction::$pjID = $predictionProject->project_slug;
		$this->model->predictionProject        = $predictionProject;
		$actualProjectCurrentRound             = sportsmanagementModelPrediction::getProjectSettings($predictionProject->project_id);

		?>
        <form name='resultsRoundSelector' method='post'>
            <input type='hidden' name='prediction_id'
                   value='<?php echo sportsmanagementModelPrediction::$predictionGameID; ?>'/>
            <input type='hidden' name='pj' value='<?php echo $predictionProject->project_slug; ?>'/>
            <input type='hidden' name='r' value='<?php echo sportsmanagementModelPrediction::$roundID; ?>'/>
            <input type='hidden' name='pjID' value='<?php echo $showProjectID; ?>'/>
            <input type='hidden' name='task' value='predictionranking.selectprojectround'/>
            <input type='hidden' name='option' value='com_sportsmanagement'/>
            <input type='hidden' name='pggroup' value='<?php echo sportsmanagementModelPrediction::$pggroup; ?>'/>
            <input type='hidden' name='pggrouprank'
                   value='<?php echo sportsmanagementModelPrediction::$pggrouprank; ?>'/>
            <!--
			Responsive tables
			Create responsive tables by adding .table-responsive to any .table to make them scroll horizontally on small devices (under 768px).
			When viewing on anything larger than 768px wide, you will not see any difference in these tables.
			-->
            <div class="table-responsive">
                <table class="table table-responsive">
                    <tr>
                        <td>
							<?php
							echo '<b>' . Text::sprintf('COM_SPORTSMANAGEMENT_PRED_RANK_SUBTITLE_01') . '</b>';
							?>
                        </td>
                        <td>
							<?php

							echo HTMLHelper::_('select.genericlist', $this->lists['ranking_array'], 'pggrouprank', 'class="inputbox" size="1" onchange="this.form.submit(); "', 'value', 'text', sportsmanagementModelPrediction::$pggrouprank);
							?>
                        </td>
                        <td>
							<?php
							$groups             = sportsmanagementModelPrediction::getPredictionGroupList();
							$predictionGroups[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_GROUPS'), 'value', 'text');
							$predictionGroups   = array_merge($predictionGroups, $groups);
							$htmlGroupOptions   = HTMLHelper::_('select.genericList', $predictionGroups, 'pggroup', 'class="inputbox" onchange="this.form.submit(); "', 'value', 'text', sportsmanagementModelPrediction::$pggroup);
							echo $htmlGroupOptions;
							?>
                        </td>
                        <td>
							<?php
							echo sportsmanagementModelPrediction::createProjectSelector(
								sportsmanagementModelPrediction::$_predictionProjectS,
								$predictionProject->project_id,
								$showProjectID
							);
							if ($showProjectID > 0)
							{
							?>

                        <td>
							<?php
							echo '&nbsp;&nbsp;';
							$routeparameter                       = array();
							$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
							$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
							$routeparameter['p']                  = $predictionProject->project_slug;
							$routeparameter['r']                  = sportsmanagementModelPrediction::$roundID;
							$routeparameter['division']           = 0;
							$routeparameter['mode']               = 0;
							$routeparameter['order']              = '';
							$routeparameter['layout']             = '';
							$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

							$imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_ROUND_RESULTS_TITLE');
							$desc     = HTMLHelper::image('media/com_sportsmanagement/jl_images/icon-16-Matchdays.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
							echo HTMLHelper::link($link, $desc, array('target' => ''));
							?>
                        </td>

						<?php
						}
						?>
                        </td>
                    </tr>


                </table>
            </div>
            <br/>
			<?php echo HTMLHelper::_('form.token'); ?>
        </form>
		<?php
		$round_ids = null;
		if (($showProjectID > 0) && ($this->config['show_rankingnav']))
		{

			if ($this->configentries['use_pred_select_rounds'])
			{
				$round_ids = $this->configentries['predictionroundid'];
			}

			$from_matchday = $this->model->createMatchdayList($predictionProject->project_id, $round_ids);
			$to_matchday   = $this->model->createMatchdayList($predictionProject->project_id, $round_ids);
			?>
            <form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" name='adminForm'
                  id='adminForm' method='post'>
                <input type="hidden" name="view" value="predictionranking"/>
                <input type='hidden' name='prediction_id'
                       value='<?php echo sportsmanagementModelPrediction::$predictionGameID; ?>'/>
                <input type='hidden' name='pj' value='<?php echo $predictionProject->project_slug; ?>'/>
                <input type='hidden' name='r' value='<?php echo sportsmanagementModelPrediction::$roundID; ?>'/>
                <input type='hidden' name='pjID' value='<?php echo $showProjectID; ?>'/>
                <input type='hidden' name='task' value='predictionranking.selectprojectround'/>
                <input type='hidden' name='option' value='com_sportsmanagement'/>
                <input type='hidden' name='pggroup' value='<?php echo sportsmanagementModelPrediction::$pggroup; ?>'/>
                <input type='hidden' name='pggrouprank'
                       value='<?php echo sportsmanagementModelPrediction::$pggrouprank; ?>'/>
                <!--
				Responsive tables
				Create responsive tables by adding .table-responsive to any .table to make them scroll horizontally on small devices (under 768px).
				When viewing on anything larger than 768px wide, you will not see any difference in these tables.
				-->
                <div class="table-responsive">
                    <table class="table table-responsive">
                        <tr>
                            <td><?php echo HTMLHelper::_('select.genericlist', $this->lists['type'], 'type', 'class="inputbox" size="1"', 'value', 'text', sportsmanagementModelPrediction::$type); ?></td>
                            <td><?php echo HTMLHelper::_('select.genericlist', $from_matchday, 'from', 'class="inputbox" size="1"', 'value', 'text', sportsmanagementModelPrediction::$from); ?></td>
                            <td><?php echo HTMLHelper::_('select.genericlist', $to_matchday, 'to', 'class="inputbox" size="1"', 'value', 'text', sportsmanagementModelPrediction::$to); ?></td>
                            <td><input type='submit' class='button' name='reload View'
                                       value='<?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_FILTER'); ?>'/></td>
                        </tr>

                        <tfoot>
                        <div class="pagination">
                            <p class="counter">
								<?php echo $this->pagination->getPagesCounter(); ?>
                            </p>
                            <p class="counter">
								<?php echo $this->pagination->getResultsCounter(); ?>
                            </p>
							<?php echo $this->pagination->getPagesLinks(); ?>
                        </div>
						<?php
						//echo $this->pagination->getLimitBox();
						?>
                        </tfoot>

                    </table>
                </div>
				<?php echo HTMLHelper::_('form.token'); ?>
            </form>
            <br/>
			<?php

		}
		?>
        <!--
		Responsive tables
		Create responsive tables by adding .table-responsive to any .table to make them scroll horizontally on small devices (under 768px).
		When viewing on anything larger than 768px wide, you will not see any difference in these tables.
		-->
        <div class="table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?> <?PHP echo $this->config['table_class_responsive']; ?>">
                <thead>
                <tr>
                    <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK'); ?></th>
					<?php

					if (sportsmanagementModelPrediction::$pggrouprank)
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER_GROUP'); ?></th>
						<?php
					}
					else
					{
						if ($this->config['show_user_icon'])
						{
							?>
                            <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_AVATAR'); ?></th><?php
						}
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER'); ?></th>
						<?php

						if ($this->config['show_pred_group'])
						{
							?>
                            <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER_GROUP'); ?></th>
							<?php
						}

					}


					if ($this->config['show_champion_tip'])
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_CHAMPION_TIP'); ?></th><?php
					}
					if ($this->config['show_final4_tip'])
					{
						?>
                        <th colspan="4" style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_FINAL4_TIP'); ?></th><?php
					}

					if ($this->config['show_tip_details'])
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_DETAILS'); ?></th><?php
					}
					?>
                    <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_POINTS'); ?></th>
					<?php
					if ($this->config['show_average_points'])
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_AVERAGE'); ?></th><?php
					}
					?>
					<?php
					if ($this->config['show_count_tips'])
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_PREDICTIONS'); ?></th><?php
					}
					?>
					<?php
					if ($this->config['show_count_joker'])
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_JOKERS'); ?></th><?php
					}
					?>
					<?php
					if ($this->config['show_count_topptips'])
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_TOPS'); ?></th><?php
					}
					?>
					<?php
					if ($this->config['show_count_difftips'])
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_MARGINS'); ?></th><?php
					}
					?>
					<?php
					if ($this->config['show_count_tendtipps'])
					{
						?>
                        <th style='text-align:center; vertical-align:top; '><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_TENDENCIES'); ?></th><?php
					}
					?>
                </tr>
                </thead>
				<?php

				$k          = 0;
				$memberList = sportsmanagementModelPrediction::getPredictionMembersList($this->config, $this->configavatar);

				$membersResultsArray = array();
				$membersDataArray    = array();

				if (sportsmanagementModelPrediction::$pggrouprank)
				{
					$groupmembersResultsArray = array();
					$groupmembersDataArray    = array();
				}

				// anfang der tippmitglieder
				foreach ($memberList AS $member)
				{
					$memberPredictionPoints = sportsmanagementModelPrediction::getPredictionMembersResultsList(
						$showProjectID,
						sportsmanagementModelPrediction::$from,
						sportsmanagementModelPrediction::$to,
						$member->user_id,
						sportsmanagementModelPrediction::$type
					);

					$predictionsCount = 0;
					$totalPoints      = 0;
					$ChampPoints      = 0;
					$totalTop         = 0;
					$totalDiff        = 0;
					$totalTend        = 0;
					$totalJoker       = 0;

					if (!empty($memberPredictionPoints))
					{
						foreach ($memberPredictionPoints AS $memberPredictionPoint)
						{
							if ((!is_null($memberPredictionPoint->homeResult))
								|| (!is_null($memberPredictionPoint->awayResult))
								|| (!is_null($memberPredictionPoint->homeDecision))
								|| (!is_null($memberPredictionPoint->awayDecision))
							)
							{
								$predictionsCount++;

								switch ($this->config['use_match_result'])
								{
									case 0:
										// normale spielzeit wird benutzt
										// wenn aber die verlängerung oder das elfmeterergebnis eingetragen wurde,
										// dann den endstand der regulaeren spielzeit nehmen.
										if (!is_null($memberPredictionPoint->homeResultOT) || !is_null($memberPredictionPoint->awayResultOT)
											|| !is_null($memberPredictionPoint->homeResultSO) || !is_null($memberPredictionPoint->awayResultSO)
										)
										{
											$partresults1                      = explode(";", $memberPredictionPoint->homeResultSplit);
											$partresults2                      = explode(";", $memberPredictionPoint->awayResultSplit);
											$memberPredictionPoint->homeResult = array_pop($partresults1);;
											$memberPredictionPoint->awayResult = array_pop($partresults2);;
										}


										break;
									case 1:
										// verlaengerung
										if (!is_null($memberPredictionPoint->homeResultOT) || !is_null($memberPredictionPoint->awayResultOT))
										{
											$memberPredictionPoint->homeResult = $memberPredictionPoint->homeResultOT;
											$memberPredictionPoint->awayResult = $memberPredictionPoint->awayResultOT;
										}
										break;
									case 2:
										// elfmeter
										if (!is_null($memberPredictionPoint->homeResultSO) || !is_null($memberPredictionPoint->awayResultSO))
										{
											$memberPredictionPoint->homeResult = $memberPredictionPoint->homeResultSO;
											$memberPredictionPoint->awayResult = $memberPredictionPoint->awayResultSO;
										}
										break;
								}

								$result = sportsmanagementModelPrediction::createResultsObject(
									$memberPredictionPoint->homeResult,
									$memberPredictionPoint->awayResult,
									$memberPredictionPoint->prTipp,
									$memberPredictionPoint->prHomeTipp,
									$memberPredictionPoint->prAwayTipp,
									$memberPredictionPoint->prJoker,
									$memberPredictionPoint->homeDecision,
									$memberPredictionPoint->awayDecision,
									$memberPredictionPoint->matchRoundId
								);
								// neue punkte berechnen
								$newPoints = sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);

								//if (!is_null($memberPredictionPoint->prPoints))
								{
									$points = $memberPredictionPoint->prPoints;
									if ($newPoints != $points)
									{
										// this check also should be done if the result is not displayed
										$memberPredictionPoint = sportsmanagementModelPrediction::savePredictionPoints(
											$newPoints,
											$memberPredictionPoint,
											$predictionProject,
											true
										);
										$points                = $newPoints;
									}
									$totalPoints = $totalPoints + $points;
								}
								if (!is_null($memberPredictionPoint->prJoker))
								{
									$totalJoker = $totalJoker + $memberPredictionPoint->prJoker;
								}
								if (!is_null($memberPredictionPoint->prTop))
								{
									$totalTop = $totalTop + $memberPredictionPoint->prTop;
								}
								if (!is_null($memberPredictionPoint->prDiff))
								{
									$totalDiff = $totalDiff + $memberPredictionPoint->prDiff;
								}
								if (!is_null($memberPredictionPoint->prTend))
								{
									$totalTend = $totalTend + $memberPredictionPoint->prTend;
								}
							}
						}
					}

					$ChampPoints = 0;
					$membersDataArray[$member->pmID]['champ_tipp']        = $member->champ_tipp;
					$membersDataArray[$member->pmID]['champ_tipp_result'] = sportsmanagementModelPrediction::getChampionPoints($member->champ_tipp);
					// if a champion was already set
					if ($membersDataArray[$member->pmID]['champ_tipp_result'] !== false) {
						// get the points for member
						$ChampPoints = $membersDataArray[$member->pmID]['champ_tipp_result'];
					}
					$Final4Points = 0;
					
					$sFinal4TeamsList = explode(';', $member->final4_tipp);
					$membersDataArray[$member->pmID]['final4_tipps'] = array();
					$p = 0;
					foreach ($sFinal4TeamsList AS $key => $value) {
						if ($value != "") {
							$final4_tip = array();
							$final4_tip['final4_tipp'] = explode(',', $value);
							$final4_tip['final4_tipp_result'] = sportsmanagementModelPrediction::getFinal4Points($value);
							if ($final4_tip['final4_tipp_result'] !== false) {
								$Final4Points += $final4_tip['final4_tipp_result'];
							}
							$membersDataArray[$member->pmID]['final4_tipps'][] = $final4_tip;
						}
					}

					$membersResultsArray[$member->pmID]['pg_group_name']    = $member->pg_group_name;
					$membersResultsArray[$member->pmID]['pg_group_id']      = $member->pg_group_id;
					$membersResultsArray[$member->pmID]['rank']             = 0;
					$membersResultsArray[$member->pmID]['predictionsCount'] = $predictionsCount;
					$membersResultsArray[$member->pmID]['totalPoints']      = $totalPoints + $ChampPoints + $Final4Points;
					$membersResultsArray[$member->pmID]['totalTop']         = $totalTop;
					$membersResultsArray[$member->pmID]['totalDiff']        = $totalDiff;
					$membersResultsArray[$member->pmID]['totalTend']        = $totalTend;
					$membersResultsArray[$member->pmID]['totalJoker']       = $totalJoker;
					$membersResultsArray[$member->pmID]['membernameAtoZ']   = $member->name;

					if (sportsmanagementModelPrediction::$pggrouprank)
					{
						if (!isset($groupmembersResultsArray[$member->pg_group_id]['predictionsCount']))
						{
							$groupmembersResultsArray[$member->pg_group_id]['predictionsCount'] = 0;
						}
						if (!isset($groupmembersResultsArray[$member->pg_group_id]['totalPoints']))
						{
							$groupmembersResultsArray[$member->pg_group_id]['totalPoints'] = 0;
						}
						if (!isset($groupmembersResultsArray[$member->pg_group_id]['totalTop']))
						{
							$groupmembersResultsArray[$member->pg_group_id]['totalTop'] = 0;
						}
						if (!isset($groupmembersResultsArray[$member->pg_group_id]['totalDiff']))
						{
							$groupmembersResultsArray[$member->pg_group_id]['totalDiff'] = 0;
						}
						if (!isset($groupmembersResultsArray[$member->pg_group_id]['totalTend']))
						{
							$groupmembersResultsArray[$member->pg_group_id]['totalTend'] = 0;
						}
						if (!isset($groupmembersResultsArray[$member->pg_group_id]['totalJoker']))
						{
							$groupmembersResultsArray[$member->pg_group_id]['totalJoker'] = 0;
						}
						// für die gruppentabelle
						$groupmembersResultsArray[$member->pg_group_id]['pg_group_id']      = $member->pg_group_id;
						$groupmembersResultsArray[$member->pg_group_id]['pg_group_name']    = $member->pg_group_name;
						$groupmembersResultsArray[$member->pg_group_id]['rank']             = 0;
						$groupmembersResultsArray[$member->pg_group_id]['predictionsCount'] += $predictionsCount;
						$groupmembersResultsArray[$member->pg_group_id]['totalPoints']      += $totalPoints + $ChampPoints;
						$groupmembersResultsArray[$member->pg_group_id]['totalTop']         += $totalTop;
						$groupmembersResultsArray[$member->pg_group_id]['totalDiff']        += $totalDiff;
						$groupmembersResultsArray[$member->pg_group_id]['totalTend']        += $totalTend;
						$groupmembersResultsArray[$member->pg_group_id]['totalJoker']       += $totalJoker;
					}

					// check all needed output for later
					$picture    = $member->avatar;
					$playerName = $member->name;

					if (((!isset($member->avatar))
						|| ($member->avatar == '')
						|| (!file_exists($member->avatar))
						|| ((!$member->show_profile) && ($this->predictionMember->pmID != $member->pmID)))
					)
					{
						$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
					}
					//$output = sportsmanagementHelper::getPictureThumb($picture, $playerName,0,25);
					$output                                            = HTMLHelper::image($picture, $playerName, array('title' => $playerName, 'width' => $this->config['show_user_icon_width']));
					$membersDataArray[$member->pmID]['show_user_icon'] = $output;
					$membersDataArray[$member->pmID]['pg_group_name']  = $member->pg_group_name;
					$membersDataArray[$member->pmID]['pg_group_id']    = $member->pg_group_id;

					if (sportsmanagementModelPrediction::$pggrouprank)
					{
						$groupmembersDataArray[$member->pg_group_id]['pg_group_name'] = $member->pg_group_name;
						$groupmembersDataArray[$member->pg_group_id]['pg_group_id']   = $member->pg_group_id;
					}

					if ($member->aliasName)
					{
						$member->name = $member->aliasName;
					}

					if (($this->config['link_name_to']) && (($member->show_profile) || ($this->predictionMember->pmID == $member->pmID)))
					{
						$link   = JSMPredictionHelperRoute::getPredictionMemberRoute(sportsmanagementModelPrediction::$predictionGameID, $member->pmID);
						$output = HTMLHelper::link($link, $member->name);
					}
					else
					{
						$output = $member->name;
					}

					$membersDataArray[$member->pmID]['name'] = $output;

					$imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_RANK_SHOW_DETAILS_OF', $member->name);
					$imgFile  = HTMLHelper::image("media/com_sportsmanagement/jl_images/zoom.png", $imgTitle, array(' title' => $imgTitle));
					// bugtracker id 0000088
					//$link = JSMPredictionHelperRoute::getPredictionResultsRoute($this->predictionGame->id ,$actualProjectCurrentRound ,$this->model->pjID,$member->pmID);
					$link = JSMPredictionHelperRoute::getPredictionResultsRoute(sportsmanagementModelPrediction::$predictionGameID, $actualProjectCurrentRound, sportsmanagementModelPrediction::$pjID);
					if (($member->show_profile) || ($this->predictionMember->pmID == $member->pmID))
					{
						$output = HTMLHelper::link($link, $imgFile);
					}
					else
					{
						$output = '&nbsp;';
					}

					$membersDataArray[$member->pmID]['show_tip_details'] = $output;

					if ((int) sportsmanagementModelPrediction::$pggrouprank)
					{
						$imgTitle                                                        = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_RANK_SHOW_DETAILS_OF', $member->pg_group_name);
						$imgFile                                                         = HTMLHelper::image("media/com_sportsmanagement/jl_images/zoom.png", $imgTitle, array(' title' => $imgTitle));
						$link                                                            = JSMPredictionHelperRoute::getPredictionResultsRoute(sportsmanagementModelPrediction::$predictionGameID, $actualProjectCurrentRound, sportsmanagementModelPrediction::$pjID, $member->pmID, '', $member->pg_group_id);
						$output                                                          = HTMLHelper::link($link, $imgFile);
						$groupmembersDataArray[$member->pg_group_id]['show_tip_details'] = $output;
					}


				}
				/**
				 *
				 * ende der tippmitglieder
				 */
				if (sportsmanagementModelPrediction::$pggrouprank)
				{
					$computedMembersRanking = sportsmanagementModelPrediction::computeMembersRanking($groupmembersResultsArray, $this->config);
				}
				else
				{
					$computedMembersRanking = sportsmanagementModelPrediction::computeMembersRanking($membersResultsArray, $this->config);
				}

				$recordCount = count($computedMembersRanking);

				$i = 1;

				if (sportsmanagementModelPrediction::$pggrouprank)
				{
					$schluessel          = 'pg_group_id';
					$membersDataArray    = $groupmembersDataArray;
					$membersResultsArray = $groupmembersResultsArray;
				}
				else
				{
					$schluessel = 'pmID';
				}


				?>
                <tbody>
				<?PHP
				/**
				 * schleife über die sortierte tabelle anfang
				 */
				$durchlauf = 1;
				foreach ($computedMembersRanking AS $key => $value)
				{
					if (in_array($durchlauf, range($this->ausgabestart, $this->ausgabeende)))
					{
						foreach ($this->items as $items)
						{
							if ($key == $items->$schluessel)
							{
								$styleStr   = ($this->predictionMember->pmID == $key) ? ' style="background-color:' . $this->config['background_color_ranking'] . '; color:black; " ' : '';
								$tdStyleStr = " style='text-align:center; vertical-align:middle; ' ";
								$class      = '';

								?>

                                <tr <?php echo $styleStr; ?> >
                                    <td<?php echo $tdStyleStr; ?>><?php echo $value['rank']; ?></td>
									<?php
									if (sportsmanagementModelPrediction::$pggrouprank)
									{
										?>
                                        <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['pg_group_name']; ?></td>
                                        <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['show_tip_details']; ?></td>
										<?php
									}
									else
									{
										if ($this->config['show_user_icon'])
										{
											?>
                                            <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['show_user_icon']; ?></td>
											<?php
										}
										?>
                                        <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['name']; ?></td>
										<?php
										if ($this->config['show_pred_group'])
										{
											?>
                                            <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['pg_group_name']; ?></td>
											<?php
										}
									}
									// soll der meistertipp angezeigt werden ? anfang
									if ($this->config['show_champion_tip'])
									{
										if (isset($membersDataArray[$key]['champ_tipp']))
										{
											if ($this->config['show_champion_tip_club_logo'])
											{
												if ($showProjectID)
												{
													$champLogo = $this->model->getChampLogo($showProjectID, $membersDataArray[$key]['champ_tipp']);

													if (isset($champLogo->name))
													{
														$imgTitle           = $champLogo->name;
														$champion_logo_size = $this->config['champion_logo_size'];
														$imgFile            = sportsmanagementHelperHtml::getBootstrapModalImage('predranking' . $key, $champLogo->$champion_logo_size, $imgTitle, '20');
													}
													else
													{
														$imgFile = '';
													}

												}
												else
												{
													$imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_CHAMPION_TIP');
													$imgFile  = HTMLHelper::image("media/com_sportsmanagement/event_icons/goal2.png", $imgTitle, array('title' => $imgTitle));
												}


											}
											else
											{
												$imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_CHAMPION_TIP');
												$imgFile  = HTMLHelper::image("media/com_sportsmanagement/event_icons/goal2.png", $imgTitle, array('title' => $imgTitle));
											}
											?>
                                            <td <?php echo $tdStyleStr; ?> >
												<?PHP
												echo $imgFile;
                                                if ($this->config['show_champion_tip_result'] && ($membersDataArray[$key]['champ_tipp_result'] !== false)) {
													echo "<sub style='color:#7D0204;font-weight:bold;'>".$membersDataArray[$key]['champ_tipp_result']."</sub>";
                                                }
												?>
                                            </td>
											<?PHP
										}
										else
										{
											?>
                                            <td>
                                            </td>
											<?PHP
										}
									}
									// soll der meistertipp angezeigt werden ? ende
									if ($this->config['show_final4_tip'])
									{
										foreach(range(0, 3) as $t) {
											if (isset($membersDataArray[$key]['final4_tipps'][$t])) {
												$final4_tip = $membersDataArray[$key]['final4_tipps'][$t]; ?>
												<td <?php echo $tdStyleStr; ?>><span style="display:inline;"><?php
                                                if ($this->config['show_final4_tip_club_logo']) {
                                                    if ($showProjectID) {
                                                        if (isset($final4_tip['final4_tipp'][1])) {
                                                            $final4Logo = sportsmanagementModelProject::getTeaminfo($final4_tip['final4_tipp'][1], 0);
                                                        }
                                                        if (isset($final4Logo->name)) {
                                                            $imgTitle           = $final4Logo->name;
                                                            $final4_logo_size   = $this->config['final4_logo_size'];
                                                            $imgFile            = sportsmanagementHelperHtml::getBootstrapModalImage('predranking' . $key, $final4Logo->$final4_logo_size, $imgTitle, '20');
                                                        } else {
                                                            $imgFile = Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOT_AVAILABLE');;
                                                        }
                                                    } else {
                                                        $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_FINAL4_TIP');
                                                        $imgFile  = HTMLHelper::image("media/com_sportsmanagement/event_icons/goal2.png", $imgTitle, array('title' => $imgTitle));
                                                    }
                                                } else {
                                                    $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_FINAL4_TIP');
                                                    $imgFile  = HTMLHelper::image("media/com_sportsmanagement/event_icons/goal2.png", $imgTitle, array('title' => $imgTitle));
                                                } 
                                                echo $imgFile;
                                                if ($this->config['show_final4_tip_result'] && ($final4_tip['final4_tipp_result'] !== false)) {
                                                    echo "<sub style='color:#7D0204;font-weight:bold;'>".$final4_tip['final4_tipp_result']."</sub>";
												}?>
												</span></td>
												<?php
                                            }
											else
											{
												?>
												<td>
												</td>
												<?PHP
											}
										}

									}
									if (!sportsmanagementModelPrediction::$pggrouprank)
									{
										if ($this->config['show_tip_details'])
										{
											?>
                                            <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['show_tip_details']; ?></td><?php
										}
									}
									?>
                                    <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalPoints']; ?></td>
									<?php
									if ($this->config['show_average_points'])
									{
										?>
                                        <td<?php echo $tdStyleStr; ?>><?php
										if ($membersResultsArray[$key]['predictionsCount'] > 0)
										{
											echo number_format(round($membersResultsArray[$key]['totalPoints'] / $membersResultsArray[$key]['predictionsCount'], 2), 2);
										}
										else
										{
											echo number_format(0, 2);
										}
										?></td><?php
									}
									?>
									<?php
									if ($this->config['show_count_tips'])
									{
										?>
                                        <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['predictionsCount']; ?></td><?php
									}
									?>
									<?php
									if ($this->config['show_count_joker'])
									{
										?>
                                        <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalJoker']; ?></td><?php
									}
									?>
									<?php
									if ($this->config['show_count_topptips'])
									{
										?>
                                        <td style='text-align:center; vertical-align:middle; '><?php echo $membersResultsArray[$key]['totalTop']; ?></td><?php
									}
									?>
									<?php
									if ($this->config['show_count_difftips'])
									{
										?>
                                        <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalDiff']; ?></td><?php
									}
									?>
									<?php
									if ($this->config['show_count_tendtipps'])
									{
										?>
                                        <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalTend']; ?></td><?php
									}
									?>
                                </tr>
								<?php
								//}
								$k = (1 - $k);
								$i++;
							}
						}
					}
					$durchlauf++;
				}
				/**
				 * schleife über die sortierte tabelle ende
				 */
				?>
                </tbody>
            </table>
        </div>
		<?php
	}
}
?>
