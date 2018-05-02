<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_results.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionresults
 */

defined('_JEXEC') or die(JText::_('Restricted access'));
JHTML::_('behavior.tooltip');

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'this->config<br /><pre>~' . print_r($this->config,true) . '~</pre><br />';
echo 'this->items<br /><pre>~' . print_r($this->items,true) . '~</pre><br />';
echo 'this->pagination<br /><pre>~' . print_r($this->pagination,true) . '~</pre><br />';
echo 'this->limit<br /><pre>~' . print_r($this->limit,true) . '~</pre><br />';
echo 'this->limitstart<br /><pre>~' . print_r($this->limitstart,true) . '~</pre><br />';
echo 'this->limitend<br /><pre>~' . print_r($this->limitend,true) . '~</pre><br />';
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

<!-- <a name='jl_top' id='jl_top'></a> -->
<?php
foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
{
	$gotSettings = $predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id);
	if ( ( ( (int)sportsmanagementModelPrediction::$pjID == $predictionProject->project_id ) && ($gotSettings)) || ( (int)sportsmanagementModelPrediction::$pjID == 0 ) )
	{
		sportsmanagementModelPrediction::$pjID = $predictionProject->project_id;
		$this->model->predictionProject = $predictionProject;
		$actualProjectCurrentRound = sportsmanagementModelPrediction::getProjectSettings($predictionProject->project_id);
		if (!isset(sportsmanagementModelPrediction::$roundID) || ( (int)sportsmanagementModelPrediction::$roundID < 1))
        {
            $this->roundID = $actualProjectCurrentRound;
        }
		if ( (int)sportsmanagementModelPrediction::$roundID < 1)
        {
            sportsmanagementModelPrediction::$roundID = 1;
        }
		if ( (int)sportsmanagementModelPrediction::$roundID > sportsmanagementModelPrediction::getProjectRounds($predictionProject->project_id))
        {
            sportsmanagementModelPrediction::$roundID = $this->model->_projectRoundsCount;
        }
        $this->roundID = sportsmanagementModelPrediction::$roundID;
		?>
		<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement'); ?>" method='post' name="adminForm" id="adminForm">
			<input type='hidden' name='option' value='com_sportsmanagement' />
			<input type='hidden' name='view' value='predictionresults' />
			<input type='hidden' name='prediction_id' value='<?php echo sportsmanagementModelPrediction::$predictionGameID; ?>' />
			<input type='hidden' name='project_id' value='<?php echo (int)$predictionProject->project_id; ?>' />
			<input type='hidden' name='cfg_which_database' value='<?php echo sportsmanagementModelPrediction::$cfg_which_database; ?>' />
      <input type='hidden' name='pj' value='<?php echo (int)$predictionProject->project_id; ?>' />
      <input type='hidden' name='p' value='<?php echo (int)$predictionProject->project_id; ?>' />
			<input type='hidden' name='r' value='<?php echo sportsmanagementModelPrediction::$roundID; ?>' />
			<input type='hidden' name='pjID' value='<?php echo sportsmanagementModelPrediction::$pjID; ?>' />
            <input type='hidden' name='pggroup' value='<?php echo sportsmanagementModelPrediction::$pggroup; ?>' />
            <input type='hidden' name='pggrouprank' value='<?php echo sportsmanagementModelPrediction::$pggrouprank; ?>' />
			<input type='hidden' name='task' value='predictionresults.selectprojectround' />
			
			<?php echo JHTML::_('form.token'); ?>
<!--
Responsive tables
Create responsive tables by adding .table-responsive to any .table to make them scroll horizontally on small devices (under 768px). 
When viewing on anything larger than 768px wide, you will not see any difference in these tables.
-->
<div class="table-responsive">
			<table class="table table-responsive" >
				<tr>
					<td >
						<?php
						echo '<b>'.JText::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_SUBTITLE_01').'</b>';
						?>
					</td>
					
                    <?php
                    $round_ids = '';
                    if ( $this->config['use_pred_select_rounds'] )
      {
      $round_ids = $this->config['predictionroundid'];
      }
                    
						$rounds = sportsmanagementHelper::getRoundsOptions($predictionProject->project_id,'ASC',FALSE,$round_ids);
                        
                        $groups = sportsmanagementModelPrediction::getPredictionGroupList();
						//$htmlRoundsOptions = JHTML::_('select.genericlist',$rounds,'current_round','class="inputbox" size="1" onchange="document.forms[\'resultsRoundSelector\'].r.value=this.value;submit()"','value','text',$this->roundID);
						$htmlRoundsOptions = JHTML::_('select.genericList',$rounds,'r','class="inputbox" onchange="this.form.submit(); "','value','text',sportsmanagementModelPrediction::$roundID);
                        
                        $predictionGroups[] = JHTML::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_PRED_SELECT_GROUPS'),'value','text');
                        $predictionGroups = array_merge($predictionGroups,$groups);
                        $htmlGroupOptions = JHTML::_('select.genericList',$predictionGroups,'pggroup','class="inputbox" onchange="this.form.submit(); "','value','text',sportsmanagementModelPrediction::$pggroup);
            
//echo __FILE__.' '.__LINE__.' project_id<br><pre>'.print_r($predictionProject->project_id,true).'</pre>';
            
            echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_SUBTITLE_02',
						'<td>'.$htmlRoundsOptions.'</td>',
						'<td>'.sportsmanagementModelPrediction::createProjectSelector(sportsmanagementModelPrediction::$_predictionProjectS,$predictionProject->project_id).'</td>',
                        '<td>'.$htmlGroupOptions.'</td>');
						?>
                    
					<td >
                        <?PHP
            echo '&nbsp;&nbsp;';
