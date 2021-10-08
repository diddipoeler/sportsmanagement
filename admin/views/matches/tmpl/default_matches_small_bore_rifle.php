<?php
/** 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matches
 * @file       defaul_matches_small_bore_rifle.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

?>
<style>
    fieldset input,
    fieldset textarea,
    fieldset select,
    fieldset img,
    fieldset button {
        float: none;
        margin: 5px 5px 5px 0;
        width: auto;
    }
</style>
<div id="editcell">
    <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE2', '<i>' . $this->roundws->name . '</i>', '<i>' . $this->projectws->name . '</i>'); ?></legend>

	<?php echo $this->loadTemplate('teamselect'); ?>
	<?php echo $this->loadTemplate('roundselect'); ?>

    <!-- Start games list -->
    <form action="<?php echo $this->request_url; ?>" method="post" id='adminForm' name='adminForm'>
        <div class="btn-group pull-right hidden-phone">
            <label for="limit"
                   class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
        </div>
		<?php
		$colspan = ($this->projectws->allow_add_time) ? 20 : 19;
		?>
        <table class="<?php echo $this->table_data_class; ?>">
            <thead>
            <tr>
                <th width="5"><?php echo count($this->matches) . '/' . $this->pagination->total; ?></th>
                <th width="20">
                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                </th>
                <!--	<th width="20" > </th> -->
				<?php
				if (ComponentHelper::getParams($this->option)->get('cfg_be_extension_single_match', 0))
				{
					?>
                    <th width="20"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SINGLE_MATCH'); ?></th>
					<?php
				}
				?>
                <th width="">
					<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATCHNR', 'mc.match_number', $this->sortDirection, $this->sortColumn); ?>
                </th>
                <th class="">
					<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_MATCHES_DATE', 'mc.match_date', $this->sortDirection, $this->sortColumn); ?>
                </th>
                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TIME'); ?></th>
                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD_ATT'); ?></th>
				<?php
				if ($this->projectws->project_type == 'DIVISIONS_LEAGUE')
				{
					$colspan++;
					?>
                    <th>
						<?php
						echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_MATCHES_DIVISION', 'divhome.id', $this->sortDirection, $this->sortColumn);
						echo '<br>' . HTMLHelper::_('select.genericlist',
								$this->lists['divisions'],
								'filter_division',
								'class="inputbox" size="1" onchange="this.form.submit()"',
								'value', 'text', $this->state->get('filter.division')
							);

						?>
                    </th>
					<?php
				}
				?>
                <th class="title"
                    nowrap="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_CHANGE_ROUNDLIST'); ?></th>
                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'); ?></th>
                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'); ?></th>
                <th style="  "><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT'); ?></th>
				<?php
				if ($this->projectws->allow_add_time)
				{
					?>
                    <th style="text-align:center;  "><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT_TYPE'); ?></th>
					<?php
				}
				?>
                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT_TYPE'); ?></th>

                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT_ARTICLE'); ?></th>

                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EVENTS'); ?></th>
                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_STATISTICS'); ?></th>
                
                <th class="title"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL'); ?></th>
                <th width="1%"><?php echo Text::_('JSTATUS'); ?></th>
                <th width="1%" class="title">
					<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'mc.id', $this->sortDirection, $this->sortColumn); ?>
                </th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <td colspan="<?php echo $colspan - 3; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
                <td colspan="3"><?php echo $this->pagination->getResultsCounter(); ?></td>
            </tr>
            </tfoot>


            <tbody>
			<?php
			$k = 0;

			for ($i = 0, $n = count($this->matches); $i < $n; $i++)
			{
				$row        =& $this->matches[$i];
				$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
				$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
				$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'matches.', $canCheckin);
				$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.match.' . $row->id) && $canCheckin;

				list($date, $time) = explode(" ", $row->match_date);
				$time = strftime("%H:%M", strtotime($time));

				if ($date == '0000-00-00')
				{
					$date = '';
				}
				?>
                <tr class="<?php echo "row$k"; ?>">
					<?php
					if (($row->cancel) > 0)
					{
						$style = "text-align:center;  background-color: #FF9999;";
					}
					else
					{
						$style = "text-align:center; ";
					}
					?>
                    <td style="<?php echo $style; ?>">
						<?php
						echo $this->pagination->getRowOffset($i);
						?>
                    </td>
                    <td class="">
						<?php
						echo HTMLHelper::_('grid.id', $i, $row->id);
						?>
                        <!--	</td>  -->
                        <!--							<td class=""> -->

						<?php
						if ($row->checked_out)
							:
							?>
							<?php echo HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'matches.', $canCheckin); ?>
						<?php endif;

						if ($canEdit && !$row->checked_out)
							:
							?>

							<?PHP
							echo sportsmanagementHelper::getBootstrapModalImage('matchdetails' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/edit.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_DETAILS'), '20', Uri::base() . 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=edit&id=' . $row->id, $this->modalwidth, $this->modalheight);
						endif;

						if (version_compare(JSM_JVERSION, '4', 'eq'))
						{
							$pcture_link   = 'index.php?option=com_media&tmpl=component&path=local-images:/com_sportsmanagement/database/matchreport/' . $row->id;
						}
						else
						{
$pcture_link = 'index.php?option=com_sportsmanagement&view=imagelist&tmpl=component&asset=com_sportsmanagement&author=&folder=matchreport' .'&mid='. $row->id.'&pid='.$this->project_id;							
						}

						?>


						<?php
						echo sportsmanagementHelper::getBootstrapModalImage('matchpicture' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/link.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_MATCHPICTURE'), '20', Uri::base() . $pcture_link, $this->modalwidth, $this->modalheight);
						?>
                        <br>
						<?php
						/** Diddipoeler einzelsportart */
						if ($this->projectws->project_art_id == 2)
						{
							$pcture_link = "index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&id=" . $row->id . "&team1=" . $row->projectteam1_id . "&team2=" . $row->projectteam2_id . "&rid=" . $row->round_id;
							echo sportsmanagementHelper::getBootstrapModalImage('einzelsportart' . $row->id,
                            Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/players_add.png',
                            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_SINGLE_SPORT'),
                            '20',
                            Uri::base() . $pcture_link,
                            $this->modalwidth,
                            $this->modalheight);
							?>

							<?php
							
						}
						?>


                    </td>

                    <td id="match_number" class="center">
                        <input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text"
                               name="match_number<?php echo $row->id; ?>"
                               value="<?php echo $row->match_number; ?>" size="6" tabindex="1"
                               class="form-control form-control-inline"/>
                    </td>
                    <td class="center" nowrap="nowrap">
						<?php

						/**
						 * das wurde beim kalender geändert
						 * $attribs = array(
						 * 'onChange' => "alert('it works')",
						 * "showTime" => 'false',
						 * "todayBtn" => 'true',
						 * "weekNumbers" => 'false',
						 * "fillTable" => 'true',
						 * "singleHeader" => 'false',
						 * );
						 * echo HTMLHelper::_('calendar', Factory::getDate()->format('Y-m-d'), 'date', 'date', '%Y-%m-%d', $attribs); ?>
						 */


						$attribs = array(
							'onChange' => "document.getElementById('cb" . $i . "').checked=true",
						);
						echo HTMLHelper::calendar(sportsmanagementHelper::convertDate($date),
							'match_date' . $row->id,
							'match_date' . $row->id,
							'%d-%m-%Y',
							$attribs
						);
						?>
                    </td>
                    <td class="left" nowrap="nowrap">

                        <input ondblclick="copyValue('match_time')"
                               onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text"
                               name="match_time<?php echo $row->id; ?>"
                               value="<?php echo $time; ?>" size="4" maxlength="5" tabindex="3"
                               class="form-control form-control-inline"/>
