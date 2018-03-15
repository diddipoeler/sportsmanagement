<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version                1.0.05
 * @file                   agegroup.php
 * @author                 diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright              Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license                This file is part of SportsManagement.
 *
 * SportsManagement is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SportsManagement is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von SportsManagement.
 *
 * SportsManagement ist Freie Software: Sie können es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
 * veröffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
 * OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License für weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */

defined('_JEXEC') or die(JText::_('Restricted access'));
JHtml::_('behavior.tooltip');

$mainframe = JFactory::getApplication();

//echo '<br /><pre>~' . print_r($config,true) . '~</pre><br />';
//echo '<br /><pre>~' . print_r($config['limit'],true) . '~</pre><br />';
?>
    <!-- <a name='jl_top' id='jl_top'></a> -->
<?php
foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
//foreach ($predictionProjectS AS $predictionProject)
{
    $gotSettings = $predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id);

    //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' gotSettings<br><pre>'.print_r($gotSettings,true).'</pre>'),'');

    if ( ( ( sportsmanagementModelPrediction::$pjID == $predictionProject->project_id ) && ( $gotSettings ) ) || ( sportsmanagementModelPrediction::$pjID == 0 ) )
    {
        //$showProjectID = (count($modelpg->_predictionProjectS) > 1) ? $modelpg->pjID : $predictionProject->project_id;
        $showProjectID                         = ( count($predictionProjectS) > 1 ) ? sportsmanagementModelPrediction::$pjID : $predictionProject->project_id;
        sportsmanagementModelPrediction::$pjID = $predictionProject->project_id;
        $modelpg->predictionProject            = $predictionProject;
        $actualProjectCurrentRound             = sportsmanagementModelPrediction::getProjectSettings($predictionProject->project_id);
//        echo 'actualProjectCurrentRound <br /><pre>~' . print_r($actualProjectCurrentRound ,true) . '~</pre><br />';
//echo 'modelpg<br /><pre>~' . print_r($modelpg,true) . '~</pre><br />';
        $roundID = $actualProjectCurrentRound;
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
        <form name='resultsRoundSelector' method='post'>
            <input type='hidden' name='prediction_id' value='<?php echo (int) $predictionGame[0]->id; ?>'/>
            <input type='hidden' name='p' value='<?php echo (int) $predictionProject->project_id; ?>'/>
            <input type='hidden' name='r' value='<?php echo (int) $roundID; ?>'/>
            <input type='hidden' name='pjID' value='<?php echo (int) $showProjectID; ?>'/>
            <input type='hidden' name='task' value='selectProjectRound'/>
            <input type='hidden' name='option' value='com_sportsmanagement'/>
            <input type='hidden' name='controller' value='predictionranking'/>

            <table class="table">
                <tr>
                    <td class='sectiontableheader'>
                        <?php
                        //echo '<b>'.JText::sprintf('JL_PRED_RANK_SUBTITLE_01').'</b>';
                        if ( $config['show_project_name'] )
                        {
                            echo '<b>' . $predictionGame[0]->name . '</b>';
                        }
                        ?>
                    </td>
                    <td class='sectiontableheader' style='text-align:right; ' width='20%' nowrap='nowrap'><?php
                        if ( $config['show_project_name_selector'] )
                        {
                            echo sportsmanagementModelPrediction::createProjectSelector($modelpg->_predictionProjectS, $predictionProject->project_id, $showProjectID);
                        }

                        if ( $showProjectID > 0 )
                        {
                            if ( $config['show_tip_link_ranking_round'] )
                            {
                                echo '&nbsp;&nbsp;';
                                $routeparameter                       = array();
                                $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database', 0);
                                $routeparameter['s']                  = JRequest::getInt('s', 0);
                                $routeparameter['p']                  = $predictionProject->project_id;
                                $routeparameter['r']                  = $roundID;
                                $routeparameter['division']           = 0;
                                $routeparameter['mode']               = 0;
                                $routeparameter['order']              = '';
                                $routeparameter['layout']             = '';
                                $link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

                                $imgTitle = JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_ROUND_RESULTS_TITLE');
                                $desc     = JHtml::image('media/com_sportsmanagement/jl_images/icon-16-Matchdays.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
                                //echo JHtml::link($link,$desc,array('target' => '_blank'));
                                echo JHtml::link($link, $desc, array('target' => ''));
                            }
                            if ( $config['show_tip_ranking'] )
                            {
                                echo '&nbsp;&nbsp;';
// predictionresults/0-intern/4-test-2016-17/0/73400-1-bundesliga-2017-18/1813608-7-spieltag/0
                                if ( !$config['show_tip_ranking_round'] )
                                {
                                    $link = JSMPredictionHelperRoute::getPredictionRankingRoute($predictionGame[0]->id,
                                    $modelpg->predictionProject->project_slug, 
                                    '');
                                }
                                else
                                {
                                    $link = JSMPredictionHelperRoute::getPredictionRankingRoute($predictionGame[0]->id,
                                     $modelpg->predictionProject->project_slug,
                                     $actualProjectCurrentRound,
                                     '',
                                     0,
                                     0,0,$actualProjectCurrentRound,$actualProjectCurrentRound);
                                }


                                $imgTitle = JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_HEAD_RANKING_IMAGE_TITLE');
                                $desc     = JHtml::image('media/com_sportsmanagement/jl_images/prediction_ranking.png', $imgTitle, array('border' => 0, 'title' => $imgTitle));
                                echo JHtml::link($link, $desc, array('target' => ''));
                            }

                        }
                        ?></td>
                </tr>
            </table>
            <br/>
            <?php echo JHtml::_('form.token'); ?>
        </form>

        <?php
        if ( ( $showProjectID > 0 ) && ( $config['show_rankingnav'] ) )
        {
            $from_matchday = $modelpg->createFromMatchdayList($predictionProject->project_id);
            $to_matchday   = $modelpg->createToMatchdayList($predictionProject->project_id);
            ?>
            <form name='adminForm' id='adminForm' method='post'>
                <table class="table">
                    <tr>
                        <td><?php echo JHtml::_('select.genericlist', $lists['type'], 'type', 'class="inputbox" size="1"', 'value', 'text', $modelpg->type); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo JHtml::_('select.genericlist', $from_matchday, 'from', 'class="inputbox" size="1"', 'value', 'text', $modelpg->from); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo JHtml::_('select.genericlist', $to_matchday, 'to', 'class="inputbox" size="1"', 'value', 'text', $modelpg->to); ?></td>
                    </tr>
                    <tr>
                        <td><input type='submit' class='button' name='reload View'
                                   value='<?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_FILTER'); ?>'/></td>
                    </tr>
                </table>
                <?php echo JHtml::_('form.token'); ?>
            </form><br/>
            <?php
        }
        ?>

        <table class="table">
            <tr>
                <td class='sectiontableheader'
                    style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK'); ?></td>
                <?php
                if ( $config['show_user_icon'] )
                {
                    ?>
                    <td class='sectiontableheader'
                        style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_AVATAR'); ?></td><?php
                }
                ?>
                <td class='sectiontableheader'
                    style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_MEMBER'); ?></td>
                <?php
                if ( $config['show_tip_details'] )
                {
                    ?>
                    <td class='sectiontableheader'
                        style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_DETAILS'); ?></td><?php
                }
                ?>

                <?php

                ?>

                <td class='sectiontableheader'
                    style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_POINTS'); ?></td>
                <?php
                if ( $config['show_average_points'] )
                {
                    ?>
                    <td class='sectiontableheader'
                        style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_AVERAGE'); ?></td><?php
                }
                ?>
                <?php
                if ( $config['show_count_tips'] )
                {
                    ?>
                    <td class='sectiontableheader'
                        style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_PREDICTIONS'); ?></td><?php
                }
                ?>
                <?php
                if ( $config['show_count_joker'] )
                {
                    ?>
                    <td class='sectiontableheader'
                        style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_JOKERS'); ?></td><?php
                }
                ?>
                <?php
                if ( $config['show_count_topptips'] )
                {
                    ?>
                    <td class='sectiontableheader'
                        style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_TOPS'); ?></td><?php
                }
                ?>
                <?php
                if ( $config['show_count_difftips'] )
                {
                    ?>
                    <td class='sectiontableheader'
                        style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_MARGINS'); ?></td><?php
                }
                ?>
                <?php
                if ( $config['show_count_tendtipps'] )
                {
                    ?>
                    <td class='sectiontableheader'
                        style='text-align:center; vertical-align:top; '><?php echo JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_TENDENCIES'); ?></td><?php
                }
                ?>
            </tr>
            <?php
            if ( $config['show_debug_modus'] )
            {
                echo 'mod_sportsmanagement_top_tipper - predictionMember<br /><pre>~' . print_r($predictionMember, true) . '~</pre><br />';
            }

            $k          = 0;
            $memberList = sportsmanagementModelPrediction::getPredictionMembersList($config, $configavatar);
            // echo '<br /><pre>~' . print_r($memberList,true) . '~</pre><br />';

            $membersResultsArray = array();
            $membersDataArray    = array();

            foreach ($memberList AS $member)
            {

/**
 * wenn in der moduleinstellung der aktuelle spieltag aus dem projekt angezeigt werden soll,
 * dann soll auch dieser übergeben werden. 
 */
if ( $config['show_tip_ranking_round'] )
{ 
$memberPredictionPoints = sportsmanagementModelPrediction::getPredictionMembersResultsList($showProjectID,
$actualProjectCurrentRound,
$actualProjectCurrentRound,
$member->user_id,
sportsmanagementModelPrediction::$type);
}    
else
{
$memberPredictionPoints = sportsmanagementModelPrediction::getPredictionMembersResultsList($showProjectID,
sportsmanagementModelPrediction::$from,
sportsmanagementModelPrediction::$to,
$member->user_id,
sportsmanagementModelPrediction::$type);
}                    
                //echo '<br /><pre>~' . print_r($memberPredictionPoints,true) . '~</pre><br />';
                $predictionsCount = 0;
                $totalPoints      = 0;
                $totalTop         = 0;
                $totalDiff        = 0;
                $totalTend        = 0;
                $totalJoker       = 0;
                if ( !empty($memberPredictionPoints) )
                {
                    foreach ($memberPredictionPoints AS $memberPredictionPoint)
                    {
                        if ( ( !is_null($memberPredictionPoint->homeResult) ) ||
                            ( !is_null($memberPredictionPoint->awayResult) ) ||
                            ( !is_null($memberPredictionPoint->homeDecision) ) ||
                            ( !is_null($memberPredictionPoint->awayDecision) )
                        )
                        {
                            $predictionsCount++;
                            $result    = sportsmanagementModelPrediction::createResultsObject($memberPredictionPoint->homeResult,
                                $memberPredictionPoint->awayResult,
                                $memberPredictionPoint->prTipp,
                                $memberPredictionPoint->prHomeTipp,
                                $memberPredictionPoint->prAwayTipp,
                                $memberPredictionPoint->prJoker,
                                $memberPredictionPoint->homeDecision,
                                $memberPredictionPoint->awayDecision);
                            $newPoints = sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject, $result);
                            //if (!is_null($memberPredictionPoint->prPoints))
                            {
                                $points = $memberPredictionPoint->prPoints;
                                if ( $newPoints != $points )
                                {
                                    // this check also should be done if the result is not displayed
                                    $memberPredictionPoint = sportsmanagementModelPrediction::savePredictionPoints($memberPredictionPoint,
                                        $predictionProject,
                                        true);
                                    $points                = $newPoints;
                                }
                                $totalPoints = $totalPoints + $points;
                            }
                            if ( !is_null($memberPredictionPoint->prJoker) )
                            {
                                $totalJoker = $totalJoker + $memberPredictionPoint->prJoker;
                            }
                            if ( !is_null($memberPredictionPoint->prTop) )
                            {
                                $totalTop = $totalTop + $memberPredictionPoint->prTop;
                            }
                            if ( !is_null($memberPredictionPoint->prDiff) )
                            {
                                $totalDiff = $totalDiff + $memberPredictionPoint->prDiff;
                            }
                            if ( !is_null($memberPredictionPoint->prTend) )
                            {
                                $totalTend = $totalTend + $memberPredictionPoint->prTend;
                            }
                        }
                    }
                }

                $membersResultsArray[$member->pmID]['rank']             = 0;
                $membersResultsArray[$member->pmID]['predictionsCount'] = $predictionsCount;
                $membersResultsArray[$member->pmID]['totalPoints']      = $totalPoints;
                $membersResultsArray[$member->pmID]['totalTop']         = $totalTop;
                $membersResultsArray[$member->pmID]['totalDiff']        = $totalDiff;
                $membersResultsArray[$member->pmID]['totalTend']        = $totalTend;
                $membersResultsArray[$member->pmID]['totalJoker']       = $totalJoker;

                // check all needed output for later
                $picture    = $member->avatar;
                $playerName = $member->name;

                if ( ( ( !isset($member->avatar) ) ||
                    ( $member->avatar == '' ) ||
                    ( !file_exists($member->avatar) ) ||
                    ( ( !$member->show_profile ) && ( $predictionMember[0]->pmID != $member->pmID ) ) )
                )
                {
                    $picture = sportsmanagementHelper::getDefaultPlaceholder("player");
                }
                //tobe removed
                //$imgTitle = JText::sprintf('JL_PRED_AVATAR_OF',$member->name);
                //$output = JHtml::image($member->avatar,$imgTitle,array(' width' => 20, ' title' => $imgTitle));

                $output                                            = sportsmanagementHelper::getPictureThumb($picture, $playerName, 0, 25);
                $membersDataArray[$member->pmID]['show_user_icon'] = $output;

                if ( ( $config['show_user_link'] ) && ( ( $member->show_profile ) || ( $predictionMember[0]->pmID == $member->pmID ) ) )
                {
                    $link   = JSMPredictionHelperRoute::getPredictionMemberRoute($predictionGame[0]->id, $member->pmID);
                    $output = JHtml::link($link, $member->name);
                }
                else
                {
                    $output = $member->name;
                }
                $membersDataArray[$member->pmID]['name'] = $output;

                $imgTitle = JText::sprintf('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_SHOW_DETAILS_OF', $member->name);
                $imgFile  = JHtml::image("media/com_sportsmanagement/jl_images/zoom.png", $imgTitle, array(' title' => $imgTitle));
                $link     = JSMPredictionHelperRoute::getPredictionResultsRoute($predictionGame[0]->id, $actualProjectCurrentRound, sportsmanagementModelPrediction::$pjID);
                if ( ( $member->show_profile ) || ( $predictionMember[0]->pmID == $member->pmID ) )
                {
                    $output = JHtml::link($link, $imgFile);
                }
                else
                {
                    $output = '&nbsp;';
                }

                $membersDataArray[$member->pmID]['show_tip_details'] = $output;


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
            $computedMembersRanking = sportsmanagementModelPrediction::computeMembersRanking($membersResultsArray, $config);
            $recordCount            = count($computedMembersRanking);
            //echo '<br /><pre>~' . print_r($computedMembersRanking,true) . '~</pre><br />';

            $i = 1;
            if ( (int) $config['limit'] < 1 )
            {
                $config['limit'] = 1;
            }
            $rlimit                                = ceil($recordCount / $config['limit']);
            sportsmanagementModelPrediction::$page = ( sportsmanagementModelPrediction::$page > $rlimit ) ? $rlimit : sportsmanagementModelPrediction::$page;
            $skipMemberCount                       = ( sportsmanagementModelPrediction::$page > 0 ) ? ( ( sportsmanagementModelPrediction::$page - 1 ) * $config['limit'] ) : 0;

            foreach ($computedMembersRanking AS $key => $value)
            {
                //echo '<br /><pre>~' . print_r($value,true) . '~</pre><br />';
                if ( $i <= $skipMemberCount )
                {
                    $i++;
                    continue;
                }

                $class      = ( $k == 0 ) ? 'sectiontableentry1' : 'sectiontableentry2';
                $styleStr   = ( $predictionMember[0]->pmID == $key ) ? ' style="background-color:yellow; color:black; " ' : '';
                $class      = ( $predictionMember[0]->pmID == $key ) ? 'sectiontableentry1' : $class;
                $tdStyleStr = " style='text-align:center; vertical-align:middle; ' ";

                //$config['show_all_user']=1;
                if ( ( ( !$config['show_all_user'] ) && ( $value['predictionsCount'] > 0 ) ) ||
                    ( $config['show_all_user'] ) ||
                    ( $predictionMember[0]->pmID == $key )
                )
                {
                    ?>
                    <tr class='<?php echo $class; ?>' <?php echo $styleStr; ?> >
                        <td<?php echo $tdStyleStr; ?>><?php echo $value['rank']; ?></td>
                        <?php
                        if ( $config['show_user_icon'] )
                        {
                            ?>
                            <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['show_user_icon']; ?></td>
                            <?php
                        }
                        ?>
                        <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['name']; ?></td>
                        <?php
                        if ( $config['show_tip_details'] )
                        {
                            ?>
                            <td<?php echo $tdStyleStr; ?>><?php echo $membersDataArray[$key]['show_tip_details']; ?></td><?php
                        }


                        ?>
                        <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalPoints']; ?></td>
                        <?php
                        if ( $config['show_average_points'] )
                        {
                            ?>
                            <td<?php echo $tdStyleStr; ?>><?php
                            if ( $membersResultsArray[$key]['predictionsCount'] > 0 )
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
                        if ( $config['show_count_tips'] )
                        {
                            ?>
                            <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['predictionsCount']; ?></td><?php
                        }
                        ?>
                        <?php
                        if ( $config['show_count_joker'] )
                        {
                            ?>
                            <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalJoker']; ?></td><?php
                        }
                        ?>
                        <?php
                        if ( $config['show_count_topptips'] )
                        {
                            ?>
                            <td style='text-align:center; vertical-align:middle; '><?php echo $membersResultsArray[$key]['totalTop']; ?></td><?php
                        }
                        ?>
                        <?php
                        if ( $config['show_count_difftips'] )
                        {
                            ?>
                            <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalDiff']; ?></td><?php
                        }
                        ?>
                        <?php
                        if ( $config['show_count_tendtipps'] )
                        {
                            ?>
                            <td<?php echo $tdStyleStr; ?>><?php echo $membersResultsArray[$key]['totalTend']; ?></td><?php
                        }
                        ?>
                    </tr>
                    <?php
                    $k = ( 1 - $k );
                    $i++;
                    if ( $i > $skipMemberCount + $config['limit'] )
                    {
                        break;
                    }
                }
            }
?>

<tr>            
<?PHP            
  
            
?>
</tr>            
</table>
<?php
}
    
?>
<table class="table table-responsive">
<tr class="bg-info">    
<td class="text-center">        
<?PHP     
if ( $config['show_tip_ranking_text'] )
{
echo '&nbsp;&nbsp;';
if ( $config['show_tip_ranking_round'] )
{ 
$link = JSMPredictionHelperRoute::getPredictionRankingRoute($predictionGame[0]->id,
                                     $modelpg->predictionProject->project_slug,
                                     $actualProjectCurrentRound,
                                     '',
                                     0,
                                     0,0,$actualProjectCurrentRound,$actualProjectCurrentRound);    
}
else
{    
$link = JSMPredictionHelperRoute::getPredictionRankingRoute($predictionGame[0]->id,
 $modelpg->predictionProject->project_slug, 
 '');
}

$desc = JText::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_TIP_RANKING_TEXT');
echo JHtml::link($link, $desc, array('target' => ''));
}              
?>
</td>
</tr>            
</table>
<?php    
    
}
?>
