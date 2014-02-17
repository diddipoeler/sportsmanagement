<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
?>

<!--[if IE]>
   <style>
      .rotate_text
      {
         writing-mode: tb-rl;
         filter: flipH() flipV();
      }
      .rotated_cell
      {
         height:400px;
         background-color: grey;
         color: white;
         padding-bottom: 3px;
         padding-left: 5px;
         padding-right: 5px;
         white-space:nowrap; 
         vertical-align:bottom
      }
   </style>
<![endif]-->

<!--[if !IE]><!-->
<style>  
.rotate_text
      {
         text-align: center;
                vertical-align: middle;
                width: 20px;
                margin: 0px;
                padding: 0px;
                padding-left: 3px;
                padding-right: 3px;
                padding-top: 10px;
                white-space: nowrap;
                -webkit-transform: rotate(-90deg); 
                -moz-transform: rotate(-90deg); 
                -o-transform: rotate(-90deg);
      }      

      .rotated_cell
      {
         height:400px;
         background-color: ;
         color: white;
         padding-bottom: 3px;
         padding-left: 5px;
         padding-right: 5px;
         white-space:nowrap; 
         vertical-align:bottom
      }
   </style>
<!--<![endif]--> 

<div>
 <?php

	#$this->config['highlight_fav_team'] = 1;
	#$this->project->fav_team_text_color = "#FFFFFF";
	$division_id = $this->divisionid;
	$matrix = '<table class="matrix">';
	$k = 1;
	$crosstable_icons_horizontal = (isset ($this->config['crosstable_icons_horizontal'])) ? $this->config['crosstable_icons_horizontal'] : 0;
	$crosstable_icons_vertical = (isset ($this->config['crosstable_icons_vertical'])) ? $this->config['crosstable_icons_vertical'] : 0;

	$k_r = 0; // count rows
	foreach ($this->teams as $team_row_id => $team_row) {
		if ($k_r == 0) // Header rows
			{
			$matrix .= '<tr class="sectiontableheader">';
			//write the first row
			if ($crosstable_icons_horizontal) {
				$matrix .= '<th class="headerspacer">&nbsp;</th>';
			} else {
				$matrix .= '<th class="headerspacer">&nbsp;</th>';
			}

			foreach ($this->teams as $team_row_header) {
				$title = JText :: _('COM_SPORTSMANAGEMENT_MATRIX_CLUB_PAGE_LINK') . ' ' . $team_row_header->name;
				$link = sportsmanagementHelperRoute :: getClubInfoRoute($this->project->id, $team_row_header->club_id);
				$desc = $team_row_header->short_name;
				if ($crosstable_icons_horizontal) // icons at the top of matrix
					{
					$picture = $team_row_header->logo_small;
					$desc = sportsmanagementHelper::getPictureThumb($picture, $title,0,0,3);
				}
				if ($this->config['link_teams'] == 1) {
					$header = '<th class="rotated_cell"><div class="rotate_text">';
					$header .= JHTML :: link($link, $desc);
					$header .= '</div></th>';
					$matrix .= $header;
				} else {
					$header = '<th class="rotated_cell"<div class="rotate_text">>';
					$header .= $desc;
					$header .= '</div></th>';
					$matrix .= $header;
				}
			}
			$matrix .= '</tr>';
		}

		$class = ($k_r % 2 == 0) ? $this->config['style_class1'] : $this->config['style_class2'];
		$trow = $team_row;
		$matrix .= '<tr class="' . $class . '">';
		$k_c = 0; //count columns

		foreach ($this->teams as $team_col_id => $team_col) {
			if ($k_c == 0) // Header columns
				{
				$title = JText :: _('COM_SPORTSMANAGEMENT_MATRIX_PLAYERS_PAGE_LINK') . ' ' . $trow->name;
				$link = sportsmanagementHelperRoute :: getPlayersRoute($this->project->id, $trow->id);
				$desc = $trow->short_name;

				if ($crosstable_icons_vertical) // icons on the left side of matrix
					{
					$picture = $trow->logo_small;
					$desc = sportsmanagementHelper::getPictureThumb($picture, $title,0,0,3);
				}
				if ($this->config['link_teams'] == 1) {
					$tValue = '<th class="teamsleft">';
					$tValue .= JHTML :: link($link, $desc);
					$tValue .= '</th>';
					$matrix .= $tValue;
				} else {
					$tValue = '<th class="teamsleft">';
					$tValue .= $desc;
					$tValue .= '</th>';
					$matrix .= $tValue;
				}
			}

			$tcol = $team_col;
			$match_result = '&nbsp;';

			// find the corresponding game
			$Allresults = '';
			foreach ($this->results as $result) {
				if($team_row->division_id != $division_id) continue;
				if (($result->projectteam1_id == $team_row->projectteamid) && ($result->projectteam2_id == $team_col->projectteamid)) {
					$ResultType = '';
					if ($result->decision == 0) {
						$e1 = $result->e1;
						$e2 = $result->e2;
						
						if($e1 > $e2) {
							$e1 = '<span style="color:'.$this->config['color_win'].'">'.$e1.'</span>';
							$e2 = '<span style="color:'.$this->config['color_win'].'">'.$e2.'</span>';
						} else if ($e1 == $e2) {
							$e1 = '<span style="color:'.$this->config['color_draw'].'">'.$e1.'</span>';
							$e2 = '<span style="color:'.$this->config['color_draw'].'">'.$e2.'</span>';
						} else if ($e1 < $e2) {
							$e1 = '<span style="color:'.$this->config['color_loss'].'">'.$e1.'</span>';
							$e2 = '<span style="color:'.$this->config['color_loss'].'">'.$e2.'</span>';
						}
						
						switch ($result->rtype) {
								case 1 : // Overtime
									$ResultType = ' ('.JText::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME');
                                    $ResultType .= ')';
									break;

								case 2 : // Shootout
									$ResultType = ' ('.JText::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT');
                                    $ResultType .= ')';
									break;

								case 0 :
									break;

							} 
					} else {
						$e1 = $result->v1;
						$e2 = $result->v2;
						if (!isset ($result->v1)) {
							$e1 = 'X';
						}
						if (!isset ($result->v2)) {
							$e2 = 'X';
						}
					}
					$showMatchReportLink = false;

					if ($result->show_report == 1 || $this->config['force_link_report'] == 1) {
						$showMatchReportLink = true;
					}
					if ($result->show_report == 0 && $e1 == "" && $e2 == "")
						$showMatchReportLink = true;
					if ($showMatchReportLink) {
						//if ((($this->config['force_link_report'] == 1) && ($result->show_report == 1) && ($e1 != "") && ($e2 != ""))) {
						// result with matchreport
						$title = "";
						$arrayString = array ();
						$link = sportsmanagementHelperRoute::getMatchReportRoute($this->project->id, $result->id);
						if (($e1 != "") && ($e2 != "")) {
							$colorStr = "color:" . $this->project->fav_team_text_color . ";";
							$bgColorStr = "background-color:" . $this->project->fav_team_color . ";";

							if (($this->config['highlight_fav_team'] != 2) || (!in_array($team_row->id, $this->favteams) && !in_array($team_col->id, $this->favteams))) {
								#$resultStr = str_replace( "%TEAMHOME%",
								#                           $this->teams[$result->projectteam1_id]->name,
								#                           JText::_( 'COM_SPORTSMANAGEMENT_STANDARD_MATCH_REPORT_FORM' ) );
								#$title = str_replace( "%TEAMGUEST%", $this->teams[$result->projectteam2_id]->name, $title );
								$resultStr = $e1 . $this->overallconfig['seperator'] . $e2 . $ResultType;
								if (($this->config['highlight_fav_team'] > 0) && ($this->project->fav_team_text_color != "") && (in_array($team_row->id, $this->favteams) || in_array($team_col->id, $this->favteams))) {
									$arrayString = array (
										"style" => $colorStr . $bgColorStr
									);
								} else {
									$arrayString = "";
								}
							} else {
								$resultStr = "";
								$resultStr .= "&nbsp;" . $e1 . $this->overallconfig['seperator'] . $e2 . $ResultType . "&nbsp;" ;
								$arrayString = array (
									"style" => $colorStr . $bgColorStr
								);
							}
							$match_result = JHTML :: link($link, $resultStr, $arrayString);
						} else {
							switch ($this->config['which_link']) {
								case 1 : // Link to Next Match page
									$link = sportsmanagementHelperRoute :: getNextMatchRoute($this->project->id, $result->id);
									//FIXME
									// $title = str_replace( "%TEAMHOME%",
									//                       $this->teams[$result->projectteam1_id]->name,
									//                       JText::_( 'COM_SPORTSMANAGEMENT_FORCED_MATCH_REPORT_NEXTPAGE_FORM' ) );
									$title = str_replace("%TEAMGUEST%", $this->teams[$result->projectteam2_id]->name, $title);
									break;

								case 2 : // Link to Match report
									$title = str_replace("%TEAMHOME%", $this->teams[$result->projectteam1_id]->name, JText :: _('COM_SPORTSMANAGEMENT_FORCED_MATCH_REPORT_FORM'));
									$title = str_replace("%TEAMGUEST%", $this->teams[$result->projectteam2_id]->name, $title);
									break;

								default :
									break;

							}
							if($result->cancel) {
								$picture = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif';
								$title = $result->cancel_reason;
								$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16,16, 99);
								$match_result = JHTML::link($link, $desc);
								$new_match = "";
								if($result->new_match_id > 0) {
									$link = sportsmanagementHelperRoute::getNextMatchRoute($this->project->id, $result->new_match_id);
									$picture = 'media/com_sportsmanagement/jl_images/bullet_black.png';
									$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16,16, 99);
									$new_match = JHTML::link($link, $desc);
								} 
								$match_result .= $new_match;
							} else {
								$picture = 'media/com_sportsmanagement/jl_images/bullet_black.png';
								$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16,16, 99);
								$match_result = JHTML :: link($link, $desc);
							}
						}
					}
					elseif (($e1 != "") && ($e2 != "")) {
						// result without match report
						if (($this->config['highlight_fav_team'] != 2) || (!in_array($team_row->id, $this->favteams)) && (!in_array($team_col->id, $this->favteams))) {
							$resultStr = $e1 . $this->overallconfig['seperator'] . $e2 . $ResultType;
						} else {
							$resultStr = "";
							$resultStr .= "<span style='color:" . $this->project->fav_team_text_color . ";'>";
							$resultStr .= "<span style='background-color:" . $this->project->fav_team_color . ";'>";
							$resultStr .= "&nbsp;" . $e1 . $this->overallconfig['seperator'] . $e2 . $ResultType . "&nbsp;";
							$resultStr .= "</span>";
							$resultStr .= "</span>";
						}
						$match_result = $resultStr;
					} else {
						// Any result available so "bullet_black.png" is shown with a link to the gameday of the match
						$link = sportsmanagementHelperRoute :: getResultsRoute($this->project->id, $result->roundid);
						$title = str_replace("%NR_OF_MATCHDAY%", $result->roundcode, JText :: _('COM_SPORTSMANAGEMENT_MATCHDAY_FORM'));
						$picture = 'media/com_sportsmanagement/jl_images/bullet_black.png';
						$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16,16, 99);
						if (($this->config['highlight_fav_team'] != 2) || (!in_array($team_row->id, $this->favteams)) && (!in_array($team_col->id, $this->favteams))) {
							$spanStartStr = "";
							$spanEndStr = "";
						} else {
							$spanStartStr = "";
							$spanStartStr .= "<span style='color:" . $this->project->fav_team_text_color . ";'>";
							$spanStartStr .= "<span style='background-color:" . $this->project->fav_team_color . ";'>";
							$spanStartStr .= "&nbsp;";

							$spanEndStr = "&nbsp;";
							$resultStr .= "</span>";
							$resultStr .= "</span>";
						}
						$match_result = $spanStartStr . JHTML :: link($link, $desc) . $spanEndStr;
					}
					//Don’t break, allow for multiple results
					if ($Allresults == '') {
						$Allresults = $match_result;
					} else {
						$Allresults .= '<br>' . $match_result;
					}
				}
			}

			$value = "";

			if ($k_r == $k_c) {
				if (($this->config['highlight_fav_team'] == 1) && (in_array($trow->team_id, $this->favteams) || in_array($tcol->team_id, $this->favteams))) {
					$dummy = '<td class="result" style="';
					$dummy .= ' color:' . $this->project->fav_team_text_color . ';';
					$dummy .= ' background-color:' . $this->project->fav_team_color . ';';
					$dummy .= '">';
				} else {
					$dummy = '<td style="text-align:center; vertical-align:middle; ">';
				}
				$value = $dummy;
				$title = '' ;
				$picture = 'media/com_sportsmanagement/jl_images/bullet_red.png';
				$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16,16, 99);
				$value .= $desc; 
			} else {
				if (($this->config['highlight_fav_team'] > 0) && (in_array($trow->team_id, $this->favteams) || in_array($tcol->team_id, $this->favteams))) {
					if ($this->config['highlight_fav_team'] == 1) {
						$dummy = '<td class="result" style="';
						$dummy .= ' color:' . $this->project->fav_team_text_color . ';';
						$dummy .= ' background-color:' . $this->project->fav_team_color . ';';
						$dummy .= '"  title="';
					} else {
						$dummy = '<td class="result" title="';
					}
				} else {
					$dummy = '<td class="result" title="';
				}
				$value = $dummy;
				$value .= $trow->name . ' - ' . $tcol->name . '">' . $Allresults . '</td>';
			}
			$matrix .= $value;
			$k_c++;
		}
		$k_r++;
		$matrix .= "</tr>";
	}
	$matrix .= '</table>';
	echo $matrix;
?>
</div>

