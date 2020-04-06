<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_rankingrows.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage rankingalltime
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
HTMLHelper::_('behavior.tooltip');

$current  = $this->current;
$previous = $this->previousRanking[$this->division];
$config   = $this->tableconfig;
$counter = 1;
$k = 0;
$j = 0;
$temprank = 0;
$columns = explode(",", $config['ordered_columns']);

foreach( $current as $ptid => $team )
{
    $team->team = $this->teams[$ptid];

    /**
*
 * Table colors
*/
    $color = "";

    if (isset($this->colors[$j]["from"]) && $counter == $this->colors[$j]["from"] ) {
        $color = $this->colors[$j]["color"];
    }

    if (isset($this->colors[$j]["from"]) && isset($this->colors[$j]["to"])
        && ( $counter > $this->colors[$j]["from"] && $counter <= $this->colors[$j]["to"] )
    ) {
        $color = $this->colors[$j]["color"];
    }

    if (isset($this->colors[$j]["to"]) && $counter == $this->colors[$j]["to"] ) {
        $j++;
    }

    /**
*
 * Favorite Team
*/
    $format = "%s";
    $favStyle = '';
    if (in_array($team->team->id, explode(",", $this->project->fav_team)) && $this->project->fav_team_highlight_type == 1 ) {
        if(trim($this->project->fav_team_color) != "" ) {
            $color = trim($this->project->fav_team_color);
        }
        $format = "%s";
        $favStyle = ' style="';
        $favStyle .= ($this->project->fav_team_text_bold != '') ? 'font-weight:bold;' : '';
        $favStyle .= (trim($this->project->fav_team_text_color) != '') ? 'color:'.trim($this->project->fav_team_text_color).';' : '';

        if ($favStyle != ' style="') {
            $favStyle .= '"';
        }
        else
        {
            $favStyle = '';
        }
    }
    echo "\n\n";
    echo '<tr class=""' . $favStyle . '>';
    echo "\n";

    /**
*
 * rank row
*/
    echo '<td class="rankingrow_rank" ';
    if($color != '') {
        echo ' style="background-color: ' . $color . '"';
    }
    echo ' align="right" nowrap="nowrap">';

    if ($team->rank != $temprank ) {
        printf($format, $team->rank);
    }
    else
    {
        echo "-";
    }

    echo '</td>';
    echo "\n";

    /**
*
 * Last rank (image)
*/
    echo '<td class="rankingrow_lastrankimg" ';
    if($color != '' && $config['use_background_row_color']) {
        echo " style='background-color: " . $color . "'";
    }
    echo ">";
    echo sportsmanagementHelperHtml::getLastRankImg($team, $previous, $ptid);
    echo '</td>';
    echo "\n";

    /**
*
 * Last rank (number)
*/
    echo '<td class="rankingrow_lastrank" nowrap="nowrap" ';
    if($color != '' && $config['use_background_row_color']) {
        echo 'style="background-color:' . $color . '"';
    }
    echo '>';
    if (( $this->tableconfig['last_ranking'] ) && ( isset($previous[$ptid]->rank) ) ) {
        echo "(" . $previous[$ptid]->rank . ")";
    }
    echo '</span>';
    echo '</td>';
    echo "\n";

    /**
*
 * logo - jersey
*/
    if ($config['show_logo_small_table'] != "no_logo" ) {
        echo '<td class="rankingrow_logo"';
        if($color != '' && $config['use_background_row_color']) {
            echo ' style="background-color: ' . $color . '"';
        }
        echo ">";

        if ($config['show_logo_small_table'] == "country_flag" ) {
            sportsmanagementHelper::showClubIcon($team->team, 2);
        }
        elseif ($config['show_logo_small_table'] == "logo_small_country_flag" ) {
            echo sportsmanagementHelper::getPictureThumb(
                $team->team->logo_small,
                $team->team->name,
                $config['team_picture_width'],
                $config['team_picture_height'], 3
            ).' ';
                    sportsmanagementHelper::showClubIcon($team->team, 2);
        }
        elseif ($config['show_logo_small_table'] == "country_flag_logo_small" ) {
            sportsmanagementHelper::showClubIcon($team->team, 2);
            echo ' '.sportsmanagementHelper::getPictureThumb(
                $team->team->logo_small,
                $team->team->name,
                $config['team_picture_width'],
                $config['team_picture_height'], 3
            );
        }
        else
        {
            $pic = $config['show_logo_small_table'];
            switch ($pic)
            {
            case 'logo_small';
                echo HTMLHelper::image($team->team->$pic, $imgTitle, array('title' => $team->team->name,'width' => '20' ));
                break;
            case 'logo_middle';
                echo HTMLHelper::image($team->team->$pic, $imgTitle, array('title' => $team->team->name,'width' => '20' ));
                break;
            case 'logo_big';
                echo HTMLHelper::image($team->team->$pic, $imgTitle, array('title' => $team->team->name,'width' => '20' ));
                break;
              
            }
                
        }

        echo '</td>';
        echo "\n";
    }

    /**
*
 * Team name
*/
    echo '<td class="rankingrow_teamname" nowrap="nowrap"';
    if($color != '' && $config['use_background_row_color']) {
        echo ' style="background-color: ' . $color . '"';
    }
    echo ">";
    $isFavTeam = in_array($team->team->id, explode(",", $this->project->fav_team));
    $config['highlight_fav'] = $isFavTeam;
    echo sportsmanagementHelper::formatTeamName($team->team, 'tr' . $team->team->id, $config, $isFavTeam, null, $this->cfg_which_database);
    echo ' ('.$team->team->unique_id.')';
    echo '</td>';
    echo "\n";

    /**
*
 * START OPTIONAL COLUMNS DISPLAY
*/
    foreach ( $columns AS $c )
    {
        //	   $routeparameter = array();
        //       $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        //       $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        //       $routeparameter['p'] = $this->project->slug;
        //       $routeparameter['tid'] = $team->team->team_slug;
        //       $routeparameter['division'] = 0;
        //       $routeparameter['mode'] = 0;
        //       $routeparameter['ptid'] = $team->ptid_slug;
      
        switch ( trim(strtoupper($c)) )
        {
        case 'PLAYED':
            echo '<td class="rankingrow_played" ';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->cnt_matches);
            echo '</td>';
            echo "\n";
            break;

        case 'WINS':
            echo '<td class="rankingrow" ';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            if (( $config['show_wdl_teamplan_link'])==1) {
                //				    $routeparameter['mode'] = 1;
                //                    $teamplan_link  = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan',$routeparameter);
                //					echo HTMLHelper::link($teamplan_link, $team->cnt_won);
                    printf($format, $team->cnt_won);
            }
            else
            {
                printf($format, $team->cnt_won);
            }
            echo '</td>';
            echo "\n";
            break;

        case 'TIES':
            echo '<td class="rankingrow" ';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            if (( $config['show_wdl_teamplan_link'])==1) {
                //				    $routeparameter['mode'] = 2;
                //                    $teamplan_link  = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan',$routeparameter);
                //					echo HTMLHelper::link($teamplan_link, $team->cnt_draw);
                    printf($format, $team->cnt_draw);
            }
            else
            {
                printf($format, $team->cnt_draw);
            }
            echo '</td>';
            echo "\n";
            break;

        case 'LOSSES':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            if (( $config['show_wdl_teamplan_link'])==1) {
                //				    $routeparameter['mode'] = 3;
                //                    $teamplan_link  = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan',$routeparameter);
                //					echo HTMLHelper::link($teamplan_link, $team->cnt_lost);
                    printf($format, $team->cnt_lost);
            }
            else
            {
                printf($format, $team->cnt_lost);
            }
            echo '</td>';
            echo "\n";
            break;

        case 'WOT':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->cnt_wot);
            echo '</td>';
            echo "\n";
            break;

        case 'WSO':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->cnt_wso);
            echo '</td>';
            echo "\n";
            break;

        case 'LOT':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->cnt_lot);
            echo '</td>';
            echo "\n";
            break;
                  
        case 'LSO':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->cnt_lso);
            echo '</td>';
            echo "\n";
            break;
                  
        case 'WINPCT':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, sprintf("%.3F", ($team->winpct() )));
            echo '</td>';
            echo "\n";
            break;

        case 'GB':
            //GB calculation, store wins and loss count of the team in first place
            if ($team->rank == 1 ) {
                $ref_won = $team->cnt_won;
                $ref_lost = $team->cnt_lost;
            }
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, round(( ( $ref_won - $team->cnt_won ) - ( $ref_lost - $team->cnt_lost ) ) / 2, 1));
            echo '</td>';
            echo "\n";

            break;

        case 'LEGS':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, sprintf("%s:%s", $team->sum_team1_legs, $team->sum_team2_legs));
            echo '</td>';
            echo "\n";
            break;
                  
        case 'LEGS_DIFF':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->diff_team_legs);
            echo '</td>';
            echo "\n";
            break;

        case 'LEGS_RATIO':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            $legsratio=round(($team->legsRatio()), 2);
            printf($format, $legsratio);
            echo '</td>';
            echo "\n";
            break;
                  
        case 'SCOREFOR':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, sprintf("%s", $team->sum_team1_result));
            echo '</td>';
            echo "\n";
            break;

        case 'SCOREAGAINST':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, sprintf("%s", $team->sum_team2_result));
            echo '</td>';
            echo "\n";
            break;
                  
        case 'SCOREPCT':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            $scorepct=round(($team->scorePct()), 2);
            printf($format, $scorepct);
                  
            echo '</td>';
            echo "\n";
            break;
                  
        case 'RESULTS':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, sprintf("%s" . ":" . "%s", $team->sum_team1_result, $team->sum_team2_result));
            echo '</td>';
            echo "\n";
            break;

        case 'DIFF':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->diff_team_results);
            echo '</td>';
            echo "\n";
            break;

        case 'POINTS':
            echo '<td class="rankingrow_points"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->getPoints());
            echo '</td>';
            echo "\n";
            break;

        case 'NEGPOINTS':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->neg_points);
            echo '</td>';
            echo "\n";
            break;

        case 'OLDNEGPOINTS':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, sprintf("%s" . ":" . "%s", $team->getPoints(), $team->neg_points));
            echo '</td>';
            echo "\n";
            break;
                  
        case 'POINTS_RATIO':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            $pointsratio=round(($team->pointsRatio()), 2);
            printf($format, $pointsratio);
            echo '</td>';
            echo "\n";
            break;
                  
        case 'BONUS':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->bonus_points);
            echo '</td>';
            echo "\n";
            break;

        case 'START':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            if ((($team->team->start_points)!=0) AND (( $config['show_manipulations'])==1)) {
                $toolTipTitle    = Text::_('COM_SPORTSMANAGEMENT_START');
                $toolTipText    = $team->team->reason;
                echo '<span class="hasTip" title="'.$toolTipTitle.' :: '.$toolTipText.'">'. printf($format, $team->team->start_points). '</span>';
            }
            else
            {
                printf($format, $team->team->start_points);
            }
            echo '</td>';
            echo "\n";
            break;

        case 'QUOT':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            $pointsquot = number_format($team->pointsQuot(), 3, ",", ".");
            printf($format, $pointsquot);
            echo '</td>';
            echo "\n";
            break;

        case 'TADMIN':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            printf($format, $team->team->username);
            echo '</td>';
            echo "\n";
            break;

        case 'GFA':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            $gfa=round(($team->getGFA()), 2);
            printf($format, $gfa);

            echo '</td>';
            echo "\n";
            break;

        case 'GAA':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            $gaa=round(($team->getGAA()), 2);
            printf($format, $gaa);

            echo '</td>';
            echo "\n";
            break;

        case 'PPG':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            $gaa=round(($team->getPPG()), 2);
            printf($format, $gaa);

            echo '</td>';
            echo "\n";
            break;

        case 'PPP':
            echo '<td class="rankingrow"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            $gaa=round(($team->getPPP()), 2);
            printf($format, $gaa);

            echo '</td>';
            echo "\n";
            break;

        case 'LASTGAMES':
            echo '<td class="rankingrow lastgames"';
            if($color != '' && $config['use_background_row_color']) {
                echo 'style="background-color:' . $color . '"';
            }
            echo '>';
            foreach ($this->previousgames[$ptid] as $g)
            {
                $txt = $this->teams[$g->projectteam1_id]->name.' [ '. $g->team1_result . ' - '. $g->team2_result . ' ] '.$this->teams[$g->projectteam2_id]->name;
                $attribs = array('title' => $txt);
                if (!$img = sportsmanagementHelperHtml::getThumbUpDownImg($g, $ptid, $attribs)) {
                    continue;
                }
                switch (sportsmanagementHelper::getTeamMatchResult($g, $ptid))
                {
                case -1:
                    $attr = array('class' => 'thumblost');
                    break;
                case 0:
                    $attr = array('class' => 'thumbdraw');
                    break;
                case 1:
                    $attr = array('class' => 'thumbwon');
                    break;
                }

                $url = Route::_(sportsmanagementHelperRoute::getMatchReportRoute($g->project_slug, $g->slug));
                echo HTMLHelper::link($url, $img, $attr);
            }
            echo '</td>';
            echo "\n";
            break;
        }
    }

    echo '</tr>';
    echo "\n";
    $k = 1 - $k;
    $counter++;
    $temprank = $team->rank;
}