<!--
                        <a href="javascript:void(0)"
                           onclick="switchMenu('present<?php echo $row->id; ?>')">&nbsp;
							<?php 
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PRESENT');
$image_attributes['title'] = $imageTitle;                            
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/arrow_open.png',$imageTitle,$image_attributes);
							?>
                        </a><br/>
                        <span id="present<?php echo $row->id; ?>" style="display: none">
									<br/>
										<input ondblclick="copyValue('time_present')"
                                               onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"
                                               type="text"
                                               name="time_present<?php echo $row->id; ?>"
                                               value="<?php echo $row->time_present; ?>" size="4" maxlength="5"
                                               tabindex="3" class="form-control form-control-inline"
                                               title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PRESENT'); ?>"/>
							<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PRESENT_SHORT'); ?>
								</span>
                                -->
                    </td>
                    <td id="crowd" class="center">
                        <input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text"
                               name="crowd<?php echo $row->id; ?>"
                               value="<?php echo $row->crowd; ?>" size="4" maxlength="5" tabindex="4"
                               class="form-control form-control-inline"/>
                    </td>
					<?php
					if ($this->projectws->project_type == 'DIVISIONS_LEAGUE')
					{
						$append = '';
						$append .= ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
						?>
                        <td class="center">
							<?php
							echo HTMLHelper::_('select.genericlist', $this->lists['divisions'], 'division_id' . $row->id,
								'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $row->division_id
							);
							?>
							<?php
							// Echo $row->divhome;
							?>
                        </td>
						<?php
					}
					?>

					<?php
					$append = 'style="background-color:white"';
					$append .= ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
					?>
                    <td id="round_id" style="text-align:center; ">
						<?php
						echo HTMLHelper::_('select.genericlist', $this->lists['project_change_rounds'], 'round_id' . $row->id,
							'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $row->round_id
						);
						?>
                    </td>

                    <td id="projectteam1_id" class="right" nowrap="">
						<?php
						if ($row->homeplayers_count == 0 || $row->homestaff_count == 0)
						{
							$image = 'players_add.png';
						}
						else
						{
							$image = 'players_edit.png';
						}


						$append = 'style="background-color:white"';

						if ($row->projectteam1_id == 0)
						{
							$append = ' ';
						}

						$append .= ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
						echo HTMLHelper::_('select.genericlist', $this->lists['teams_' . $row->divhomeid], 'projectteam1_id' . $row->id,
							'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $row->projectteam1_id
						);
						?>
                        <br>
						<?php
						$pcture_link = 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editlineup&match_date=' . $date . '&id=' . $row->id . '&team=' . $row->projectteam1_id;
						echo sportsmanagementHelper::getBootstrapModalImage('matchlineuphome' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/' . $image, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_LINEUP_HOME'), '20', Uri::base() . $pcture_link, $this->modalwidth, $this->modalheight);
						$title = ' ' . Text::_('COM_SPORTSMANAGEMENT_F_PLAYERS') . ': ' . $row->homeplayers_count . ', ' .
							' ' . Text::_('COM_SPORTSMANAGEMENT_F_TEAM_STAFF') . ': ' . $row->homestaff_count . ' ';

						echo '<sub>' . $row->homeplayers_count . '</sub> ';

