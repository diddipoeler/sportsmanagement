<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matrix
 * @file       default_matrix.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
?>

<!--[if IE]>
   <style>
	  .rotate_text
	  {
		 writing-mode: tb-rl;
		 filter: flipH() flipV();
		 position: absolute;
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
		 vertical-align:bottom;
	  }
   </style>
<![endif]-->

<!--[if !IE]><!-->
<style>
.table-header-rotated th.rotate-45{
  height: 80px;
  width: 40px;
  min-width: 40px;
  max-width: 40px;
  position: relative;
  vertical-align: bottom;
  padding: 0;
  font-size: 12px;
  line-height: 0.8;
}

.table-header-rotated th.rotate-45 > div{
  position: relative;
  top: 0px;
  left: 40px; /* 80 * tan(45) / 2 = 40 where 80 is the height on the cell and 45 is the transform angle*/
  height: 100%;
  -ms-transform:skew(-45deg,0deg);
  -moz-transform:skew(-45deg,0deg);
  -webkit-transform:skew(-45deg,0deg);
  -o-transform:skew(-45deg,0deg);
  transform:skew(-45deg,0deg);
  overflow: hidden;
  border-left: 1px solid #dddddd;
  border-right: 1px solid #dddddd;
  border-top: 1px solid #dddddd;
}

.table-header-rotated th.rotate-45 span {
  -ms-transform:skew(45deg,0deg) rotate(315deg);
  -moz-transform:skew(45deg,0deg) rotate(315deg);
  -webkit-transform:skew(45deg,0deg) rotate(315deg);
  -o-transform:skew(45deg,0deg) rotate(315deg);
  transform:skew(45deg,0deg) rotate(315deg);
  position: absolute;
  bottom: 30px; /* 40 cos(45) = 28 with an additional 2px margin*/
  left: -25px; /*Because it looked good, but there is probably a mathematical link here as well*/
  display: inline-block;
  /* width: 100%;
  width: 85px; /* 80 / cos(45) - 40 cos (45) = 85 where 80 is the height of the cell, 40 the width of the cell and 45 the transform angle*/
  text-align: left;
  /* white-space: nowrap; /*whether to display in one line or not*/
}

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
				position: absolute;
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
		 vertical-align:bottom;
		 position: relative;
		 /*position: absolute; */
	  }
   </style>
<!--<![endif]-->