$routeparameter = array();
$routeparameter['cfg_which_database'] = sportsmanagementModelPrediction::$cfg_which_database;
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = sportsmanagementModelPrediction::$pjID;
$routeparameter['r'] = sportsmanagementModelPrediction::$roundID;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);            

						$imgTitle = JText::_('COM_SPORTSMANAGEMENT_PRED_ROUND_RESULTS_TITLE');
						$desc = JHTML::image('media/com_sportsmanagement/jl_images/icon-16-Matchdays.png',$imgTitle,array('border' => 0,'title' => $imgTitle));
						echo JHTML::link($link,$desc,array('target' => ''));
						?>
                        </td>
				</tr>

<tfoot>
<div class="pred_ranking">
<?php 
echo $this->pagination->getListFooter(); 
?>
</div>
</tfoot>  
                
			</table>
            </div>
            <br />
		</form>
<!--
Responsive tables
Create responsive tables by adding .table-responsive to any .table to make them scroll horizontally on small devices (under 768px). 
When viewing on anything larger than 768px wide, you will not see any difference in these tables.
-->
        <div class="table-responsive">        
		<table class="<?PHP echo $this->config['table_class']; ?> table-responsive">
        <thead>
			<tr>
				<?php 
                $tdClassStr = "style='text-align:center; vertical-align:middle; '"; 
                ?>
				<th <?php echo $tdClassStr; ?> ><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RANK'); ?></th>
				<?php
				
        if ($this->config['show_user_icon'])
				{
					?><th <?php echo $tdClassStr; ?> ><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_AVATAR'); ?></th><?php
				}
				
				?>
				<th <?php echo $tdClassStr; ?> ><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_MEMBER'); ?></th>
				<?php
                if ($this->config['show_pred_group'])
				{
					?><th <?php echo $tdClassStr; ?> ><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_MEMBER_GROUP'); ?></th><?php
				}
                
				$match_ids = NULL;
                $round_ids = NULL;
                $proteams_ids = NULL;
                
        // nur spiele zum tippen ?
		if ( $this->config['use_pred_select_matches'] )
        {
        $match_ids = $this->config['predictionmatchid'];
        }
        // nur spieltage tippen ?
      if ( $this->config['use_pred_select_rounds'] )
      {
      $round_ids = $this->config['predictionroundid'];
      }  
      // nur bestimmte mannschaften tippen ?
      if ( $this->config['use_pred_select_proteams'] )
        {
        $proteams_ids = $this->config['predictionproteamid'];
        }
        
      
        // hier holen wir uns die spiele zu dem projekt und der runde
				$roundMatchesList = sportsmanagementModelPredictionResults::getMatches($this->roundID,$predictionProject->project_id,$match_ids,$round_ids,$proteams_ids,$this->config['show_logo_small_overview']);
				
				//echo '<br />roundMatchesList<pre>~' . print_r($roundMatchesList,true) . '~</pre><br />';
				
				foreach ($roundMatchesList AS $match)
				{
					?>
					<th <?php echo $tdClassStr; ?> >
          <?php
          // clublogo oder vereinsflagge
						
                        
                        switch($this->config['show_logo_small_overview'])
                        //if ( $this->config['show_logo_small_overview'] == 1 ) //wir nehmen das kleine logo!
                        {
                            case 'logo_small':
                            case 'logo_middle':
                            case 'logo_big':
                            // bild ist nicht vorhanden, dann das standardbild
                            if ( !sportsmanagementHelper::existPicture( $match->homeLogo ) )
                            {
                            $match->homeLogo = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");    
                            }
echo sportsmanagementHelperHtml::getBootstrapModalImage('predresult'.$match->homeid,$match->homeLogo,$match->homeName,'20');                               
                            ?>                                    

                            <?PHP
                            //echo sportsmanagementModelPredictionResults::showClubLogo($match->homeLogobig,$match->homeName).'<br />';
                        if ( $this->config['show_team_names'] == 1 )
                        {
                            echo $match->homeShortName.'<br />';
                        }
                        break;
                        
                        case 'country_flag':    
						//if ( $this->config['show_logo_small_overview'] == 2 )
                        //{
                            echo JSMCountries::getCountryFlag($match->homeCountry).'<br />';
                        if ( $this->config['show_team_names'] == 1 )
                        {
                            echo $match->homeCountry.'<br />';
                        }
                        break;
                        }
                        
                        $outputStr = (isset($match->homeResult)) ? $match->homeResult : '-';
						$outputStr .= '&nbsp;'.$this->config['seperator'].'&nbsp;';
						$outputStr .= (isset($match->awayResult)) ? $match->awayResult : '-';
                        
						?>
                        <span class='hasTip' title="<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_RESULT_HINT',$match->homeName,$match->awayName,$outputStr); ?>"><?php echo $outputStr; ?></span>
                        <br />
                        <?php
						
                        
                        switch($this->config['show_logo_small_overview'])
                        //if ( $this->config['show_logo_small_overview'] == 1 ) //wir nehmen das kleine logo!
                        {
                            case 'logo_small':
                            case 'logo_middle':
                            case 'logo_big':
                            // bild ist nicht vorhanden, dann das standardbild
                            if ( !sportsmanagementHelper::existPicture( $match->awayLogo ) )
                            {
                            $match->awayLogo = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");    
                            }
echo sportsmanagementHelperHtml::getBootstrapModalImage('predresult'.$match->awayid,$match->awayLogo,$match->awayName,'20');                        
                            ?>                                    
                            
                            <?PHP
                            //echo '<br />'.sportsmanagementModelPredictionResults::showClubLogo($match->awayLogobig,$match->awayName).'<br />';
                        if ( $this->config['show_team_names'] == 1 )
                        {
                            echo $match->awayShortName.'<br />';
                        }
                        break;
                        
                        case 'country_flag':
						//if ( $this->config['show_logo_small_overview'] == 2 )
                        //{
                            echo '<br />'.JSMCountries::getCountryFlag($match->awayCountry);
                        if ( $this->config['show_team_names'] == 1 )
                        {
                            echo $match->awayCountry.'<br />';
                        }
                        break;
                        }
						
            ?>
            </th>
					<?php
				}
				?>
				<?php
				if ($this->config['show_points'])
				{
					?><th <?php echo $tdClassStr; ?> ><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_POINTS'); ?></th><?php
				}
				?>
				<?php
				if ($this->config['show_average_points'])
				{
					?><th <?php echo $tdClassStr; ?> ><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_AVERAGE'); ?></th><?php
				}
				?>
			</tr>
            </thead>
			<?php
			
			if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
      {
			echo '<br />predictionMember<pre>~' . print_r($this->predictionMember,true) . '~</pre><br />';
			echo '<br />predictionProject<pre>~' . print_r($predictionProject,true) . '~</pre><br />';
			}
			
			$k = 0;
			$tdStyleStr = " style='text-align:center; vertical-align:middle; ' ";

			// verlegt in die view.htm.php
            //$memberList = $this->model->getPredictionMembersList($this->config,$this->configavatar);
            
			//$memberResultsList = $this->model->getPredictionMembersResultsList($predictionProject->project_id,$this->roundID);

			$membersResultsArray = array();
			$membersDataArray = array();
			$membersMatchesArray = array();

      if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
		echo '<br />memberList<pre>~' . print_r($this->memberList,true) . '~</pre><br />';
		}
				
			foreach ($this->memberList AS $member)
			{
			
		if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
		echo '<br />member<pre>~' . print_r($member,true) . '~</pre><br />';
		}
				
				$memberPredictionPoints = sportsmanagementModelPrediction::getPredictionMembersResultsList(	$predictionProject->project_id,
																							$this->roundID,
																							$this->roundID,
																							$member->user_id);
        
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{																							
echo '<br />memberPredictionPoints<pre>~' . print_r($memberPredictionPoints,true) . '~</pre><br />';
}

				$memberPredictionPointsCount=0;
				$predictionsCount=0;
				$totalPoints=0;
				$totalTop=0;
				$totalDiff=0;
				$totalTend=0;
				$totalJoker=0;

				//$membersMatchesArray[$member->pmID]='';
				if (!empty($memberPredictionPoints))
				{
					foreach ($memberPredictionPoints AS $memberPredictionPoint)
					{
						if ((!is_null($memberPredictionPoint->homeResult)) ||
							(!is_null($memberPredictionPoint->awayResult)) ||
							(!is_null($memberPredictionPoint->homeDecision)) ||
							(!is_null($memberPredictionPoint->awayDecision)))
						{
						
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo '<br />memberPredictionPoint<pre>~' . print_r($memberPredictionPoint,true) . '~</pre><br />';
}
				    
							$predictionsCount++;
							$result = sportsmanagementModelPrediction::createResultsObject(	$memberPredictionPoint->homeResult,
																			$memberPredictionPoint->awayResult,
																			$memberPredictionPoint->prTipp,
																			$memberPredictionPoint->prHomeTipp,
																			$memberPredictionPoint->prAwayTipp,
																			$memberPredictionPoint->prJoker,
																			$memberPredictionPoint->homeDecision,
																			$memberPredictionPoint->awayDecision);
							$newPoints = sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
							//if (!is_null($memberPredictionPoint->prPoints))
							{
								$points = $memberPredictionPoint->prPoints;
								if ( $newPoints != $points )
								{
									// this check also should be done if the result is not displayed
									$memberPredictionPoint = sportsmanagementModelPrediction::savePredictionPoints(	$memberPredictionPoint,
																								$predictionProject,
																								true);
									//$points=$newPoints;
								}
								//$totalPoints=$totalPoints+$points;
							}
							if (!is_null($memberPredictionPoint->prJoker)){$totalJoker=$totalJoker+$memberPredictionPoint->prJoker;}
							if (!is_null($memberPredictionPoint->prTop)){$totalTop=$totalTop+$memberPredictionPoint->prTop;}
							if (!is_null($memberPredictionPoint->prDiff)){$totalDiff=$totalDiff+$memberPredictionPoint->prDiff;}
							if (!is_null($memberPredictionPoint->prTend)){$totalTend=$totalTend+$memberPredictionPoint->prTend;}
						}

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo '<br />memberPredictionPoint<pre>~' . print_r($memberPredictionPoint,true) . '~</pre><br />';
}
						
						$memberPredictionOutput = JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOT_AVAILABLE');

                        $matchTimeDate = sportsmanagementHelper::getTimestamp($memberPredictionPoint->match_date,1,$predictionProjectSettings->timezone);
                        $thisTimeDate = sportsmanagementHelper::getTimestamp(date("Y-m-d H:i:s"),1,$predictionProjectSettings->timezone);
						$showAllowed = (($thisTimeDate >= $matchTimeDate) ||
										(!is_null($memberPredictionPoint->homeResult)) ||
										(!is_null($memberPredictionPoint->awayResult)) ||
										(!is_null($memberPredictionPoint->homeDecision)) ||
										(!is_null($memberPredictionPoint->awayDecision)) ||
										($this->predictionMember->pmID==$member->pmID));

