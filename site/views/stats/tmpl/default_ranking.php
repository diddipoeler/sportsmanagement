<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_ranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage stats
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;

?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="ranking">
    <table class="<?php echo $this->config['ranking_table_class'];?>">
    <tbody>
	<tr>
	    <td class="contentheading"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING'); ?></td>
	</tr>
    </tbody>
    </table>

        <br />

        <table class="<?php echo $this->config['ranking_table_class'];?>">

			<tr class="sectiontableheader">
                <th style="width:6px;"></th>
                <th style="width:25%;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_TEAM'), "name", null, "ASC");
        ?>
                </th>
                <th style="width:3%;text-align:center;">
                <?php
        echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_MATCHES');
        ?>
                </th>
                <th style="width:16%;text-align:right;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_TOTAL'), "totalattend", null);
        ?>
                </th>
                <th style="width:16%;text-align:right;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_AVG'), "avgattend", null);
        ?>
                </th>
                <th style="width:19%;text-align:right;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_CAPACITY'), "capacity", null);
        ?>
                </th>
                <th style="width:20%;text-align:right;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_UTILISATION'), "utilisation", null);
        ?>
                </th>
            </tr>
            <?php

                //reorder table according to criteria
                if (isset($_REQUEST['order'])) {
                    switch ($_REQUEST['order']) {
                    case 'name':
                        usort($this->attendanceranking, array("sportsmanagementModelStats", "teamNameCmp2"));
                        break;
                    case 'totalattend':
                        usort($this->attendanceranking, array("sportsmanagementModelStats", "totalattendCmp"));
                        break;
                    case 'avgattend':
                        usort($this->attendanceranking, array("sportsmanagementModelStats", "avgattendCmp"));
                        break;
                    case 'capacity':
                        usort($this->attendanceranking, array("sportsmanagementModelStats", "capacityCmp"));
                        break;
                    case 'utilisation':
                        usort($this->attendanceranking, array("sportsmanagementModelStats", "utilisationCmp"));
                        break;
                    }

                    if ($_REQUEST['dir'] == 'DESC') {
                        $this->attendanceranking = array_reverse($this->attendanceranking, false);
                    }
                }

              $k = 0;
              $favteam = explode(",", $this->project->fav_team);

                for ($i = 0, $n = count($this->attendanceranking); $i < $n; $i++) {
                    $row   = $this->attendanceranking[$i];
                    $color = '';

                    if (in_array($this->attendanceranking[$i]->teamid, $favteam)) {
                        if (trim($this->project->fav_team_color) != "") {
                            $color = 'background-color:'.$this->project->fav_team_color.';';
                        }
                    }
                ?>
            <tr
                class="<?php echo ($k == 0)? 'sectiontableentry1' : 'sectiontableentry2'; ?>">
                <td style="width:6px;text-align:right;<?php echo $color;?>"><b><?php echo $i+1;?></b></td>
                <td style="width:22%;<?php echo $color;?>"><?php echo $row->team;?></td>
                <td style="width:3%;text-align:center;<?php echo $color;?>"><?php if ($row->avgspectatorspt>0) echo round($row->sumspectatorspt / $row->avgspectatorspt);else echo '-';?></td>
                <td style="width:16%;text-align:right;<?php echo $color;?>"><?php echo $row->sumspectatorspt;?></td>
                <td style="width:15%;text-align:right;<?php echo $color;?>"><?php echo round($row->avgspectatorspt,0);?></td>
                <td style="width:19%;text-align:right;<?php echo $color;?>"><?php if ($row->capacity>0) echo $row->capacity; else echo '-';?></td>
                <td style="width:20%;text-align:right;<?php echo $color;?>"><?php if ($row->capacity>0) echo round(($row->avgspectatorspt / $row->capacity)*100)."%";else echo '-';?></td>
            </tr>
                <?php
                $k = 1 - $k;

            }
            ?>
        </table>
        </div>


