<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_view_tippentry_do.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage predictionentry
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
	
        $visible = 'hidden';

    if (((Factory::getUser()->id==0) || (!sportsmanagementModelPrediction::checkPredictionMembership())) &&
        ((!$this->allowedAdmin) || ($this->predictionMember->pmID==0)))
    {
        if ($this->allowedAdmin)
        {
            echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_SELECT_EXISTING_MEMBER');
        }
    }
    else
    {
        foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
        {
            $gotSettings = $predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject(sportsmanagementModelPrediction::$pjID);
            if ( ( ( (int)sportsmanagementModelPredictionEntry::$pjID == (int)$predictionProject->project_id ) && ($gotSettings) ) || ( (int)sportsmanagementModelPredictionEntry::$pjID == 0 ) )
                //if ( ( ( $this->model->pjID == $predictionProject->project_id ) && ($gotSettings) )  )
            {
               
                sportsmanagementModelPredictionEntry::$pjID = sportsmanagementModelPrediction::$pjID;
                $this->model->predictionProject = $predictionProject;
                $actualProjectCurrentRound = sportsmanagementModelPrediction::getProjectSettings(sportsmanagementModelPrediction::$pjID);
                
                if (!isset( sportsmanagementModelPrediction::$roundID ) || ( (int)sportsmanagementModelPrediction::$roundID < 1 ) )
                {
                    sportsmanagementModelPrediction::$roundID = $actualProjectCurrentRound;
                }
                if ( (int)sportsmanagementModelPrediction::$roundID < 1)
                {
                    sportsmanagementModelPrediction::$roundID = 1;
                }
                if ( (int)sportsmanagementModelPrediction::$roundID > sportsmanagementModelPrediction::getProjectRounds(sportsmanagementModelPrediction::$pjID))
                {
                    sportsmanagementModelPrediction::$roundID = sportsmanagementModelPrediction::$_projectRoundsCount;
                }
                $memberProjectJokersCount = sportsmanagementModelPrediction::getMemberPredictionJokerCount($this->predictionMember->user_id,
                                                                                                           sportsmanagementModelPrediction::$pjID);
                $match_ids = NULL;
                $round_ids = NULL;
                $proteams_ids = NULL;
                /** nur spiele zum tippen ? */
                if ( $this->config['use_pred_select_matches'] )
                {
                    $match_ids = $this->config['predictionmatchid'];
                }
                /** nur spieltage tippen ? */
                if ( $this->config['use_pred_select_rounds'] )
                {
                    $round_ids = $this->config['predictionroundid'];
                }
                /** nur bestimmte mannschaften tippen ? */
                if ( $this->config['use_pred_select_proteams'] )
                {
                    $proteams_ids = $this->config['predictionproteamid'];
                }
                
$roundResults = $this->model->getMatchesDataForPredictionEntry(    sportsmanagementModelPrediction::$predictionGameID,
                sportsmanagementModelPrediction::$pjID,
                sportsmanagementModelPrediction::$roundID,
                $this->predictionMember->user_id,
                $match_ids,
                $round_ids,
                $proteams_ids);
                ?>
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
<?php echo HTMLHelper::_('form.token'); ?>
<div class="table-responsive">
<table class="table table-responsive" >
<tr>
<?php
    if ( $this->config['use_pred_select_rounds'] )
    {
        $round_ids = $this->config['predictionroundid'];
    }
    
    
    $rounds = sportsmanagementHelper::getRoundsOptions((int)sportsmanagementModelPrediction::$pjID,'ASC',FALSE,$round_ids);

    $htmlRoundsOptions = HTMLHelper::_('select.genericlist',$rounds,'r','class="inputbox" size="1" onchange="this.form.submit();"','value','text',sportsmanagementModelPrediction::$roundID );
    echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_SUBTITLE_02',
                        '<tr><td>'.sportsmanagementModelPrediction::createProjectSelector(sportsmanagementModelPrediction::$_predictionProjectS,(int)sportsmanagementModelPrediction::$pjID).'</td></tr>',
                        '<tr> <td>'.$htmlRoundsOptions.'</td></tr>');
    ?>
</tr>
</table>
</div>
<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<?php $formName = 'predictionDoTipp'.(int)sportsmanagementModelPrediction::$pjID; ?>
<form name='<?php echo $formName; ?>' id='<?php echo $formName; ?>' method='post' onsubmit='return chkFormular()' >
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
<?php echo HTMLHelper::_('form.token'); ?>

<?php
?>

<script type='text/javascript'>
function chkFormular()
{
    var message = "";
    if ( parseInt(document.<?php echo $formName; ?>.jokerCount.value) > parseInt(document.<?php echo $formName; ?>.maxJokerCount.value) )
    {
        message+="<?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CHECK_JOKERS_COUNT'); ?>\n";
    }
    if (message==""){return true;}
    else {
        alert(message);
        return false;
    }
}
</script>

<div class="table-responsive">
<table class="<?PHP echo $this->config['table_class']; ?> table-responsive" >
<tr>
<?php
if ( $predictionProject->use_goals )
{
?>
<td>
<?php echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_GOALS' ); ?>
<input type="text" id="goals_<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>" name="goals[<?php echo $predictionProject->project_id; ?>][<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>]"
value="<?php echo 0; ?>" />
</td>
<?php        
}

if ( $predictionProject->use_penalties )
{
?>
<td>
<?php echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_PENALTIES' ); ?>
<input type="text" id="penalties_<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>" name="penalties[<?php echo $predictionProject->project_id; ?>][<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>]"
value="<?php echo 0; ?>" />
</td>
<?php        
}

if ( $predictionProject->use_cards )
{
?>
<td>
<?php echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_YELLOW_CARDS' ); ?>
<input type="text" id="yellowcards_<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>" name="yellowcards[<?php echo $predictionProject->project_id; ?>][<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>]"
value="<?php echo 0; ?>" />
</td>
<td>
<?php echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_YELLOW_RED_CARDS' ); ?>
<input type="text" id="yellowredcards_<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>" name="yellowredcards[<?php echo $predictionProject->project_id; ?>][<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>]"
value="<?php echo 0; ?>" />
</td>
<td>
<?php echo Text::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USE_RED_CARDS' ); ?>
<input type="text" id="redcards_<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>" name="redcards[<?php echo $predictionProject->project_id; ?>][<?php echo (int)sportsmanagementModelPrediction::$roundID; ?>]"
value="<?php echo 0; ?>" />
</td>
<?php        
}





?>
</tr>
<tr>
<th style='color:white ;background-color:teal;'><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_HOME_TEAM'); ?></th></th>
<th style='color:white ;background-color:teal;'><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_AWAY_TEAM'); ?></th></th>
<th style='color:white ;background-color:teal;'><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_PREDICTIONS'); ?></th></th>
<?php
    if (($predictionProject->joker) && ($predictionProject->mode==0))
    {
            ?> <th style='text-align:left;color:white; background-color:teal; '> <?php
            if ($predictionProject->joker_limit > 0)
            {
               echo $memberProjectJokersCount."/".$predictionProject->joker_limit;
            }
            else
            {
                 echo "";
            }
            ?></th><?php
                }
                ?>
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
        $dFavTeamsList[] = explode(',',$value);
    }
    
    foreach ($dFavTeamsList AS $key => $value)
    {
        $favTeamsList[$value[0]] = $value[1];
    }
    if (empty(sportsmanagementModelPrediction::$_predictionMember->champ_tipp))
    {
        sportsmanagementModelPrediction::$_predictionMember->champ_tipp = '0,0';
    }
    
    $sChampTeamsList = explode(';',sportsmanagementModelPrediction::$_predictionMember->champ_tipp);
    foreach ($sChampTeamsList AS $key => $value)
    {
        $dChampTeamsList[] = explode(',',$value);
    }
    foreach ($dChampTeamsList AS $key => $value)
    {
        $champTeamsList[$value[0]] = $value[1];
    }
    $showSaveButton=false;
    if (count($roundResults) > 0)
    {
        $prev_pred_matchdatetime = "";
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
            
            $matchTimeDate = $matchTimeDate - $closingtime;
            $tippAllowed =    ( ( $thisTimeDate < $matchTimeDate ) &&
                               ($resultHome=='-') &&
                               ($resultAway=='-') ) || (($this->allowedAdmin)&&($this->predictionMember->admintipp));
            //$tippAllowed = true;
            if (!$tippAllowed){$disabled=' disabled="disabled" ';}else{$disabled=''; $showSaveButton=true;}

            ?>
<tr class='<?php echo $class; ?>'>
<!-- <td class="td_c"> -->
<?php
    /**
     * das datum des spiels
     */
    $jdate = Factory::getDate($result->match_date);
    $jdate->setTimezone(new DateTimeZone($predictionProjectSettings->timezone));
    //echo $jdate->format('d.m.Y H:i'); //outputs 01:00:00
    $pred_matchdatetime = $jdate->format('D  d.M. Y H:i')." Uhr </br>";
    
    if ($pred_matchdatetime != $prev_pred_matchdatetime){
        ?> <td  colspan='4' style="text-align: center;font-weight:bold; " ><?php echo $pred_matchdatetime;?></td><?php
            $prev_pred_matchdatetime = $pred_matchdatetime;
            }
    ?>
<!-- </td> -->
 <tr class='<?php echo $class; ?>'><?php
    $homeName = sportsmanagementModelPrediction::getMatchTeam($result->projectteam1_id,$this->config['prediction_team_name']);
    $awayName = sportsmanagementModelPrediction::getMatchTeam($result->projectteam2_id,$this->config['prediction_team_name']);
    //                                $homeName = $this->model->getMatchTeam($result->projectteam1_id);
    //                                $awayName = $this->model->getMatchTeam($result->projectteam2_id);
    ?>
<td nowrap='nowrap' class="td_r">
<?php
    if     ((isset($favTeamsList[$predictionProject->project_id])) &&
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
        echo '<br />';
    ?>
<!-- </td> -->
<!-- <td nowrap='nowrap' class="td_c"> -->
<?php
    // clublogo oder vereinsflagge
    switch($this->config['show_logo_small'])
    //if ( $this->config['show_logo_small_overview'] == 1 ) //wir nehmen das kleine logo!
    {
        case 'logo_small':
        case 'logo_middle':
        case 'logo_big':
            $logo_home = sportsmanagementModelPrediction::getMatchTeamClubLogo($result->projectteam1_id,$this->config['show_logo_small']);
            if    (($logo_home == '') || (!file_exists($logo_home)))
            {
                $logo_home = 'images/com_sportsmanagement/database/placeholders/placeholder_small.gif';
            }
            $imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $homeName);

            
            echo sportsmanagementHelperHtml::getBootstrapModalImage('tippteaminfohome' . $result->projectteam1_id,
                                                                    $logo_home,
                                                                    $imgTitle,
                                                                    30,
                                                                    '',
                                                                    $this->modalwidth,
                                                                    $this->modalheight,
                                                                    2);
    ?>

<?PHP
    
    
    echo ' ';
    break;
    //}
    
case 'country_flag':
    //if ( $this->config['show_logo_small'] == 2 )
    //                                {
    $country_home = sportsmanagementModelPrediction::getMatchTeamClubFlag($result->projectteam1_id);
    echo JSMCountries::getCountryFlag($country_home);
    break;
    }
    ?>
</td>
<td nowrap='nowrap' class="td_c">
<?php
    //echo ' <b>' . $this->config['seperator'] . '</b> ';
    ?>
<!-- </td> -->

<!-- <td nowrap='nowrap' class="td_r"> -->

<?php
    if     ((isset($favTeamsList[$predictionProject->project_id])) &&
            ($favTeamsList[$predictionProject->project_id]==$result->projectteam2_id))
    {
        ?>
<span style='background-color:yellow; color:black; padding:2px; '><?php echo $awayName; ?></span>
<?php
    }
    else
    {
        echo $awayName;
    }
    echo '<br />';
    ?>
<!-- </td> -->
<!-- <td nowrap='nowrap' class="td_c"> -->




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
            $imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $awayName);
            echo sportsmanagementHelperHtml::getBootstrapModalImage('tippteaminfoaway' . $result->projectteam2_id,
                                                                    $logo_away,
                                                                    $imgTitle,
                                                                    30,
                                                                    '',
                                                                    $this->modalwidth,
                                                                    $this->modalheight,
                                                                    2);
		    echo ' ';
        ?>


<?PHP

    ?>
<!-- </div> -->

<?PHP

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

<!-- <td nowrap='nowrap' class="td_l"> -->
<?php
    /*if     ((isset($favTeamsList[$predictionProject->project_id])) &&
            ($favTeamsList[$predictionProject->project_id] == $result->projectteam2_id))
    {*/
        ?><!-- <span style='background-color:yellow; color:black; padding:2px; '><?php echo $awayName; ?></span> -->
<?php
    //}
    //else
   // {
        //echo $awayName;
    //}
    ?>
<!-- </td> -->
<!-- @ShortBrother <td class="td_c"> -->
<!-- <td style="text-align: center;"> -->
<?php
    //echo $resultHome . $this->config['seperator'] . $resultAway;
    ?>
<!-- </td> -->

<td class="td_c">
<?php
    echo '<input type="hidden" name="cids[' . $predictionProject->project_id . '][]"
    value="' . $result->id . '" />';
    echo '<input type="hidden"
    name="prids[' . $predictionProject->project_id . '][' . $result->id . ']"
    value="' . $result->prid . '" />';
    // welcher tippmodus
    if ( $predictionProject->mode == 0 )    // Tipp in normal mode
    {
        echo $this->createStandardTippSelect($result->tipp_home,
                                             $result->tipp_away,
                                             $result->tipp,
                                             $predictionProject->project_id,
                                             $result->id,
                                             $this->config['seperator'],
                                             $tippAllowed);
    }
    else    // Tipp in toto mode
    {
        echo $this->createTotoTippSelect($result->tipp_home,
                                         $result->tipp_away,
                                         $result->tipp,
                                         $predictionProject->project_id,
                                         $result->id,
                                         $tippAllowed);
    }
    
    ?>
</td>
<?php
    if ( ($predictionProject->joker) && ( $predictionProject->mode == 0 ) )
    {
        ?>
<td class="td_c">
<input    type='checkbox' id='cbj_<?php echo $result->id; ?>'
name='jokers[<?php echo $predictionProject->project_id; ?>][<?php echo $result->id; ?>]'
value='<?php echo $result->joker; ?>' <?php
    if (isset($result->joker) && ($result->joker=='1')){echo 'checked="checked" ';}
    echo $disabled; ?> onchange='    if(this.checked)
{
    document.<?php echo $formName; ?>.jokerCount.value++;
    if ( parseInt(document.<?php echo $formName; ?>.jokerCount.value) > parseInt(document.<?php echo $formName; ?>.maxJokerCount.value) )
    {
        alert("<?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAX_JOKER_WARNING'); ?>");
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
    ?><input    type='hidden' id='cbj_<?php echo $result->id; ?>'
    name='jokers[<?php echo $predictionProject->project_id; ?>][<?php echo $result->id; ?>]'
    value='<?php echo $result->joker; ?>' /><?php
}
?>
</td><?php
    }
    ?>


<!-- <td class="td_c"> --> <?php
    /*if ((!$tippAllowed) || (($this->allowedAdmin)&&($this->predictionMember->admintipp)))
    {
        $points = sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
        $totalPoints = $totalPoints+$points;
        echo $points;
    }
    else
    {*/
        ?> <!-- &nbsp; --> <?php
           // }
            ?>
<!-- </td> -->
<!-- </tr> -->
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
<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_HOME_WIN',$percentageH,$homeCount);?></span><br />
<span style='color:<?php echo $this->config['color_draw']; ?>; '>
<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_DRAW',$percentageD,$drawCount);?></span><br />
<span style='color:<?php echo $this->config['color_guest_win']; ?>; '>
<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_AWAY_WIN',$percentageA,$awayCount); ?></span>
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
<b><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NO_POSSIBLE_PREDICTIONS'); ?></b>
</td>
<?php
    }
    else
    {
        ?>
<!-- <td colspan='6'>&nbsp;</td> -->
<td colspan='2'>&nbsp;</td>
<td class="td_c">
<?php
    if (count($roundResults) > 0)
    {
        if ($showSaveButton)
        {
            ?><!-- <input type='submit' name='addtipp' value='<?php echo Text::_('JSAVE'); ?>' class='button' /> --> <?php
                ?><input type='submit' name='addtipp'  style='color:white; background-color:teal; font-weight:bold;'  value='<?php echo Text::_('JSAVE'); ?>' class='button' /><?php
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
<!-- <td class="td_c"><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_TOTAL_POINTS_COUNT',$totalPoints); ?></td> -->
<?php
    }
    ?>
</tr>
</table>
</div>
<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<br />
<?php
    //            if (($this->config['show_help']==1)||($this->config['show_help']==2))
    //            {
    //                echo $this->model->createHelptText($predictionProject->mode);
    //            }
    }
    }
    }
    ?><br />