//echo '<br />showAllowed<pre>~' . print_r($showAllowed,true) . '~</pre><br />';
//echo '<br />matchTimeDate <pre>~' . print_r($matchTimeDate ,true) . '~</pre><br />';
//echo '<br />thisTimeDate <pre>~' . print_r($thisTimeDate ,true) . '~</pre><br />';

						if ($showAllowed)
						{
							
              // anzeige ändern bei normaler tipeingabe und toto-tip
              if ( $predictionProject->mode == 0 )
              {
              $memberPredictionOutput = $memberPredictionPoint->prHomeTipp.$this->config['seperator'].$memberPredictionPoint->prAwayTipp;
              }
              elseif ( $predictionProject->mode == 1 )
              {
              $memberPredictionOutput = $memberPredictionPoint->prTipp;
              }



							if ((!is_null($memberPredictionPoint->homeResult)) ||
								(!is_null($memberPredictionPoint->awayResult)) ||
								(!is_null($memberPredictionPoint->homeDecision)) ||
								(!is_null($memberPredictionPoint->awayDecision)))
							{
								$points = $memberPredictionPoint->prPoints;
								$totalPoints = $totalPoints+$points;
								$memberPredictionPointsCount++;
								$memberPredictionOutput .= '<sub style="color: red;">'.$points.'</sub>';
							}
							else	// needed for Windows Internet Explorer
							{
								$memberPredictionOutput .= '<sub>&nbsp;</sub>';
							}
						}
						else
						{
							$memberPredictionOutput = '- '.$this->config['seperator'].' -';
						}
						
            if ($memberPredictionPoint->prJoker)
						{
							$memberPredictionOutput .= '<sub style="color: red;">*</sub>';
						}

						//$membersMatchesArray[$member->pmID].='<td'.$tdStyleStr.'>'.$memberPredictionOutput.'</td>';
						$membersMatchesArray[$member->pmID][$memberPredictionPoint->matchID] = $memberPredictionOutput;
					}
				}

				$membersResultsArray[$member->pmID]['rank']	= 0;
                $membersResultsArray[$member->pmID]['pg_group_name'] = $member->pg_group_name;
                $membersResultsArray[$member->pmID]['pg_group_id'] = $member->pg_group_id;
				$membersResultsArray[$member->pmID]['predictionsCount']	= $predictionsCount;
				$membersResultsArray[$member->pmID]['totalPoints'] = $totalPoints;
				$membersResultsArray[$member->pmID]['totalTop'] = $totalTop;
				$membersResultsArray[$member->pmID]['totalDiff'] = $totalDiff;
				$membersResultsArray[$member->pmID]['totalTend'] = $totalTend;
				$membersResultsArray[$member->pmID]['totalJoker'] = $totalJoker;

				// check all needed output for later
				{
					$picture = $member->avatar;
					
					if ( $member->aliasName )
          {
          $member->name = $member->aliasName;
          }
          
					$playerName = $member->name;
					$membersDataArray[$member->pmID]['pg_group_name'] = $member->pg_group_name;
                    $membersDataArray[$member->pmID]['pg_group_id']	= $member->pg_group_id;
                    
					if (((!isset($member->avatar)) ||
						($member->avatar=='') ||
						(!file_exists($member->avatar)) ||
						((!$member->show_profile) && ($this->predictionMember->pmID!=$member->pmID))))
					{
						$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
					}
				//tobe removed
				//$imgTitle = JText::sprintf('JL_PRED_AVATAR_OF',$member->name);
				//$output = JHTML::image($member->avatar,$imgTitle,array(' width' => 20, ' title' => $imgTitle));
				
					$output = sportsmanagementHelper::getPictureThumb($picture, $playerName,0,25);
					$membersDataArray[$member->pmID]['show_user_icon'] = $output;
				
					if ( ( $this->config['link_name_to'] ) && (($member->show_profile)||($this->predictionMember->pmID==$member->pmID)))
					{
						$link = JSMPredictionHelperRoute::getPredictionMemberRoute(sportsmanagementModelPrediction::$predictionGameID,$member->pmID);
						$output = JHTML::link($link,$member->name);
					}
					else
					{
						$output = $member->name;
					}
					$membersDataArray[$member->pmID]['name'] = $output;
				}
			}

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo '<br />membersResultsArray<pre>~' . print_r($membersResultsArray,true) . '~</pre><br />';
echo '<br />membersDataArray<pre>~' . print_r($membersDataArray,true) . '~</pre><br />';
}
			
			$computedMembersRanking = sportsmanagementModelPrediction::computeMembersRanking($membersResultsArray,$this->config);
			$recordCount = count($computedMembersRanking);
			
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo '<br />computedMembersRanking<pre>~' . print_r($computedMembersRanking,true) . '~</pre><br />';
echo '<br />membersMatchesArray<pre>~' . print_r($membersMatchesArray,true) . '~</pre><br />';
}
      
			$i=1;
            $skipMemberCount = 0;

