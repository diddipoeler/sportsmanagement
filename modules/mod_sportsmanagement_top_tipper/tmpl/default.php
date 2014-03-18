<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die(JText::_('Restricted access'));
JHTML::_('behavior.tooltip');
//echo '<br /><pre>~' . print_r($config,true) . '~</pre><br />';
//echo '<br /><pre>~' . print_r($config['limit'],true) . '~</pre><br />';
?>
<a name='jl_top' id='jl_top'></a>
<?php
foreach ($modelpg->_predictionProjectS AS $predictionProject)
{
	$gotSettings = $predictionProjectSettings = $modelpg->getPredictionProject($predictionProject->project_id);
	if ((($modelpg->pjID == $predictionProject->project_id) && ($gotSettings)) || ($modelpg->pjID==0))
	{
		$showProjectID = (count($modelpg->_predictionProjectS) > 1) ? $modelpg->pjID : $predictionProject->project_id;
		$modelpg->pjID = $predictionProject->project_id;
		$modelpg->predictionProject = $predictionProject;
		$actualProjectCurrentRound = $modelpg->getProjectSettings($predictionProject->project_id);
		/*
		if (!isset($this->roundID) || ($this->roundID < 1)){
			$this->roundID=$actualProjectCurrentRound;
		}
		if ($this->roundID < 1){$this->roundID=1;}
		if ($modelpg->from < 1){
			$modelpg->from=1;
		}
		if ($this->roundID > $modelpg->getProjectRounds($predictionProject->project_id)){
			$this->roundID=$modelpg->_projectRoundsCount;
		}
		if ($modelpg->to > $modelpg->_projectRoundsCount){
			$modelpg->to=$modelpg->_projectRoundsCount;
		}
		if ($modelpg->to == 0){
			$modelpg->to=$actualProjectCurrentRound;
		}
		*/
		?>
		<form name='resultsRoundSelector' method='post' >
			<input type='hidden' name='prediction_id' value='<?php echo (int)$predictionGame[0]->id; ?>' />
			<input type='hidden' name='p' value='<?php echo (int)$predictionProject->project_id; ?>' />
			<input type='hidden' name='r' value='<?php echo (int)$roundID; ?>' />
			<input type='hidden' name='pjID' value='<?php echo (int)$showProjectID; ?>' />
			<input type='hidden' name='task' value='selectProjectRound' />
			<input type='hidden' name='option' value='com_sportsmanagement' />
			<input type='hidden' name='controller' value='predictionranking' />

			<table class='blog' cellpadding='0' cellspacing='0' >
				<tr>
					<td class='sectiontableheader'>
						<?php
						//echo '<b>'.JText::sprintf('JL_PRED_RANK_SUBTITLE_01').'</b>';
						if ( $config['show_project_name'] )
		        {
						echo '<b>'.$predictionGame[0]->name.'</b>';
						}
						?>
					</td>
					<td class='sectiontableheader' style='text-align:right; ' width='20%' nowrap='nowrap' ><?php
						if ( $config['show_project_name_selector'] )
		        {
            echo $modelpg->createProjectSelector(	$modelpg->_predictionProjectS,
																	$predictionProject->project_id,
																	$showProjectID);
		        }
            				
            if ($showProjectID > 0)
						{
if ( $config['show_tip_link_ranking_round'] )
		        {
							echo '&nbsp;&nbsp;';
							$link = sportsmanagementHelperRoute::getResultsRoute($predictionProject->project_id,$roundID);
							$imgTitle=JText::_('JL_PRED_ROUND_RESULTS_TITLE');
							$desc = JHTML::image('media/com_sportsmanagement/jl_images/icon-16-Matchdays.png',$imgTitle,array('border' => 0,'title' => $imgTitle));
							//echo JHTML::link($link,$desc,array('target' => '_blank'));
							echo JHTML::link($link,$desc,array('target' => ''));
				}		
            if ( $config['show_tip_ranking'] )
		        {
		        echo '&nbsp;&nbsp;';
            
            if ( !$config['show_tip_ranking_round'] )
		        {
            $link = PredictionHelperRoute::getPredictionRankingRoute($predictionGame[0]->id , $modelpg->pjID , '' );
            }
            else
            {
            $link = PredictionHelperRoute::getPredictionRankingRoute($predictionGame[0]->id , $modelpg->pjID , $actualProjectCurrentRound );
            }
            
		        
		        
            
            $imgTitle=JText::_('JL_PRED_HEAD_RANKING_IMAGE_TITLE');
						$desc = JHTML::image('media/com_sportsmanagement/jl_images/prediction_ranking.png',$imgTitle,array('border' => 0,'title' => $imgTitle));
						echo JHTML::link($link,$desc,array('target' => ''));
		        }
            
            }
						?></td>
				</tr>
			</table><br />
			<?php echo JHTML::_( 'form.token' ); ?>
		</form>
		
    <?php
		if (($showProjectID > 0) && ($config['show_rankingnav']))
		{
			$from_matchday = $modelpg->createFromMatchdayList($predictionProject->project_id);
			$to_matchday = $modelpg->createToMatchdayList($predictionProject->project_id);
			?>
			<form name='adminForm' id='adminForm' method='post'>
				<table>
					<tr>
						<td><?php echo JHTML::_('select.genericlist',$lists['type'],'type','class="inputbox" size="1"','value','text',$modelpg->type); ?></td>
						<td><?php echo JHTML::_('select.genericlist',$from_matchday,'from','class="inputbox" size="1"','value','text',$modelpg->from); ?></td>
						<td><?php echo JHTML::_('select.genericlist',$to_matchday,'to','class="inputbox" size="1"','value','text',$modelpg->to); ?></td>
						<td><input type='submit' class='button' name='reload View' value='<?php echo JText::_('JL_PRED_RANK_FILTER'); ?>' /></td>
					</tr>
				</table>
				<?php echo JHTML::_( 'form.token' ); ?>
			</form><br />
			<?php
		}
		?>
		
		<table width='100%' cellpadding='0' cellspacing='0'>
			<tr>
				<td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_RANK'); ?></td>
				<?php
				if ($config['show_user_icon'])
				{
					?><td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_AVATAR'); ?></td><?php
				}
				?>
				<td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_MEMBER'); ?></td>
				<?php
				if ($config['show_tip_details'])
				{
					?><td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_RANK_DETAILS'); ?></td><?php
				}
				?>

        <?php
        
				?>				
				
				<td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_POINTS'); ?></td>
				<?php
				if ($config['show_average_points'])
				{
					?><td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_AVERAGE'); ?></td><?php
				}
				?>
				<?php
				if ($config['show_count_tips'])
				{
					?><td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_RANK_PREDICTIONS'); ?></td><?php
				}
				?>
				<?php
				if ($config['show_count_joker'])
				{
					?><td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_RANK_JOKERS'); ?></td><?php
				}
				?>
				<?php
				if ($config['show_count_topptips'])
				{
					?><td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_RANK_TOPS'); ?></td><?php
				}
				?>
				<?php
				if ($config['show_count_difftips'])
				{
					?><td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_RANK_MARGINS'); ?></td><?php
				}
				?>
				<?php
				if ($config['show_count_tendtipps'])
				{
					?><td class='sectiontableheader' style='text-align:center; vertical-align:top; '><?php echo JText::_('JL_PRED_RANK_TENDENCIES'); ?></td><?php
				}
				?>
			</tr>
			<?php
				if ($config['show_debug_modus'])
        {
        echo 'mod_sportsmanagement_top_tipper - predictionMember<br /><pre>~' . print_r($predictionMember,true) . '~</pre><br />';
        }
        
				$k = 0;
				$memberList = $modelpg->getPredictionMembersList($config,$configavatar);
				// echo '<br /><pre>~' . print_r($memberList,true) . '~</pre><br />';
				
				$membersResultsArray = array();
				$membersDataArray = array();

				foreach ($memberList AS $member)
				{

					//echo '<br /><pre>~' . print_r($modelpg->page,true) . '~</pre><br />';
					$memberPredictionPoints = $modelpg->getPredictionMembersResultsList(	$showProjectID,
																								$modelpg->from,
																								$modelpg->to,
																								$member->user_id,
																								$modelpg->type);
					//echo '<br /><pre>~' . print_r($memberPredictionPoints,true) . '~</pre><br />';
					$predictionsCount=0;
					$totalPoints=0;
					$totalTop=0;
					$totalDiff=0;
					$totalTend=0;
					$totalJoker=0;
					if (!empty($memberPredictionPoints))
					{
						foreach ($memberPredictionPoints AS $memberPredictionPoint)
						{
							if ((!is_null($memberPredictionPoint->homeResult)) ||
								(!is_null($memberPredictionPoint->awayResult)) ||
								(!is_null($memberPredictionPoint->homeDecision)) ||
								(!is_null($memberPredictionPoint->awayDecision)))
							{
								$predictionsCount++;
								$result = $modelpg->createResultsObject(	$memberPredictionPoint->homeResult,
																				$memberPredictionPoint->awayResult,
																				$memberPredictionPoint->prTipp,
																				$memberPredictionPoint->prHomeTipp,
																				$memberPredictionPoint->prAwayTipp,
																				$memberPredictionPoint->prJoker,
																				$memberPredictionPoint->homeDecision,
																				$memberPredictionPoint->awayDecision);
								$newPoints = $modelpg->getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
								//if (!is_null($memberPredictionPoint->prPoints))
								{
									$points=$memberPredictionPoint->prPoints;
									if ($newPoints!=$points)
									{
										// this check also should be done if the result is not displayed
										$memberPredictionPoint=$modelpg->savePredictionPoints(	$memberPredictionPoint,
																									$predictionProject,
																									true);
										$points=$newPoints;
									}
									$totalPoints=$totalPoints+$points;
								}
								if (!is_null($memberPredictionPoint->prJoker)){$totalJoker=$totalJoker+$memberPredictionPoint->prJoker;}
								if (!is_null($memberPredictionPoint->prTop)){$totalTop=$totalTop+$memberPredictionPoint->prTop;}
								if (!is_null($memberPredictionPoint->prDiff)){$totalDiff=$totalDiff+$memberPredictionPoint->prDiff;}
								if (!is_null($memberPredictionPoint->prTend)){$totalTend=$totalTend+$memberPredictionPoint->prTend;}
							}
						}
					}

					$membersResultsArray[$member->pmID]['rank']				= 0;
					$membersResultsArray[$member->pmID]['predictionsCount']	= $predictionsCount;
					$membersResultsArray[$member->pmID]['totalPoints']		= $totalPoints;
					$membersResultsArray[$member->pmID]['totalTop']			= $totalTop;
					$membersResultsArray[$member->pmID]['totalDiff']		= $totalDiff;
					$membersResultsArray[$member->pmID]['totalTend']		= $totalTend;
					$membersResultsArray[$member->pmID]['totalJoker']		= $totalJoker;

					// check all needed output for later
					$picture = $member->avatar;
					$playerName = $member->name;					
					
					if (((!isset($member->avatar)) ||
						($member->avatar=='') ||
						(!file_exists($member->avatar)) ||
						((!$member->show_profile) && ($predictionMember[0]->pmID != $member->pmID))))
					{
						$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
					}
					//tobe removed
					//$imgTitle = JText::sprintf('JL_PRED_AVATAR_OF',$member->name);
					//$output = JHTML::image($member->avatar,$imgTitle,array(' width' => 20, ' title' => $imgTitle));
					
					$output = sportsmanagementHelper::getPictureThumb($picture, $playerName,0,25);
					$membersDataArray[$member->pmID]['show_user_icon'] = $output;

					if (($config['show_user_link'])&&(($member->show_profile)||($predictionMember[0]->pmID == $member->pmID)))
					{
						$link = PredictionHelperRoute::getPredictionMemberRoute($predictionGame[0]->id,$member->pmID);
						$output = JHTML::link($link,$member->name);
					}
					else
					{
						$output = $member->name;
					}
					$membersDataArray[$member->pmID]['name'] = $output;
					
					$imgTitle = JText::sprintf('JL_PRED_RANK_SHOW_DETAILS_OF',$member->name);
					$imgFile = JHTML::image( "media/com_sportsmanagement/jl_images/zoom.png", $imgTitle , array(' title' => $imgTitle));
					$link = PredictionHelperRoute::getPredictionResultsRoute($predictionGame[0]->id ,$actualProjectCurrentRound ,$modelpg->pjID);
					if (($member->show_profile)||($predictionMember[0]->pmID == $member->pmID))
					{
						$output = JHTML::link( $link, $imgFile);
					}
					else
					{
						$output = '&nbsp;';
					}

					$membersDataArray[$member->pmID]['show_tip_details']	= $output;
					
					
					
					
				}

				//echo '<br /><pre>~' . print_r($membersResultsArray,true) . '~</pre><br />';
				//echo '<br /><pre>~' . print_r($membersDataArray,true) . '~</pre><br />';
				//$membersResultsArray2=$membersResultsArray;
				//$membersResultsArray3=$membersResultsArray;
				//$membersResultsArray4=$membersResultsArray;
				//$membersResultsArray5=$membersResultsArray;
				//$membersResultsArray6=$membersResultsArray;

				/*
				$membersResultsArray = array_merge(	$membersResultsArray,
													$membersResultsArray2,
													$membersResultsArray3,
													$membersResultsArray4,
													$membersResultsArray5,
													$membersResultsArray6
													);
				*/
				$computedMembersRanking=$modelpg->computeMembersRanking($membersResultsArray,$config);
				$recordCount=count($computedMembersRanking);
				//echo '<br /><pre>~' . print_r($computedMembersRanking,true) . '~</pre><br />';

				$i=1;
				if ((int)$config['limit'] < 1){$config['limit']=1;}
				$rlimit=ceil($recordCount / $config['limit']);
				$modelpg->page=($modelpg->page > $rlimit) ? $rlimit : $modelpg->page;
				$skipMemberCount=($modelpg->page > 0) ? (($modelpg->page-1)*$config['limit']) : 0;

				foreach ($computedMembersRanking AS $key => $value)
				{
					//echo '<br /><pre>~' . print_r($value,true) . '~</pre><br />';
					if ($i <= $skipMemberCount) { $i++; continue; }

					$class = ($k==0) ? 'sectiontableentry1' : 'sectiontableentry2';
					$styleStr = ($predictionMember[0]->pmID==$key) ? ' style="background-color:yellow; color:black; " ' : '';
					$class = ($predictionMember[0]->pmID==$key) ? 'sectiontableentry1' : $class;
					$tdStyleStr = " style='text-align:center; vertical-align:middle; ' ";

					//$config['show_all_user']=1;
					if (((!$config['show_all_user']) && ($value['predictionsCount'] > 0)) ||
						($config['show_all_user']) ||
						($predictionMember[0]->pmID == $key))
					{
						?>
						<tr class='<?php echo $class; ?>' <?php echo $styleStr; ?> >
							<td<?php echo $tdStyleStr; ?>><?php echo $value['rank']; ?></td>
							<?php
							if ($config['show_user_icon'])
							{
								?>
								<td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['show_user_icon']; ?></td>
								<?php
							}
							?>
							<td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['name']; ?></td>
							<?php
							if ($config['show_tip_details'])
							{
								?><td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['show_tip_details']; ?></td><?php
							}
							
							
							
							?>
							<td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalPoints']; ?></td>
							<?php
							if ($config['show_average_points'])
							{
								?><td<?php echo $tdStyleStr; ?>><?php
								if ($membersResultsArray[$key]['predictionsCount'] > 0)
								{
									echo number_format(round($membersResultsArray[$key]['totalPoints']/$membersResultsArray[$key]['predictionsCount'],2),2);
								}
								else
								{
									echo number_format(0,2);
								}
								?></td><?php
							}
							?>
							<?php
							if ($config['show_count_tips'])
							{
								?><td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['predictionsCount']; ?></td><?php
							}
							?>
							<?php
							if ($config['show_count_joker'])
							{
								?><td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalJoker']; ?></td><?php
							}
							?>
							<?php
							if ($config['show_count_topptips'])
							{
								?><td style='text-align:center; vertical-align:middle; '><?php echo $membersResultsArray[$key]['totalTop']; ?></td><?php
							}
							?>
							<?php
							if ($config['show_count_difftips'])
							{
								?><td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalDiff']; ?></td><?php
							}
							?>
							<?php
							if ($config['show_count_tendtipps'])
							{
								?><td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalTend']; ?></td><?php
							}
							?>
						</tr>
						<?php
						$k = (1-$k);
						$i++;
						if ($i > $skipMemberCount+$config['limit']){break;}
					}
				}
			?> 
		</table>
		<?php
	}
}
?>