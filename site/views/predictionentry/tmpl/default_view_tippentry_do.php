<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_view_tippentry_do.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionentry
 */

defined('_JEXEC') or die('Restricted access');


if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
$visible = 'text';    
echo '<br />config<pre>~' . print_r($this->config,true) . '~</pre><br />';
echo '<br />allowedAdmin<pre>~' . print_r($this->allowedAdmin,true) . '~</pre><br />';
echo '<br />predictionMember<pre>~' . print_r($this->predictionMember,true) . '~</pre><br />';
}
else
{
$visible = 'hidden';    
}

//$this->config['show_tipp_tendence']=1;
if (((JFactory::getUser()->id==0) || (!sportsmanagementModelPrediction::checkPredictionMembership())) &&
	((!$this->allowedAdmin) || ($this->predictionMember->pmID==0)))
{
	if ($this->allowedAdmin)
	{
		echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_SELECT_EXISTING_MEMBER');

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{		
echo '<br />allowedAdmin<pre>~' . print_r($this->allowedAdmin,true) . '~</pre><br />';
echo '<br />predictionMember<pre>~' . print_r($this->predictionMember,true) . '~</pre><br />';
echo '<br />getUser<pre>~' . print_r(JFactory::getUser()->id,true) . '~</pre><br />';
}

	}
}
else
{
	foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
	{

//echo __FILE__.' '.__LINE__.' 1 predictionProject project_id<br><pre>'.print_r($predictionProject->project_id,true).'</pre>';			
//echo __FILE__.' '.__LINE__.' 1 model->pjID project_id<br><pre>'.print_r(sportsmanagementModelPredictionEntry::$pjID,true).'</pre>';
		
        //$predictionProject->joker=0;
		$gotSettings = $predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject(sportsmanagementModelPrediction::$pjID);
//echo __FILE__.' '.__LINE__.' 1 gotSettings<br><pre>'.print_r($gotSettings,true).'</pre>';        
		if ( ( ( (int)sportsmanagementModelPredictionEntry::$pjID == (int)$predictionProject->project_id ) && ($gotSettings) ) || ( (int)sportsmanagementModelPredictionEntry::$pjID == 0 ) )
        //if ( ( ( $this->model->pjID == $predictionProject->project_id ) && ($gotSettings) )  )
		{
			
//echo __FILE__.' '.__LINE__.' 2 predictionProject project_id<br><pre>'.print_r($predictionProject->project_id,true).'</pre>';			
//echo __FILE__.' '.__LINE__.' 2 model->pjID project_id<br><pre>'.print_r(sportsmanagementModelPredictionEntry::$pjID,true).'</pre>';
            
            sportsmanagementModelPredictionEntry::$pjID = sportsmanagementModelPrediction::$pjID;
      $this->model->predictionProject = $predictionProject;
			$actualProjectCurrentRound = sportsmanagementModelPrediction::getProjectSettings(sportsmanagementModelPrediction::$pjID);

//echo __FILE__.' '.__LINE__.' 3 predictionProject project_id<br><pre>'.print_r($predictionProject->project_id,true).'</pre>';			
//echo __FILE__.' '.__LINE__.' 3 model->pjID project_id<br><pre>'.print_r(sportsmanagementModelPredictionEntry::$pjID,true).'</pre>';

//echo __FILE__.' '.__LINE__.' roundID<br><pre>'.print_r(sportsmanagementModelPrediction::$roundID,true).'</pre>';            
            
            if (!isset( sportsmanagementModelPrediction::$roundID ) || ( (int)sportsmanagementModelPrediction::$roundID < 1 ) )
            {
                sportsmanagementModelPrediction::$roundID = $actualProjectCurrentRound;
                }
//echo __FILE__.' '.__LINE__.' roundID<br><pre>'.print_r(sportsmanagementModelPredictionEntry::$roundID,true).'</pre>';                
			if ( (int)sportsmanagementModelPrediction::$roundID < 1)
            {
                sportsmanagementModelPrediction::$roundID = 1;
                }
//echo __FILE__.' '.__LINE__.' roundID<br><pre>'.print_r(sportsmanagementModelPredictionEntry::$roundID,true).'</pre>';
			if ( (int)sportsmanagementModelPrediction::$roundID > sportsmanagementModelPrediction::getProjectRounds(sportsmanagementModelPrediction::$pjID)) 
            {
                sportsmanagementModelPrediction::$roundID = $this->model->_projectRoundsCount;
                }
//echo __FILE__.' '.__LINE__.' roundID<br><pre>'.print_r(sportsmanagementModelPredictionEntry::$roundID,true).'</pre>';
			$memberProjectJokersCount = sportsmanagementModelPrediction::getMemberPredictionJokerCount($this->predictionMember->user_id,
																					sportsmanagementModelPrediction::$pjID);

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
        
			$roundResults = $this->model->getMatchesDataForPredictionEntry(	sportsmanagementModelPrediction::$predictionGameID,
																			sportsmanagementModelPrediction::$pjID,
																			sportsmanagementModelPrediction::$roundID,
																			$this->predictionMember->user_id,
                                                                            $match_ids,
                                                                            $round_ids,
                                                                            $proteams_ids);

			//$roundResults = null;
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{		
echo '<br />predictionGameID<pre>~' . print_r($this->model->predictionGameID,true) . '~</pre><br />';
echo '<br />project_id<pre>~' . print_r($predictionProject->project_id,true) . '~</pre><br />';
echo '<br />roundID<pre>~' . print_r($this->model->roundID,true) . '~</pre><br />';
echo '<br />user_id<pre>~' . print_r($this->predictionMember->user_id,true) . '~</pre><br />';
echo '<br />roundResults<pre>~' . print_r($roundResults,true) . '~</pre><br />';
}			
			
			
			
			
//			if (($this->config['show_help']==0)||($this->config['show_help']==2)){echo $this->model->createHelptText($predictionProject->mode);}
			?>
<!--			<a name='jl_top' id='jl_top'></a> -->
			<form name='resultsRoundSelector' id='resultsRoundSelector' method='post' onsubmit="alert(1)">
				<input type='hidden' name='option' value='com_sportsmanagement' />
				<input type='hidden' name='task' value='predictionentry.selectprojectround' />
                <input type='hidden' name='cfg_which_database' value='<?php echo sportsmanagementModelPrediction::$cfg_which_database; ?>' />
				<input type='hidden' name='prediction_id' value='<?php echo sportsmanagementModelPrediction::$predictionGameID; ?>' />
				<input type='hidden' name='p' value='<?php echo (int)$predictionProject->project_id; ?>' />
                <input type='hidden' name='pj' value='<?php echo sportsmanagementModelPrediction::$pjID; ?>' />
				<input type='hidden' name='r' value='<?php echo sportsmanagementModelPrediction::$roundID; ?>' />
                <input type='hidden' name='pggroup' value='<?php echo sportsmanagementModelPrediction::$pggroup; ?>' />
				<input type='hidden' name='memberID' value='<?php echo $this->predictionMember->pmID; ?>' />
				<input type='hidden' name='pjID' value='<?php echo sportsmanagementModelPrediction::$pjID; ?>' />
				<?php echo JHTML::_('form.token'); ?>
<!--
Responsive tables
Create responsive tables by adding .table-responsive to any .table to make them scroll horizontally on small devices (under 768px). 
When viewing on anything larger than 768px wide, you will not see any difference in these tables.
--> 
<div class="table-responsive">
				<table class="table table-responsive" >
					<tr>
						<td class=""><b><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_SUBTITLE_01'); ?></b></td>
					<!--	<td class="" style='text-align:right; ' width='20%'  > -->
							<?php
                            if ( $this->config['use_pred_select_rounds'] )
      {
      $round_ids = $this->config['predictionroundid'];
      }
                            
							$rounds = sportsmanagementHelper::getRoundsOptions((int)sportsmanagementModelPrediction::$pjID,'ASC',FALSE,$round_ids);
//							$htmlRoundsOptions = JHTML::_('select.genericlist',$rounds,'current_round','class="inputbox" size="1" onchange="document.forms[\'resultsRoundSelector\'].r.value=this.value;submit()"','value','text',$this->model->roundID);
							$htmlRoundsOptions = JHTML::_('select.genericlist',$rounds,'r','class="inputbox" size="1" onchange="this.form.submit();"','value','text',sportsmanagementModelPrediction::$roundID );
							echo JText::sprintf(	'COM_SPORTSMANAGEMENT_PRED_ENTRY_SUBTITLE_02',
													'<td>'.$htmlRoundsOptions.'</td>',
													'<td>'.sportsmanagementModelPrediction::createProjectSelector(sportsmanagementModelPrediction::$_predictionProjectS,(int)sportsmanagementModelPrediction::$pjID).'</td>');
							?>
					<!--	</td> -->
					</tr>
				</table>
                </div>
                <br />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
			<?php $formName = 'predictionDoTipp'.(int)sportsmanagementModelPrediction::$pjID; ?>
			<form	name='<?php echo $formName; ?>'
					id='<?php echo $formName; ?>'
					method='post' onsubmit='return chkFormular()' >

				<input type='hidden' name='task' value='predictionentry.addtipp' />
				<input type='hidden' name='option' value='com_sportsmanagement' />
                <input type='hidden' name='cfg_which_database' value='<?php echo sportsmanagementModelPrediction::$cfg_which_database; ?>' />
				<input type='hidden' name='pj' value='<?php echo (int)sportsmanagementModelPrediction::$pjID; ?>' />
				<input type='hidden' name='prediction_id' value='<?php echo sportsmanagementModelPrediction::$predictionGameID; ?>' />
				<input type='hidden' name='user_id' value='<?php echo $this->predictionMember->user_id; ?>' />
				<input type='hidden' name='memberID' value='<?php echo $this->predictionMember->pmID; ?>' />
				<input type='hidden' name='r' value='<?php echo sportsmanagementModelPrediction::$roundID; ?>' />
				<input type='hidden' name='pjID' value='<?php echo (int)sportsmanagementModelPredictionEntry::$pjID; ?>' />
				<input type='hidden' name='pids[]' value='<?php echo (int)sportsmanagementModelPrediction::$pjID; ?>' />
				<input type='hidden' name='ptippmode[<?php echo (int)sportsmanagementModelPrediction::$pjID; ?>]' value='<?php echo $predictionProject->mode; ?>' />
				<input type='hidden' name='jokerCount' value='<?php echo $memberProjectJokersCount; ?>' />
				<input type='hidden' name='maxJokerCount' value='<?php echo $predictionProject->joker_limit; ?>' />
				<?php echo JHTML::_('form.token'); ?>
                
<?php
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{		
echo '<br />predictionDoTipp<br />';
echo '<br />prediction_id<pre>~' . print_r($this->model->predictionGameID,true) . '~</pre><br />';
echo '<br />user_id<pre>~' . print_r($this->predictionMember->user_id,true) . '~</pre><br />';
echo '<br />memberID<pre>~' . print_r($this->predictionMember->pmID,true) . '~</pre><br />';


}
?>
                
				<script type='text/javascript'>
					function chkFormular()
					{
						var message = "";

						if ( parseInt(document.<?php echo $formName; ?>.jokerCount.value) > parseInt(document.<?php echo $formName; ?>.maxJokerCount.value) )
						{
							message+="<?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CHECK_JOKERS_COUNT'); ?>\n";
						}
						if (message==""){return true;}
						else {
						  alert(message);
						  return false;
						}
					}
				</script>
<!--
Responsive tables
Create responsive tables by adding .table-responsive to any .table to make them scroll horizontally on small devices (under 768px). 
When viewing on anything larger than 768px wide, you will not see any difference in these tables.
-->                 
                <div class="table-responsive">
				<table class="<?PHP echo $this->config['table_class']; ?> table-responsive" >
					<tr>
						<th class="" style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DATE_TIME'); ?></th>
						<th class='sectiontableheader' style='text-align:center; ' colspan="5" ><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MATCH'); ?></th>
						<th class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_RESULT'); ?></th>
						<th class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_YOURS'); ?></th>
						<?php
						if (($predictionProject->joker) && ($predictionProject->mode==0))
						{
							?><th class='sectiontableheader' style='text-align:center; '><?php
							if ($predictionProject->joker_limit > 0)
							{
								echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_JOKER_COUNT',$memberProjectJokersCount,$predictionProject->joker_limit);
							}
							else
							{
								echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_JOKER');
							}
							?></th><?php
						}
						?>
						<th class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_POINTS'); ?></th>
					</tr>
					<?php
					$k = 1;
					$disabled = '';
					$totalPoints=0;

					if (empty(sportsmanagementModelPrediction::$_predictionMember->fav_team))
                    {
                        sportsmanagementModelPrediction::$_predictionMember->fav_team = '0,0';
                        }
                        
					$sFavTeamsList = explode(';',sportsmanagementModelPrediction::$_predictionMember->fav_team);
					foreach ($sFavTeamsList AS $key => $value)
                    {
                        $dFavTeamsList[]=explode(',',$value);
                        }
                        
					foreach ($dFavTeamsList AS $key => $value)
                    {
                        $favTeamsList[$value[0]]=$value[1];
                        }

					if (empty(sportsmanagementModelPrediction::$_predictionMember->champ_tipp))
                    {
                        sportsmanagementModelPrediction::$_predictionMember->champ_tipp = '0,0';
                        }
                        
					$sChampTeamsList = explode(';',sportsmanagementModelPrediction::$_predictionMember->champ_tipp);
					foreach ($sChampTeamsList AS $key => $value)
                    {
                        $dChampTeamsList[]=explode(',',$value);
                        }
					foreach ($dChampTeamsList AS $key => $value)
                    {
                        $champTeamsList[$value[0]] = $value[1];
                        }

					$showSaveButton=false;
					if (count($roundResults) > 0)
					{
					
          // schleife über die ergebnisse in der runde
					foreach ($roundResults AS $result)
					{
						$class = ($k==0) ? 'sectiontableentry1' : 'sectiontableentry2';

						$resultHome = (isset($result->team1_result)) ? $result->team1_result : '-';
						if (isset($result->team1_result_decision)){$resultHome=$result->team1_result_decision;}
						$resultAway = (isset($result->team2_result)) ? $result->team2_result : '-';
						if (isset($result->team2_result_decision)){$resultAway=$result->team2_result_decision;}
						
						$closingtime = $this->config['closing_time'] ;//3600=1 hour
						$matchTimeDate = sportsmanagementHelper::getTimestamp($result->match_date,1,$predictionProjectSettings->timezone);
						$thisTimeDate = sportsmanagementHelper::getTimestamp(date("Y-m-d H:i:s"),1,$predictionProjectSettings->timezone);
						

            // änderungen erlaubt ?   $this->config['show_help']
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
echo '<br />this->closingtime<pre>~' . print_r($closingtime,true) . '~</pre><br />';
echo '<br />this->matchTimeDate<pre>~' . print_r($matchTimeDate,true) . '~</pre><br />';
echo '<br />this->thisTimeDate<pre>~' . print_r($thisTimeDate,true) . '~</pre><br />';
            
            
echo '<br />this->allowedAdmin<pre>~' . print_r($this->allowedAdmin,true) . '~</pre><br />';
echo '<br />this->predictionMember->admintipp<pre>~' . print_r($this->predictionMember->admintipp,true) . '~</pre><br />';
echo '<br />this->use_tipp_admin<pre>~' . print_r($this->config['use_tipp_admin'],true) . '~</pre><br />';
            }
            
            $matchTimeDate = $matchTimeDate - $closingtime; 
						$tippAllowed =	( ( $thisTimeDate < $matchTimeDate ) &&
										 ($resultHome=='-') &&
										 ($resultAway=='-') ) || (($this->allowedAdmin)&&($this->predictionMember->admintipp));
						//$tippAllowed = true;
						if (!$tippAllowed){$disabled=' disabled="disabled" ';}else{$disabled=''; $showSaveButton=true;}
						
						if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
            echo '<br />this->matchTimeDate nach berechnung<pre>~' . print_r($matchTimeDate,true) . '~</pre><br />';
            echo '<br />this->thisTimeDate<pre>~' . print_r($thisTimeDate,true) . '~</pre><br />';
            echo '<br />resultHome<pre>~'.print_r($resultHome,true).'~</pre><br />';
						echo '<br />resultAway<pre>~'.print_r($resultAway,true).'~</pre><br />';
						echo '<br />tippAllowed<pre>~'.print_r($tippAllowed,true).'~</pre><br />';
						echo '<br />date<pre>~' . print_r( JHTML::_('date',date('Y-m-d H:i:s', $thisTimeDate), "%Y-%m-%d %H:%M:%S"), true ) . '~</pre><br />';
						echo '<br />predictionProjectSettings<pre>~' . print_r($predictionProjectSettings->timezone, true ) . '~</pre><br />';
						}
						
            ?>
						<tr class='<?php echo $class; ?>'>
							<td class="td_c">
								<?php
                // das datum des spiels
// 								echo JHTML::date($result->match_date,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE'));
// 								echo ' - ';
// 								echo JHTML::date(date("Y-m-d H:i:s",$matchTimeDate),$this->config['time_format']); 
                //echo $result->match_date;
                echo JHtml::date($result->match_date, 'd.m.Y H:i', false);
								?>
							</td>
								<?php
								$homeName = sportsmanagementModelPrediction::getMatchTeam($result->projectteam1_id,$this->config['prediction_team_name']);
								$awayName = sportsmanagementModelPrediction::getMatchTeam($result->projectteam2_id,$this->config['prediction_team_name']);
//								$homeName = $this->model->getMatchTeam($result->projectteam1_id);
//								$awayName = $this->model->getMatchTeam($result->projectteam2_id);								
								?>
							<td nowrap='nowrap' class="td_r">
								<?php
								if 	((isset($favTeamsList[$predictionProject->project_id])) &&
									($favTeamsList[$predictionProject->project_id]==$result->projectteam1_id))
								{
								?>
								<span style='background-color:yellow; color:black; padding:2px; '><?php echo $homeName; ?></span>
								<?php
								}
								else
								{
									echo $homeName;
								}
								?>
							</td>
							<td nowrap='nowrap' class="td_c">
								<?php
                // clublogo oder vereinsflagge
								switch($this->config['show_logo_small'])
                        //if ( $this->config['show_logo_small_overview'] == 1 ) //wir nehmen das kleine logo!
                        {
                            case 'logo_small':
                            case 'logo_middle':
                            case 'logo_big':
									$logo_home = sportsmanagementModelPrediction::getMatchTeamClubLogo($result->projectteam1_id,$this->config['show_logo_small']);
									if	(($logo_home == '') || (!file_exists($logo_home)))
									{
										$logo_home = 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif';
									}
									$imgTitle = JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $homeName);
									//echo JHTML::image($logo_home,$imgTitle,array(' width' => 20,' title' => $imgTitle));
                                    ?>                                    
                                    
<a href="<?php echo JURI::root().$logo_home;?>" title="<?php echo $imgTitle;?>" data-toggle="modal" data-target="#modal<?php echo $result->projectteam1_id;?>">
<img src="<?php echo JURI::root().$logo_home;?>" alt="<?php echo $imgTitle;?>" width="20" />
</a>        
<div class="modal fade" id="modal<?php echo $result->projectteam1_id;?>" tabindex="-1" role="dialog" aria-labelledby="beispielModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image(JURI::root().$logo_home, $imgTitle, array('title' => $imgTitle,'class' => "img-rounded" ));      
?>
</div>
                                    
                                    <?PHP 
                                    
                                    
									echo ' ';
								break;
                                //}
                                
                case 'country_flag':                
                //if ( $this->config['show_logo_small'] == 2 )
//								{
                $country_home = sportsmanagementModelPrediction::getMatchTeamClubFlag($result->projectteam1_id);
                echo JSMCountries::getCountryFlag($country_home);
                break;
                }
								?>
							</td>							
							<td nowrap='nowrap' class="td_c">
								<?php							
								echo ' <b>' . $this->config['seperator'] . '</b> ';
								?>
							</td>
							<td nowrap='nowrap' class="td_c">
								<?php	
                // clublogo oder vereinsflagge
								switch($this->config['show_logo_small'])
                        //if ( $this->config['show_logo_small_overview'] == 1 ) //wir nehmen das kleine logo!
                        {
                            case 'logo_small':
                            case 'logo_middle':
                            case 'logo_big':
									$logo_away = sportsmanagementModelPrediction::getMatchTeamClubLogo($result->projectteam2_id,$this->config['show_logo_small']);
									if (($logo_away=='') || (!file_exists($logo_away)))
									{
										$logo_away = 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif';
									}
									$imgTitle = JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $awayName);
									echo ' ';
                                    ?>                                    
                                    
<a href="<?php echo JURI::root().$logo_away;?>" title="<?php echo $imgTitle;?>" data-toggle="modal" data-target="#modal<?php echo $result->projectteam2_id;?>">
<img src="<?php echo JURI::root().$logo_away;?>" alt="<?php echo $imgTitle;?>" width="20" />
</a>        
<div class="modal fade" id="modal<?php echo $result->projectteam2_id;?>" tabindex="-1" role="dialog" aria-labelledby="beispielModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image(JURI::root().$logo_away, $imgTitle, array('title' => $imgTitle,'class' => "img-rounded" ));      
?>
</div>
                                    
                                    <?PHP
									//echo JHTML::image($logo_away,$imgTitle,array(' width' => 20,' title' => $imgTitle));
								break;
                                //}
                case 'country_flag':    
						//if ( $this->config['show_logo_small_overview'] == 2 )
                        //{
                $country_away = sportsmanagementModelPrediction::getMatchTeamClubFlag($result->projectteam2_id);
                echo JSMCountries::getCountryFlag($country_away);
                break;
                }
								?>
							</td>						
							<td nowrap='nowrap' class="td_l">
								<?php	
								if 	((isset($favTeamsList[$predictionProject->project_id])) &&
									($favTeamsList[$predictionProject->project_id] == $result->projectteam2_id))
								{
									?><span style='background-color:yellow; color:black; padding:2px; '><?php echo $awayName; ?></span>
								<?php
								}
								else
								{
									echo $awayName;
								}
								?>
							</td>
						<!-- @ShortBrother <td class="td_c"> -->
                            <td style="text-align: center;">
								<?php
								echo $resultHome . $this->config['seperator'] . $resultAway;
								?>
							</td>
							<td class="td_c">
								<?php
								echo '<input	type="hidden" name="cids[' . $predictionProject->project_id . '][]"
													value="' . $result->id . '" />';
								echo '<input	type="hidden"
													name="prids[' . $predictionProject->project_id . '][' . $result->id . ']"
													value="' . $result->prid . '" />';

                // welcher tippmodus
								if ($predictionProject->mode=='0')	// Tipp in normal mode
								{
									echo $this->createStandardTippSelect(	$result->tipp_home,
                                    $result->tipp_away,
                                    $result->tipp,
																					$predictionProject->project_id,
                                                                                    $result->id,
																					$this->config['seperator'],
                                                                                    $tippAllowed);
								}
								else	// Tipp in toto mode
								{
									echo $this->createTotoTippSelect(	$result->tipp_home,
                                    $result->tipp_away,
                                    $result->tipp,
																				$predictionProject->project_id,
                                                                                $result->id,
                                                                                $tippAllowed);
								}
								
								?>
							</td>
							<?php
							if (($predictionProject->joker) && ($predictionProject->mode==0))
							{
							?>
								<td class="td_c">
									<input	type='checkbox' id='cbj_<?php echo $result->id; ?>'
											name='jokers[<?php echo $predictionProject->project_id; ?>][<?php echo $result->id; ?>]'
											value='<?php echo $result->joker; ?>' <?php
											if (isset($result->joker) && ($result->joker=='1')){echo 'checked="checked" ';}
											echo $disabled; ?> onchange='	if(this.checked)
																			{
																				document.<?php echo $formName; ?>.jokerCount.value++;
																				if ( parseInt(document.<?php echo $formName; ?>.jokerCount.value) > parseInt(document.<?php echo $formName; ?>.maxJokerCount.value) )
																				{
																					alert("<?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAX_JOKER_WARNING'); ?>");
																					this.checked=false;
																					document.<?php echo $formName; ?>.jokerCount.value--;
																				}
				  															}
				  															else
																			{
																				document.<?php echo $formName; ?>.jokerCount.value--;
																			}' /><?php
									if ((!empty($disabled)) && (!empty($result->joker)))
									{
										?><input	type='hidden' id='cbj_<?php echo $result->id; ?>'
													name='jokers[<?php echo $predictionProject->project_id; ?>][<?php echo $result->id; ?>]'
													value='<?php echo $result->joker; ?>' /><?php
									}
								?>
								</td><?php
							}
							?>
							<td class="td_c"><?php
								if ((!$tippAllowed) || (($this->allowedAdmin)&&($this->predictionMember->admintipp)))
								{
									$points = sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
									$totalPoints = $totalPoints+$points;
									echo $points;
								}
								else
								{
									?>&nbsp;<?php
								}
								?>
							</td>
						</tr>
						<?php
						if ( $this->config['show_tipp_tendence'] )
						{
							?>
							<tr class="tipp_tendence">
								<td class="td_c"><?php
									//echo 'ChartImage';
									echo '&nbsp;'; ?></td>
								<td class="td_l">
									<?php
									$totalCount = $this->model->getTippCount($predictionProject->project_id, $result->id, 3);
									$homeCount = $this->model->getTippCount($predictionProject->project_id, $result->id, 1);
									$awayCount = $this->model->getTippCount($predictionProject->project_id, $result->id, 2);
									$drawCount = $this->model->getTippCount($predictionProject->project_id, $result->id, 0);
									if ($totalCount > 0)
									{
										$percentageH = round(( $homeCount * 100 / $totalCount ),2);
										$percentageD = round(( $drawCount * 100 / $totalCount ),2);
										$percentageA = round(( $awayCount * 100 / $totalCount ),2);
									}
									else
									{
										$percentageH = 0;
										$percentageD = 0;
										$percentageA = 0;
									}
									?>
									<span style='color:<?php echo $this->config['color_home_win']; ?>; ' >
									<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_HOME_WIN',$percentageH,$homeCount);?></span><br />
									<span style='color:<?php echo $this->config['color_draw']; ?>; '>
									<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_DRAW',$percentageD,$drawCount);?></span><br />
									<span style='color:<?php echo $this->config['color_guest_win']; ?>; '>
									<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_AWAY_WIN',$percentageA,$awayCount); ?></span>
								</td>
								<td colspan='8'>&nbsp;</td>
							</tr>
							<?php
						}
						else
						{
							$k = (1-$k);							
						}
						
					}
					}
					?>
					<tr>
						<?php
						if (count($roundResults)==0)
						{
							$colspan=($predictionProject->joker) ? '6' : '5';
							?>
							<td colspan='<?php echo $colspan; ?>' class="td_c" >
								<b><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NO_POSSIBLE_PREDICTIONS'); ?></b>
							</td>
							<?php
						}
						else
						{
							?>
							<td colspan='6'>&nbsp;</td>
							<td class="td_c">
								<?php
								if (count($roundResults) > 0)
								{
									if ($showSaveButton)
									{
									?><input type='submit' name='addtipp' value='<?php echo JText::_('JSAVE'); ?>' class='button' /><?php
									}
									else
									{
										?>&nbsp;<?php
									}
								}
								else
								{
									?>&nbsp;<?php
								}
								?>
							</td>
							<?php echo $colspan=($predictionProject->joker) ? '<td>&nbsp;</td>' : ''; ?>
							<td class="td_c"><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_TOTAL_POINTS_COUNT',$totalPoints); ?></td>
							<?php
						}
						?>
					</tr>
				</table>
               </div>
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
            <br />
			<?php
//			if (($this->config['show_help']==1)||($this->config['show_help']==2))
//			{
//				echo $this->model->createHelptText($predictionProject->mode);
//			}
		}
	}
}
?><br />