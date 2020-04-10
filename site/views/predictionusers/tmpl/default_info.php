<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionusers
 * @file       default_info.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_PERS_DATA'); ?></h2>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="info">
	<?php
	if ($this->config['show_full_name'])
	{
		$outputUserName = $this->predictionMember->name;
	}
	else
	{
		$outputUserName = $this->predictionMember->username;
	}
	if (sportsmanagementModelPrediction::$pjID > 0)
	{
		$showProjectID = sportsmanagementModelPrediction::$pjID;
	}
	else
	{
		$showProjectID = null;
	}

	$memberPredictionPoints = sportsmanagementModelPrediction::getPredictionMembersResultsList($showProjectID, 1, null, $this->predictionMember->user_id);

	$predictionsCount = 0;
	$totalPoints      = 0;
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
				if (!is_null($memberPredictionPoint->prPoints))
				{
					$totalPoints = $totalPoints + $memberPredictionPoint->prPoints;
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

	?>
    <table class='table'>
        <tr>
            <td class='picture'>
				<?php
				// das userbild
				sportsmanagementModelPredictionUsers::showMemberPicture($outputUserName, $this->predictionMember->user_id);
				?>
            </td>
            <td class='info'>
                <table class='table'>
                    <tr>
                        <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NAME'); ?></td>
                        <td class='data'>
							<?php
							$outputName = Text::sprintf('%1$s %2$s', $outputUserName, '');
							if ($this->predictionMember->user_id)
							{
								switch ($this->config['show_user_profile'])
								{
									case 1:     // Link to Joomla Contact Page
										$link       = sportsmanagementHelperRoute::getContactRoute($this->predictionMember->user_id);
										$outputName = HTMLHelper::link($link, $outputName);
										break;

									case 2:     // Link to CBE User Page with support for SportsManagement Tab
										$link       = sportsmanagementHelperRoute::getUserProfileRouteCBE(
											$this->predictionMember->user_id,
											$this->predictionGame->id,
											$this->predictionMember->pmID
										);
										$outputName = HTMLHelper::link($link, $outputName);
										break;

									default:
										break;
								}
							}
							echo $outputName;
							?>
                        </td>
                    </tr>
					<?php
					if ($this->config['show_register_date'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_MEMBERSHIP'); ?></td>
                            <td class='data'>
								<?php
								echo($this->predictionMember->pmRegisterDate != '0000-00-00 00:00:00' ?
									HTMLHelper::date($this->predictionMember->pmRegisterDate, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')) :
									Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_UNKNOWN'));
								?>
                            </td>
                        </tr>
						<?php
					}
					?>
					<?php
					if ($this->config['show_slogan'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_SLOGAN'); ?></td>
                            <td class='data'><?php
								echo (!empty($this->predictionMember->slogan)) ? strip_tags($this->predictionMember->slogan) : Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NO_SLOGAN')
								?></td>
                        </tr>
						<?php
					}
					?>
					<?php
					if ($this->config['show_lasttip'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_LAST_PRED'); ?></td>
                            <td class='data'>
								<?php
								echo (!empty($this->predictionMember->last_tipp) && ($this->predictionMember->last_tipp != '0000-00-00 00:00:00')) ?
									HTMLHelper::date($this->predictionMember->last_tipp, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')) : Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NEVER');
								?>
                            </td>
                        </tr>
						<?php
					}
					?>
					<?php
					if ($this->config['show_fav_team'])
					{
						$found = false;
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_FAVTEAMS'); ?></td>
                            <td class='data'><?php
								foreach ($this->predictionProjectS AS $predictionProject)
								{
									if ((sportsmanagementModelPrediction::$pjID == 0) || (sportsmanagementModelPrediction::$pjID == $predictionProject->project_id))
									{
										if ($predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id))
										{
											if ($res = $this->model->getPredictionProjectTeams($predictionProject->project_id))
											{
												foreach ($res AS $team)
												{
													foreach ($this->favTeams AS $key => $value)
													{
														if ($team->value == $value)
														{
															$found = true;
															?>
                                                            <span class='hasTip' title="<?php
															echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_FAVTEAM_IN_PROJECT', $predictionProjectSettings->name);
															?>"><?php
																echo $team->text . '<br />';
																?></span>
															<?php
															break;
														}
													}
												}
											}
										}
									}
								}
								if (!$found)
								{
									echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NO_FAVTEAM');
								}
								?></td>
                        </tr>
						<?php
					}
					?>

                    <tr>
                        <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_CHAMPIONS'); /*Meistertipp*/ ?></td>
                        <td class='data'><?php
							$found = false;
							if (!isset($this->predictionMember->champ_tipp))
							{
								$this->predictionMember->champ_tipp = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NO_CHAMP');
							}

							$champShown       = false;
							$dummyOutputShown = false;
							foreach ($this->predictionProjectS AS $predictionProject)
							{
								if ((sportsmanagementModelPrediction::$pjID == 0) || (sportsmanagementModelPrediction::$pjID == $predictionProject->project_id))
								{
									if ($predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id))
									{
										//$predictionProjectSettings->start_date='2010-08-08';
										//$time=time();
										$time = strtotime($predictionProjectSettings->start_date);
										//$date=date("Y-m-d",$time);
										//echo $date.'/';
										$time     += 86400; // Ein Tag in Sekunden
										$showDate = date("Y-m-d", $time);
										//echo $showDate;
										$thisTimeDate             = sportsmanagementHelper::getTimestamp(date("Y-m-d H:i:s"), 1, $predictionProjectSettings->timezone);
										$competitionStartTimeDate = sportsmanagementHelper::getTimestamp($showDate, 1, $predictionProjectSettings->timezone);
										$showChamp                = ($thisTimeDate > $competitionStartTimeDate);
										//if (($showChamp) || ($this->showediticon))

										if (($showChamp))
										{
											if ($res = $this->model->getPredictionProjectTeams($predictionProject->project_id))
											{
												foreach ($res AS $team)
												{
													foreach ($this->champTeams AS $key => $value)
													{
														if ($team->value == $value)
														{
															$found      = true;
															$champShown = true;
															?>
                                                            <span class='hasTip' title="<?php
															echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_CHAMPION_IN_PROJECT', $predictionProjectSettings->name);
															?>"><?php
																echo $team->text . '<br />';
																?></span>
															<?php
															break;
														}
													}
												}
											}
										}
										else
										{
											if (!$dummyOutputShown)
											{
												echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_SHOW_AFTER_START') . '<br />';
											}
											$dummyOutputShown = true;
										}
									}
								}
							}
							if ((!$found) && ($champShown))
							{
								echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_NO_CHAMP');
							}
							?></td>
                    </tr>
                </table>
            </td>
            <td class='info'>
                <table class='plinfo'>
					<?php
					if (count(sportsmanagementModelPrediction::$_predictionProjectS) > 1)
					{
						?>
                        <tr>
                            <td class='data' style='text-align:center; ' colspan='2'>
                                <form name='resultsRoundSelector' method='post'>
                                    <input type='hidden' name='prediction_id'
                                           value='<?php echo (int) $this->predictionGame->id; ?>'/>
                                    <input type='hidden' name='project_id'
                                           value='<?php echo (int) $this->model->pjID; ?>'/>
                                    <input type='hidden' name='uid'
                                           value='<?php echo (int) $this->predictionMember->pmID; ?>'/>
                                    <input type='hidden' name='pjID' value='<?php echo (int) $this->model->pjID; ?>'/>
                                    <input type='hidden' name='task' value='predictionusers.selectprojectround'/>
                                    <input type='hidden' name='option' value='com_sportsmanagement'/>

									<?php echo HTMLHelper::_('form.token'); ?>

									<?php echo sportsmanagementModelPrediction::createProjectSelector(
										sportsmanagementModelPrediction::$_predictionProjectS,
										sportsmanagementModelPrediction::$pjID,
										1
									); ?>
                                </form>
                            </td>
                        </tr>
						<?php
					}
					?>
					<?php

					?>
					<?php
					if ($this->config['show_totalpoints'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_TOTAL_POINTS'); ?></td>
                            <td class='data'><?php
								// Add Link to totalranking
								echo $totalPoints;
								?></td>
                        </tr>
						<?php
					}
					?>
					<?php

					?>
					<?php
					if ($this->config['show_counttipps'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_PRED_COUNT'); ?></td>
                            <td class='data'><?php echo $predictionsCount; ?></td>
                        </tr>
						<?php
					}
					?>
					<?php
					if ($this->config['show_averagepoints'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_AVERAGE_POINTS'); ?></td>
                            <td class='data'><?php
								if ($predictionsCount > 0)
								{
									echo number_format(round($totalPoints / $predictionsCount, 2), 2);
								}
								else
								{
									echo number_format(0, 2);
								}
								?></td>
                        </tr>
						<?php
					}
					?>
					<?php
					if ($this->config['show_toptipps'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_TOPS'); ?></td>
                            <td class='data'><?php
								if ($predictionsCount > 0)
								{
									$percent = round($totalTop * 100 / $predictionsCount, 2);
								}
								else
								{
									$percent = number_format(0, 2);
								}
								echo Text::sprintf('%1$s (%2$s%%)', $totalTop, $percent);
								?></td>
                        </tr>
						<?php
					}
					?>
					<?php
					if ($this->config['show_difftipps'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_MARGINS'); ?></td>
                            <td class='data'><?php
								if ($predictionsCount > 0)
								{
									$percent = round($totalDiff * 100 / $predictionsCount, 2);
								}
								else
								{
									$percent = number_format(0, 2);
								}
								echo Text::sprintf('%1$s (%2$s%%)', $totalDiff, $percent);
								?></td>
                        </tr>
						<?php
					}
					?>
					<?php
					if ($this->config['show_tendtipps'])
					{
						?>
                        <tr>
                            <td class='label'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_TENDENCIES'); ?></td>
                            <td class='data'><?php
								if ($predictionsCount > 0)
								{
									$percent = round($totalTend * 100 / $predictionsCount, 2);
								}
								else
								{
									$percent = number_format(0, 2);
								}
								echo Text::sprintf('%1$s (%2$s%%)', $totalTend, $percent);
								?></td>
                        </tr>
						<?php
					}
					?>
					<?php

					?>
                </table>
            </td>
        </tr>
    </table>
</div>