$image_attributes['title'] = $title;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/'.$image,Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_LINEUP_HOME'),$image_attributes);

						echo '<sub>' . $row->homestaff_count . '</sub> ';
						?>
                    </td>
                    <td id="projectteam2_id" class="left" nowrap="">
						<?php
						$append = 'style="background-color:white"';

						if ($row->projectteam2_id == 0)
						{
							$append = ' ';
						}

						$append .= ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
						echo HTMLHelper::_('select.genericlist', $this->lists['teams_' . $row->divhomeid], 'projectteam2_id' . $row->id,
							'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $row->projectteam2_id
						);
						?>
                        <br>
						<?php
						if ($row->awayplayers_count == 0 || $row->awaystaff_count == 0)
						{
							$image = 'players_add.png';
						}
						else
						{
							$image = 'players_edit.png';
						}

						$title = ' ' . Text::_('COM_SPORTSMANAGEMENT_F_PLAYERS') . ': ' . $row->awayplayers_count . ', ' .
							' ' . Text::_('COM_SPORTSMANAGEMENT_F_TEAM_STAFF') . ': ' . $row->awaystaff_count;

						echo '<sub>' . $row->awayplayers_count . '</sub> ';
$image_attributes['title'] = $title;                        
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/'.$image,Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_LINEUP_AWAY'),$image_attributes);
						echo '<sub>' . $row->awaystaff_count . '</sub> ';

						?>
                        <!--    </a> -->
						<?php
						$pcture_link = 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editlineup&match_date=' . $date . '&id=' . $row->id . '&team=' . $row->projectteam2_id;
						echo sportsmanagementHelper::getBootstrapModalImage('matchlineupaway' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/' . $image, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_LINEUP_AWAY'), '20', Uri::base() . $pcture_link, $this->modalwidth, $this->modalheight);
						?>
                    </td>
                    <td class="left" nowrap="nowrap">
                        <input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if ($row->alt_decision == 1)
						{
							echo "class=\"subsequentdecision\" title=\"" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION') . "\"";
						} ?>
                               type="text" name="team1_result<?php echo $row->id; ?>"
                               value="<?php echo $row->team1_result; ?>" size="2" tabindex="5"
                               class="form-control form-control-inline"/> :
                        <input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" <?php if ($row->alt_decision == 1)
						{
							echo "class=\"subsequentdecision\" title=\"" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SUB_DECISION') . "\"";
						} ?>
                               type="text" name="team2_result<?php echo $row->id; ?>"
                               value="<?php echo $row->team2_result; ?>" size="2" tabindex="5"
                               class="form-control form-control-inline"/>

						<?PHP
                        echo $row->ringetotal;

						?>