/*			
            if ((int)$this->config['limit'] < 1){$this->config['limit']=1;}
			$rlimit=ceil($recordCount / $this->config['limit']);
			$this->model->page=($this->model->page > $rlimit) ? $rlimit : $this->model->page;
			$skipMemberCount=($this->model->page > 0) ? (($this->model->page-1)*$this->config['limit']) : 0;
*/

?>
<tbody>
<?PHP
			foreach ($computedMembersRanking AS $key => $value)
			{
				if ($i <= $skipMemberCount) { $i++; continue; }

				//$class = ($k==0) ? 'sectiontableentry1' : 'sectiontableentry2';
                // änderung bluesunny62
				//$styleStr = ($this->predictionMember->pmID==$key) ? ' style="background-color:yellow; color:black; " ' : '';
                $styleStr = ($this->predictionMember->pmID==$key) ? ' style="background-color:'.$this->config['background_color_ranking'].'; color:black; " ' : '';
				//$class = ($this->predictionMember->pmID==$key) ? 'sectiontableentry1' : $class;
				$tdStyleStr = " style='text-align:center; vertical-align:middle; ' ";
                $class = '';
				?>
				<?php
	
    			
    
					?>
					<tr <?php echo $styleStr; ?> >
						<td<?php echo $tdStyleStr; ?>><?php echo $value['rank']; ?></td>
						<?php
						if ($this->config['show_user_icon'])
						{
							?><td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['show_user_icon']; ?></td><?php
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
						foreach ($roundMatchesList AS $roundMatch)
						{
							echo '<td'.$tdStyleStr.'>';
							if (isset($membersMatchesArray[$key][$roundMatch->mID]))
							{
								echo $membersMatchesArray[$key][$roundMatch->mID];
							}
							else
							{
								echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOT_AVAILABLE');
							}
							echo '</td>';
						}
						?>

						<?php
						if ($this->config['show_points'])
						{
							?><td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalPoints']; ?></td><?php
						}
						?>
						<?php
						if ($this->config['show_average_points'])
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
					</tr>
					<?php
					$k = (1-$k);
					$i++;
					//if ($i > $skipMemberCount+$this->config['limit']){break;}
				//}
			}
			?>
            </tbody>
		</table>
        </div>
	<?php
	}
}
?>