<div class="<?php echo $this->divclassrow;?> table-responsive" id="defaultmatrix">
	<?php

	// $this->config['highlight_fav_team'] = 1;
	// $this->project->fav_team_text_color = "#FFFFFF";
	$division_id = $this->divisionid;

	$matrix = '<table class="' . $this->config['table_class'] . ' table-header-rotated">';
	$k = 1;
	$crosstable_icons_horizontal = (isset($this->config['crosstable_icons_horizontal'])) ? $this->config['crosstable_icons_horizontal'] : 0;
	$crosstable_icons_vertical = (isset($this->config['crosstable_icons_vertical'])) ? $this->config['crosstable_icons_vertical'] : 0;

	$k_r = 0; // Count rows

	foreach ($this->teams as $team_row_id => $team_row)
{
		if ($k_r == 0) // Header rows
	{
			$matrix .= '<tr class="sectiontableheader">';

			// Write the first row
			$matrix .= '<th class="headerspacer">&nbsp;</th>';

			if ($crosstable_icons_horizontal)
		{
				$matrix .= '<th class="headerspacer">&nbsp;</th>';
			}
			else
		{
				$matrix .= '<th class="headerspacer">&nbsp;</th>';
			}

					  $teamnumber = 1;

			foreach ($this->teams as $team_row_header)
		{
							  $title = Text :: _('COM_SPORTSMANAGEMENT_MATRIX_CLUB_PAGE_LINK') . ' ' . $team_row_header->name;
				$link = sportsmanagementHelperRoute::getClubInfoRoute($this->project->slug, $team_row_header->club_slug, null, Factory::getApplication()->input->getInt('cfg_which_database', 0));

				// $desc = $team_row_header->short_name;
				$name = $this->config['teamnames'];
				$desc = $teamnumber . ', ' . $team_row_header->$name;

				if ($crosstable_icons_horizontal) // Icons at the top of matrix
			{
					$picture = $team_row_header->logo_small;
					$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 0, 0, 3);
				}


				if ($this->config['link_teams'] == 1)
			{
								$header = '<th class="rotate-45"><div ><span>';
								$header .= HTMLHelper::link($link, $desc);
								$header .= '</span></div></th>';
								$matrix .= $header;
				}
				else
			{
					$header = '<th class="rotate-45"><div ><span>';
					$header .= $desc;
					$header .= '</span></div></th>';
					$matrix .= $header;
				}

							  $teamnumber++;
			}

			$matrix .= '</tr>';
		}


			$trow = $team_row;
		$matrix .= '<tr class="">';
		$k_c = 0; // Count columns

		foreach ($this->teams as $team_col_id => $team_col)
	{
			if ($k_c == 0) // Header columns
		{
				$title = Text :: _('COM_SPORTSMANAGEMENT_MATRIX_PLAYERS_PAGE_LINK') . ' ' . $trow->name;
				$routeparameter = array();
				$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
				$routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
				$routeparameter['p'] = $this->project->slug;
				$routeparameter['tid'] = $trow->team_slug;
				$routeparameter['ptid'] = 0;
				$link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);


				// $desc = $trow->short_name;
				$name = $this->config['teamnames'];
				$desc = $trow->$name;

				if ($crosstable_icons_vertical) // Icons on the left side of matrix
			{
					$picture = $trow->logo_small;
					$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 0, 0, 3);
				}

				$tValue = '<th class="">';
				$tValue .= $k_r + 1;
				$tValue .= '</th>';
				$matrix .= $tValue;

				if ($this->config['link_teams'] == 1)
			{
					$tValue = '<th class="teamsleft">';
					$tValue .= HTMLHelper::link($link, $desc);
					$tValue .= '</th>';
					$matrix .= $tValue;
				}
				else
			{
							  $tValue = '<th class="teamsleft">';
							  $tValue .= $desc;
							  $tValue .= '</th>';
							  $matrix .= $tValue;
				}
			}

								$tcol = $team_col;
								$match_result = '&nbsp;';

								// Find the corresponding game
								$Allresults = '';

			foreach ($this->results as $result)
		{
				if ($team_row->division_id != $division_id)
			{
					continue;
				}


				if (($result->projectteam1_id == $team_row->projectteamid) && ($result->projectteam2_id == $team_col->projectteamid))
			{
								$ResultType = '';

					if ($result->decision == 0)
				{
											$e1 = $result->e1;
											$e2 = $result->e2;

						if ($e1 > $e2)
					{
									  $e1 = '<span style="color:' . $this->config['color_win'] . '">' . $e1 . '</span>';
									  $e2 = '<span style="color:' . $this->config['color_win'] . '">' . $e2 . '</span>';
						}
						elseif ($e1 == $e2)
					{
												$e1 = '<span style="color:' . $this->config['color_draw'] . '">' . $e1 . '</span>';
												$e2 = '<span style="color:' . $this->config['color_draw'] . '">' . $e2 . '</span>';
						}
						elseif ($e1 < $e2)
					{
												$e1 = '<span style="color:' . $this->config['color_loss'] . '">' . $e1 . '</span>';
												$e2 = '<span style="color:' . $this->config['color_loss'] . '">' . $e2 . '</span>';
						}

						switch ($result->rtype)
					{
							case 1 : // Overtime
								$ResultType = ' (' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_OVERTIME');
								  $ResultType .= ')';
							break;

							case 2 : // Shootout
								$ResultType = ' (' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOOTOUT');
								  $ResultType .= ')';
							break;

							case 0 :
							break;
						}
					}
					else
				{
						$e1 = $result->v1;
						$e2 = $result->v2;

						if (!isset($result->v1))
					{
							   $e1 = 'X';
						}


						if (!isset($result->v2))
					{
							   $e2 = 'X';
						}
					}

								$showMatchReportLink = false;

					if ($result->show_report == 1 || $this->config['force_link_report'] == 1)
				{
						$showMatchReportLink = true;
					}


					if ($result->show_report == 0 && $e1 == "" && $e2 == "")
				{
											$showMatchReportLink = true;
					}


					if ($showMatchReportLink)
				{
											// If ((($this->config['force_link_report'] == 1) && ($result->show_report == 1) && ($e1 != "") && ($e2 != ""))) {
											// result with matchreport
											$title = "";
											$arrayString = array ();
											$routeparameter = array();
											$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
											$routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
											$routeparameter['p'] = $this->project->slug;
											$routeparameter['mid'] = $result->match_slug;
											$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter);

						if (($e1 != "") && ($e2 != ""))
					{
							$colorStr = "color:" . $this->project->fav_team_text_color . ";";
							$bgColorStr = "background-color:" . $this->project->fav_team_color . ";";

							if (($this->config['highlight_fav_team'] != 2) || (!in_array($team_row->id, $this->favteams) && !in_array($team_col->id, $this->favteams)))
						{
								// $resultStr = str_replace( "%TEAMHOME%",
								// $this->teams[$result->projectteam1_id]->name,
								// Text::_( 'COM_SPORTSMANAGEMENT_STANDARD_MATCH_REPORT_FORM' ) );
								// $title = str_replace( "%TEAMGUEST%", $this->teams[$result->projectteam2_id]->name, $title );
								$resultStr = $e1 . $this->overallconfig['seperator'] . $e2 . $ResultType;

								if (($this->config['highlight_fav_team'] > 0) && ($this->project->fav_team_text_color != "") && (in_array($team_row->id, $this->favteams) || in_array($team_col->id, $this->favteams)))
							{
									$arrayString = array (
									 "style" => $colorStr . $bgColorStr
																	);
								}
								else
							{
														$arrayString = "";
								}
							}
							else
						{
															$resultStr = "";
															$resultStr .= "&nbsp;" . $e1 . $this->overallconfig['seperator'] . $e2 . $ResultType . "&nbsp;";
															$arrayString = array (
															 "style" => $colorStr . $bgColorStr
															);
							}

							$match_result = HTMLHelper::link($link, $resultStr, $arrayString);
						}
						else
					{
							switch ($this->config['which_link'])
						{
								case 1 : // Link to Next Match page
										$link = sportsmanagementHelperRoute :: getNextMatchRoute($this->project->slug, $result->id, Factory::getApplication()->input->getInt('cfg_which_database', 0));

										// FIXME
										// $title = str_replace( "%TEAMHOME%",
										//                       $this->teams[$result->projectteam1_id]->name,
										//                       Text::_( 'COM_SPORTSMANAGEMENT_FORCED_MATCH_REPORT_NEXTPAGE_FORM' ) );
										$title = str_replace("%TEAMGUEST%", $this->teams[$result->projectteam2_id]->name, $title);
													break;

								case 2 : // Link to Match report
										$title = str_replace("%TEAMHOME%", $this->teams[$result->projectteam1_id]->name, Text :: _('COM_SPORTSMANAGEMENT_FORCED_MATCH_REPORT_FORM'));
										$title = str_replace("%TEAMGUEST%", $this->teams[$result->projectteam2_id]->name, $title);
													break;

								default :
													break;
							}

							if ($result->cancel)
						{
								$picture = 'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/away.gif';
								$title = $result->cancel_reason;
								$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16, 16, 99);
								$match_result = HTMLHelper::link($link, $desc);
								$new_match = "";

								if ($result->new_match_id > 0)
							{
															$link = sportsmanagementHelperRoute::getNextMatchRoute($this->project->slug, $result->new_match_id, Factory::getApplication()->input->getInt('cfg_which_database', 0));
															$picture = 'media/com_sportsmanagement/jl_images/bullet_black.png';
															$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16, 16, 99);
															$new_match = HTMLHelper::link($link, $desc);
								}

										$match_result .= $new_match;
							}
							else
						{
														$picture = 'media/com_sportsmanagement/jl_images/bullet_black.png';
														$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16, 16, 99);
														$match_result = HTMLHelper::link($link, $desc);
							}
						}
					}
					elseif (($e1 != "") && ($e2 != ""))
				{
						// Result without match report
						if (($this->config['highlight_fav_team'] != 2) || (!in_array($team_row->id, $this->favteams)) && (!in_array($team_col->id, $this->favteams)))
					{
							$resultStr = $e1 . $this->overallconfig['seperator'] . $e2 . $ResultType;
						}
						else
					{
												$resultStr = "";
												$resultStr .= "<span style='color:" . $this->project->fav_team_text_color . ";'>";
												$resultStr .= "<span style='background-color:" . $this->project->fav_team_color . ";'>";
												$resultStr .= "&nbsp;" . $e1 . $this->overallconfig['seperator'] . $e2 . $ResultType . "&nbsp;";
												$resultStr .= "</span>";
												$resultStr .= "</span>";
						}

						$match_result = $resultStr;
					}
					else
				{
											// Any result available so "bullet_black.png" is shown with a link to the gameday of the match
											$link = sportsmanagementHelperRoute :: getResultsRoute($this->project->slug, $result->roundid, 0, 0, 0, null, Factory::getApplication()->input->getInt('cfg_which_database', 0));
											$title = str_replace("%NR_OF_MATCHDAY%", $result->roundcode, Text :: _('COM_SPORTSMANAGEMENT_MATCHDAY_FORM'));
											$picture = 'media/com_sportsmanagement/jl_images/bullet_black.png';
											$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16, 16, 99);

						if (($this->config['highlight_fav_team'] != 2) || (!in_array($team_row->id, $this->favteams)) && (!in_array($team_col->id, $this->favteams)))
					{
												$spanStartStr = "";
												$spanEndStr = "";
						}
						else
					{
												$spanStartStr = "";
												$spanStartStr .= "<span style='color:" . $this->project->fav_team_text_color . ";'>";
												$spanStartStr .= "<span style='background-color:" . $this->project->fav_team_color . ";'>";
												$spanStartStr .= "&nbsp;";

												$spanEndStr = "&nbsp;";
												$resultStr .= "</span>";
												$resultStr .= "</span>";
						}

											$match_result = $spanStartStr . HTMLHelper::link($link, $desc) . $spanEndStr;
					}

								// Don’t break, allow for multiple results
					if ($Allresults == '')
				{
						$Allresults = $match_result;
					}
					else
				{
											$Allresults .= '<br>' . $match_result;
					}
				}
			}

								$value = "";

			if ($k_r == $k_c)
		{
				if (($this->config['highlight_fav_team'] == 1) && (in_array($trow->team_id, $this->favteams) || in_array($tcol->team_id, $this->favteams)))
			{
					$dummy = '<td class="result" style="';
					$dummy .= ' color:' . $this->project->fav_team_text_color . ';';
					$dummy .= ' background-color:' . $this->project->fav_team_color . ';';
					$dummy .= '">';
				}
				else
			{
					$dummy = '<td style="text-align:center; vertical-align:middle; ">';
				}

				$value = $dummy;
				$title = '';
				$picture = $this->config['image_placeholder'];
				$desc = sportsmanagementHelper::getPictureThumb($picture, $title, 16, 16, 99);
				$value .= $desc;
			}
			else
		{
				if (($this->config['highlight_fav_team'] > 0) && (in_array($trow->team_id, $this->favteams) || in_array($tcol->team_id, $this->favteams)))
			{
					if ($this->config['highlight_fav_team'] == 1)
				{
						$dummy = '<td class="result" style="';
						$dummy .= ' color:' . $this->project->fav_team_text_color . ';';
						$dummy .= ' background-color:' . $this->project->fav_team_color . ';';
						$dummy .= '"  title="';
					}
					else
				{
											$dummy = '<td class="result" title="';
					}
				}
				else
			{
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