<!--
                        <a href="javascript:void(0)"
                           onclick="switchMenu('part<?php echo $row->id; ?>')">&nbsp;
							<?php 
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_PERIOD_SCORES');
$image_attributes['title'] = $imageTitle;                            
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/arrow_open.png',$imageTitle,$image_attributes);
							?>
                        </a>
                        -->

						<?PHP
						if ($row->alt_decision == 1)
						{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_SUB_DEC');
$image_attributes['title'] = $imageTitle;						  
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/user_edit.png',$imageTitle,$image_attributes);
						}
						?>


                        <br/>
                        <span id="part<?php echo $row->id; ?>" style="display: none">
									<br/>
							<?php
							if ($this->projectws->allow_add_time == 1)
							{
								?>

                                <input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"
                                       type="text" style="font-size: 9px;"
                                       name="team1_result_ot<?php echo $row->id; ?>"
                                       value="<?php echo (isset($row->team1_result_ot)) ? $row->team1_result_ot : ''; ?>"
                                       size="2" tabindex="7" class="form-control form-control-inline"/> :
						<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text"
                               style="font-size: 9px;"
                               name="team2_result_ot<?php echo $row->id; ?>"
                               value="<?php echo (isset($row->team2_result_ot)) ? $row->team2_result_ot : ''; ?>"
                               size="2" tabindex="7" class="form-control form-control-inline"/>
						<?php echo '&nbsp;&nbsp;OT:<br />'; ?>

						<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text"
                               style="font-size: 9px;"
                               name="team1_result_so<?php echo $row->id; ?>"
                               value="<?php echo (isset($row->team1_result_so)) ? $row->team1_result_so : ''; ?>"
                               size="2" tabindex="8" class="form-control form-control-inline"/> :
						<input onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" type="text"
                               style="font-size: 9px;"
                               name="team2_result_so<?php echo $row->id; ?>"
                               value="<?php echo (isset($row->team2_result_so)) ? $row->team2_result_so : ''; ?>"
                               size="2" tabindex="8" class="form-control form-control-inline"/>
								<?php echo '&nbsp;&nbsp;SO:<br />'; ?>
								<?php
							}
							?>
								</span>
                    </td>
					<?php
					if ($this->projectws->allow_add_time)
					{
						?>
                        <td>
							<?php
							$appendselect = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
							echo HTMLHelper::_('select.genericlist', $this->lists['match_result_type'],
								'match_result_type' . $row->id, 'class="form-control form-control-inline" size="1" ' . $appendselect, 'value', 'text',
								$row->match_result_type
							);
							?>
                        </td>
						<?php
					}
					?>

                    <td class="center">
						<?php
						if ($this->selectlist)
						{
							if (array_key_exists('result_type', $this->selectlist))
							{
								$appendselect = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
								echo HTMLHelper::_('select.genericlist', $this->selectlist['result_type'],
									'result_type' . $row->id, 'class="form-control form-control-inline" size="1" ' . $appendselect, 'value', 'text',
									$row->result_type
								);
							}
							else
							{
								echo $row->result_type;
							}
						}
						else
						{
							echo $row->result_type;
						}
						?>

                    </td>

                    <td class="center">
						<?php
						$appendselect = 'style="background-color:white" onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
						echo HTMLHelper::_('select.genericlist', $this->lists['articles'],
							'content_id' . $row->id, 'class="form-control form-control-inline" size="1" ' . $appendselect, 'value', 'text',
							$row->content_id
						);
						?>
                    </td>

                    <td class="center">
						<?php
						echo sportsmanagementHelper::getBootstrapModalImage('editevents' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/events.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_EVENTS'), '20', Uri::base() . 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editevents&id=' . $row->id . '&useeventtime=' . $this->projectws->useeventtime, $this->modalwidth, $this->modalheight);
						echo sportsmanagementHelper::getBootstrapModalImage('editeventsbb' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/teams.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_SBBEVENTS'), '20', Uri::base() . 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editeventsbb&id=' . $row->id . '&useeventtime=' . $this->projectws->useeventtime, $this->modalwidth, $this->modalheight);
						?>
						<?php
						// End several events
						?>
                    </td>
                    
                    <td class="center">
						<?php
						echo sportsmanagementHelper::getBootstrapModalImage('editstats' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/calc16.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_STATS'), '20', Uri::base() . 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editstats&id=' . $row->id, $this->modalwidth, $this->modalheight);

						// Start statistics:
						?>

                    </td>
                    
                    <td style='text-align:center; '>
						<?php
						if ($row->count_result)
						{
							$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PUBLISHED');
							$imageFile  = 'administrator/components/com_sportsmanagement/assets/images/ok.png';
						}
						else
						{
							$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_UNPUBLISHED');
							$imageFile  = 'administrator/components/com_sportsmanagement/assets/images/delete.png';
						}
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image', $imageFile, $imageTitle,$image_attributes);
						?>
                    </td>

                    <td class="center">
                        <div class="btn-group">
							<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'matches.', $canChange, 'cb'); ?>
							<?php
							// Create dropdown items and render the dropdown list.
							if ($canChange)
							{
								HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'matches');
								HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'matches');
								echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->id));
							}
							?>
                        </div>


                    </td>
                    <td class="center">
						<?php
						echo $row->id;
						?>
                    </td>
                </tr>
				<?php
				$k = 1 - $k; //sports_type_name
			}
			?>
            </tbody>
        </table>

<?php $dValue = $this->roundws->round_date_first . ' ' . $this->projectws->start_time; ?>
<input type='hidden' name='sports_type_name' value='<?php echo $this->projectws->sports_type_name; ?>'/>
<input type='hidden' name='match_date' value='<?php echo $dValue; ?>'/>
<input type='hidden' name='use_legs' value='<?php echo $this->projectws->use_legs; ?>'/>
<input type='hidden' name='calendar_id' value='<?php echo $this->projectws->gcalendar_id; ?>'/>
<input type='hidden' name='boxchecked' value='0'/>
<input type='hidden' name='search_mode' value='<?php echo $this->lists['search_mode']; ?>'/>
<input type='hidden' name='filter_order' value='<?php echo $this->sortColumn; ?>'/>
<input type='hidden' name='filter_order_Dir' value='<?php echo $this->sortDirection; ?>'/>
<input type='hidden' name='rid' value='<?php echo $this->roundws->id; ?>'/>
<input type='hidden' name='project_id' value='<?php echo $this->roundws->project_id; ?>'/>
<input type='hidden' name='act' value=''/>
<input type='hidden' name='task' value=''/>
<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
</div>
