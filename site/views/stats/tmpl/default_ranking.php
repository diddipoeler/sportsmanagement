<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

    <table class="<?php echo $this->config['ranking_table_class'];?>">
    <tbody>
	<tr>
	    <td class="contentheading"><?php echo JText::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING'); ?></td>
	</tr>
    </tbody>
    </table>

        <br />
        <div style="<?php // echo $show_att_ranking;?>float:left;width:96%;clear:both;margin:0 0 25px 0;">
        <table class="<?php echo $this->config['ranking_table_class'];?>">

			<tr class="sectiontableheader">
                <th style="width:6px;"></th>
                <th style="width:25%;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(JText::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_TEAM'), "name", null, "ASC");
        ?>
                </th>
                <th style="width:3%;text-align:center;">
                <?php
        echo JText::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_MATCHES');
        ?>
                </th>
                <th style="width:16%;text-align:right;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(JText::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_TOTAL'), "totalattend", null);
        ?>
                </th>
                <th style="width:16%;text-align:right;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(JText::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_AVG'), "avgattend", null);
        ?>
                </th>
                <th style="width:19%;text-align:right;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(JText::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_CAPACITY'), "capacity", null);
        ?>
                </th>
                <th style="width:20%;text-align:right;">
                <?php
        sportsmanagementHelperHtml::printColumnHeadingSort(JText::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_UTILISATION'), "utilisation", null);
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


