<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

 <?php
	$division_id = $this->divisionid;
	//$matrix = '<table class="matrix" border="2px">';
    $matrix = '<table class="'.$this->config['table_class'].'" border="2px">';
	$k = 1;
	$crosstable_icons_horizontal = (isset ($this->config['crosstable_icons_horizontal'])) ? $this->config['crosstable_icons_horizontal'] : 0;
	$crosstable_icons_vertical = (isset ($this->config['crosstable_icons_vertical'])) ? $this->config['crosstable_icons_vertical'] : 0;

	$k_r = 0; // count rows
	foreach ($this->teams as $team_row_id => $team_row) 
    {
		if ($k_r == 0) // Header rows
			{
			$matrix .= '<tr class="sectiontableheader">';
			//write the first row
            $matrix .= '<th class="rankheader">п/п</th>';
			if ($crosstable_icons_horizontal) {
				$matrix .= '<th class="headerspacer">&nbsp;</th>';
			} else {
				//$matrix .= '<th class="teamheader">Команда</th>';
                $matrix .= '<th class="teamheader">'.JText :: _('COM_SPORTSMANAGEMENT_MATRIX_TEAM_NAME') . '</th>';
			}
            
            $teamnumber = 1;
			foreach ($this->teams as $team_row_header) 
            {
				$title = JText :: _('COM_SPORTSMANAGEMENT_MATRIX_CLUB_PAGE_LINK') . ' ' . $team_row_header->name;
				$link = sportsmanagementHelperRoute::getClubInfoRoute($this->project->slug, $team_row_header->club_slug);
                $name = $this->config['teamnames'];
                $desc = $teamnumber;
                
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
					$header = '<th class="rotated_cell"><div class="rotate_text">';
					$header .= $desc;
					$header .= '</div></th>';
					$matrix .= $header;
				}
                $teamnumber++;
			}
			$matrix .= '</tr>';
		}

		$trow = $team_row;
		$matrix .= '<tr class="">';
		$k_c = 0; //count columns

		foreach ($this->teams as $team_col_id => $team_col) 
        {
        
            if ($k_c == 0) // Header columns
				{
				$title = JText :: _('COM_SPORTSMANAGEMENT_MATRIX_PLAYERS_PAGE_LINK') . ' ' . $trow->name;
				$routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $trow->team_slug;
       $routeparameter['ptid'] = 0;
       		$link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster',$routeparameter);
				
				//$desc = $trow->short_name;
                $name = $this->config['teamnames'];
                $desc = $trow->$name;

				if ($crosstable_icons_vertical) // icons on the left side of matrix
					{
					$picture = $trow->logo_small;
					$desc = sportsmanagementHelper::getPictureThumb($picture, $title,0,0,3);
				}
            
                    $tValue = '<th class="">';
					$tValue .= $k_r + 1;
					$tValue .= '</th>';
					$matrix .= $tValue;
                                                           
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
            // darstelung russisch
            
            	// erste runde
                $ResultType = '';
					if ( isset($team_row->first[$team_col_id]) && $team_row->first[$team_col_id]->decision == 0 ) 
                    {
                        
                        if ( isset($team_row->first[$team_col_id]) )
                        {
                        $e1 = $team_row->first[$team_col_id]->e1;
			$e2 = $team_row->first[$team_col_id]->e2;
						}
                        else
                        {
                        $e1 = '';
						$e2 = '';    
                        }
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
						
                        if ( isset($team_row->first[$team_col_id]) )
                        {
						switch ($team_row->first[$team_col_id]->rtype) {
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
                            }
					} 
                    else 
                    {
                        if ( isset($team_row->first[$team_col_id]) )
                        {
						$e1 = $team_row->first[$team_col_id]->v1;
						$e2 = $team_row->first[$team_col_id]->v2;
                        }
                        else
                        {
                        $e1 = '';
						$e2 = '';    
                        }
						if (!isset ($team_row->first[$team_col_id]->v1)) {
							$e1 = 'X';
						}
						if (!isset ($team_row->first[$team_col_id]->v2)) {
							$e2 = 'X';
						}
					}
					$showMatchReportLink = false;

					if ( isset($team_row->first[$team_col_id]) && ( $team_row->first[$team_col_id]->show_report == 1 || $this->config['force_link_report'] == 1 ) ) {
						$showMatchReportLink = true;
					}
                    
                    if ( isset($team_row->first[$team_col_id]) )
                    {
					if ($team_row->first[$team_col_id]->show_report == 0 && $e1 == "" && $e2 == "")
						$showMatchReportLink = true;
                        }
					if ($showMatchReportLink) {
						//if ((($this->config['force_link_report'] == 1) && ($result->show_report == 1) && ($e1 != "") && ($e2 != ""))) {
						// result with matchreport
						$title = "";
						$arrayString = array ();
						$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $team_row->first[$team_col_id]->match_slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
						
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
									$link = sportsmanagementHelperRoute :: getNextMatchRoute($this->project->slug, $team_row->first[$team_col_id]->id);
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
							if($team_row->first[$team_col_id]->cancel) {
								$picture = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif';
								$title = $result->cancel_reason;
								$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16,16, 99);
								$match_result = JHTML::link($link, $desc);
								$new_match = "";
								if($result->new_match_id > 0) {
									$link = sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $team_row->first[$team_col_id]->new_match_id);
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
						$link = sportsmanagementHelperRoute :: getResultsRoute($this->project->slug, $team_row->first[$team_col_id]->roundid);
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
            
            // zweite runde
            
            $ResultType = '';
					if ( isset($team_row->second[$team_col_id]) && $team_row->second[$team_col_id]->decision == 0 ) 
                    {
						                       
                        if ( isset($team_row->second[$team_col_id]) )
                        {
                        $e1 = $team_row->second[$team_col_id]->e1;
			$e2 = $team_row->second[$team_col_id]->e2;
						}
                        else
                        {
                        $e1 = '';
						$e2 = '';    
                        }
                        
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
						if ( isset($team_row->first[$team_col_id]) )
                        {
						switch ($team_row->first[$team_col_id]->rtype) {
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
                            }
					} 
                    else 
                    {
						if ( isset($team_row->second[$team_col_id]) )
                        {
                        $e1 = $team_row->second[$team_col_id]->v1;
						$e2 = $team_row->second[$team_col_id]->v2;
                        }
                        else
                        {
                        $e1 = '';
						$e2 = '';    
                        }
						if (!isset ($team_row->second[$team_col_id]->v1)) {
							$e1 = 'X';
						}
						if (!isset ($team_row->second[$team_col_id]->v2)) {
							$e2 = 'X';
						}
					}
					$showMatchReportLink = false;

					if ( isset($team_row->second[$team_col_id]) && ($team_row->second[$team_col_id]->show_report == 1 || $this->config['force_link_report'] == 1 ) )  {
						$showMatchReportLink = true;
					}
					if ( isset($team_row->second[$team_col_id]) && ( $team_row->second[$team_col_id]->show_report == 0 && $e1 == "" && $e2 == "" ) )
						$showMatchReportLink = true;
					if ($showMatchReportLink) {
						//if ((($this->config['force_link_report'] == 1) && ($result->show_report == 1) && ($e1 != "") && ($e2 != ""))) {
						// result with matchreport
						$title = "";
						$arrayString = array ();
						$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $team_row->second[$team_col_id]->match_slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
						
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
									$link = sportsmanagementHelperRoute :: getNextMatchRoute($this->project->slug, $team_row->second[$team_col_id]->id);
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
							if($team_row->second[$team_col_id]->cancel) {
								$picture = 'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif';
								$title = $result->cancel_reason;
								$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16,16, 99);
								$match_result = JHTML::link($link, $desc);
								$new_match = "";
								if($result->new_match_id > 0) {
									$link = sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $team_row->second[$team_col_id]->new_match_id);
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
						$link = sportsmanagementHelperRoute :: getResultsRoute($this->project->slug, $team_row->second[$team_col_id]->roundid);
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
				$picture = $this->config['image_placeholder'];
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

