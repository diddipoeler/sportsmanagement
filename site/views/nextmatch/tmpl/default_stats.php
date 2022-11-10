<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       default_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatch">
<?php
$this->notes = array();
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_H2H');
echo $this->loadTemplate('jsm_notes'); 
?>
    <table class="table" id="nextmatch-default_stats-anfang">
        <thead>
        <tr class="" align="center">
            <th class="h2h" width="33%">
				<?php
				if (!is_null($this->teams))
				{
					echo $this->teams[0]->name;
				}
				else
				{
					echo Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM");
				}
				?>
            </th>

            <th class="h2h" width="33%">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_STATS');
				?>
            </th>
            <th class="h2h">
				<?php
				if (!is_null($this->teams))
				{
					echo $this->teams[1]->name;
				}
				else
				{
					echo Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM");
				}
				?>
            </th>

        </tr>
        </thead>
		<?php
		if ($this->config['show_chances'])
		{
if ( !$this->chances )
{
$this->chances[0] = '';
$this->chances[1] = '';	
}			
			?>
            <tr class="sectiontableentry1">
                <td class="valueleft">
					<?php
					echo $this->chances[0] . "%";
					?>
                </td>
                <td class="statlabel">
					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_CHANCES');
					?>
                </td>
                <td class="valueright">
					<?php
					echo $this->chances[1] . "%";
					?>
                </td>
            </tr>
			<?php
		}

		if ($this->config['show_current_rank'])
		{
			?>
            <tr class="sectiontableentry2">
                <td class="valueleft">
					<?php
					echo $this->homeranked->rank;
					?>
                </td>
                <td class="statlabel">
					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_CURRENT_RANK');
					?>
                </td>
                <td class="valueright">
					<?php
					echo $this->awayranked->rank;
					?>
                </td>
            </tr>
			<?php
		}

		if ($this->config['show_match_count'])
		{
			?>
            <tr class="sectiontableentry1">
                <td class="valueleft">
					<?php
					echo $this->homeranked->cnt_matches;
					?>
                </td>
                <td class="statlabel">
					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_COUNT_MATCHES');
					?>
                </td>
                <td class="valueright">
					<?php
					echo $this->awayranked->cnt_matches;
					?>
                </td>
            </tr>
			<?php
		}

		if ($this->config['show_match_total'])
		{
			?>
            <tr class="sectiontableentry2">
                <td class="valueleft">
					<?php
					printf("%s/%s/%s", $this->homeranked->cnt_won, $this->homeranked->cnt_draw, $this->homeranked->cnt_lost);
					?>
                </td>
                <td class="statlabel">
					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_TOTAL');
					?>
                </td>
                <td class="valueright">
					<?php
					printf("%s/%s/%s", $this->awayranked->cnt_won, $this->awayranked->cnt_draw, $this->awayranked->cnt_lost);
					?>
                </td>
            </tr>
			<?php
		}

		if ($this->config['show_match_total_home'])
		{
			?>
            <tr class="sectiontableentry1">
                <td class="valueleft">
					<?php printf(
						"%s/%s/%s",
						$this->homeranked->cnt_won_home,
						$this->homeranked->cnt_draw_home,
						$this->homeranked->cnt_lost_home
					); ?>
                </td>
                <td class="statlabel">
					<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HOME'); ?>
                </td>
                <td class="valueright">
					<?php printf(
						"%s/%s/%s",
						$this->awayranked->cnt_won_home,
						$this->awayranked->cnt_draw_home,
						$this->awayranked->cnt_lost_home
					); ?>
                </td>
            </tr>
			<?php
		}
		?>
		<?php
		if ($this->config['show_match_total_away'])
		{
			?>
            <tr class="sectiontableentry2">
                <td class="valueleft">
					<?php printf(
						"%s/%s/%s",
						$this->homeranked->cnt_won - $this->homeranked->cnt_won_home,
						$this->homeranked->cnt_draw - $this->homeranked->cnt_draw_home,
						$this->homeranked->cnt_lost - $this->homeranked->cnt_lost_home
					); ?>
                </td>
                <td class="statlabel">
					<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY'); ?>
                </td>
                <td class="valueright">
					<?php printf(
						"%s/%s/%s",
						$this->awayranked->cnt_won - $this->awayranked->cnt_won_home,
						$this->awayranked->cnt_draw - $this->awayranked->cnt_draw_home,
						$this->awayranked->cnt_lost - $this->awayranked->cnt_lost_home
					); ?>
                </td>
            </tr>
			<?php
		}
		?>
		<?php
		if ($this->config['show_match_points'])
		{
			?>
            <tr class="sectiontableentry1">
                <td class="valueleft">
					<?php
					echo $this->homeranked->sum_points;

					// Echo JSMRankingTeam->getPoints();
					?>
                </td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_POINTS'); ?></td>
                <td class="valueright"><?php echo $this->awayranked->sum_points; ?></td>
            </tr>
			<?php
		}
		?>
		<?php
		if ($this->config['show_match_goals'])
		{
			?>
            <tr class="sectiontableentry2">
                <td class="valueleft">
					<?php printf(
						"%s : %s",
						$this->homeranked->sum_team1_result,
						$this->homeranked->sum_team2_result
					); ?>
                </td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GOALS'); ?></td>
                <td class="valueright">
					<?php printf(
						"%s : %s",
						$this->awayranked->sum_team1_result,
						$this->awayranked->sum_team2_result
					); ?>
                </td>
            </tr>
			<?php
		}
		?>
		<?php
		if ($this->config['show_match_diff'])
		{
			?>
            <tr class="sectiontableentry1">
                <td class="valueleft">
					<?php echo $this->homeranked->diff_team_results; ?>
                </td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DIFFERENCE'); ?></td>
                <td class="valueright">
					<?php echo $this->awayranked->diff_team_results; ?>
                </td>
            </tr>
			<?php
		}

		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
		$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);

		if ($this->config['show_match_highest_stats'])
			:
			?>

			<?php if ($this->config['show_match_highest_won'])
			:
			?>
            <tr class="sectiontableentry2">
                <td class="valueleft">
					<?php if ($stat = $this->home_highest_home_win)
						:
						?>
						<?php
						$routeparameter['p']   = $this->home_highest_home_win->project_slug;
						$routeparameter['mid'] = $this->home_highest_home_win->match_slug;
						echo HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter), sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals)); ?>
					<?php else

						:
						?>
                        ----
					<?php endif; ?>
                </td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_WON_HOME'); ?></td>
                <td class="valueright">
					<?php if ($stat = $this->away_highest_away_win)
						:
						?>
						<?php
						$routeparameter['p']   = $this->away_highest_away_win->project_slug;
						$routeparameter['mid'] = $this->away_highest_away_win->match_slug;
						echo HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter), sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals)); ?>
					<?php else

						:
						?>
                        ----
					<?php endif; ?>
                </td>

            </tr>
		<?php endif; ?>

			<?php if ($this->config['show_match_highest_loss'])
			:
			?>
            <tr class="sectiontableentry1">
                <td class="valueleft">
					<?php if ($stat = $this->home_highest_home_def)
						:
						?>
						<?php
						$routeparameter['p']   = $this->home_highest_home_def->project_slug;
						$routeparameter['mid'] = $this->home_highest_home_def->match_slug;
						echo HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter), sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals)); ?>
					<?php else

						:
						?>
                        ----
					<?php endif; ?>
                </td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_LOSS_HOME'); ?></td>
                <td class="valueright">
					<?php if ($stat = $this->away_highest_away_def)
						:
						?>
						<?php
						$routeparameter['p']   = $this->away_highest_away_def->project_slug;
						$routeparameter['mid'] = $this->away_highest_away_def->match_slug;
						echo HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter), sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals)); ?>
					<?php else

						:
						?>
                        ----
					<?php endif; ?>
                </td>

            </tr>
		<?php endif; ?>

			<?php if ($this->config['show_match_highest_won_away'])
			:
			?>
            <tr class="sectiontableentry2">
                <td class="valueleft">
					<?php if ($stat = $this->home_highest_away_win)
						:
						?>
						<?php
						$routeparameter['p']   = $this->home_highest_away_win->project_slug;
						$routeparameter['mid'] = $this->home_highest_away_win->match_slug;
						echo HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter), sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals)); ?>
					<?php else

						:
						?>
                        ----
					<?php endif; ?>
                </td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_WON_AWAY'); ?></td>
                <td class="valueright">
					<?php if ($stat = $this->away_highest_home_win)
						:
						?>
						<?php
						$routeparameter['p']   = $this->away_highest_home_win->project_slug;
						$routeparameter['mid'] = $this->away_highest_home_win->match_slug;
						echo HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter), sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals)); ?>
					<?php else

						:
						?>
                        ----
					<?php endif; ?>
                </td>
            </tr>
		<?php endif; ?>

			<?php if ($this->config['show_match_highest_loss_away'])
			:
			?>
            <tr class="sectiontableentry1">
                <td class="valueleft">
					<?php if ($stat = $this->home_highest_away_def)
						:
						?>
						<?php
						$routeparameter['p']   = $this->home_highest_away_def->project_slug;
						$routeparameter['mid'] = $this->home_highest_away_def->match_slug;
						echo HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter), sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals)); ?>
					<?php else

						:
						?>
                        ----
					<?php endif; ?>
                </td>
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HIGHEST_LOSS_AWAY'); ?></td>
                <td class="valueright">
					<?php if ($stat = $this->away_highest_home_def)
						:
						?>
						<?php
						$routeparameter['p']   = $this->away_highest_home_def->project_slug;
						$routeparameter['mid'] = $this->away_highest_home_def->match_slug;
						echo HTMLHelper::link(sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter), sprintf("%s - %s %s:%s", $stat->hometeam, $stat->awayteam, $stat->homegoals, $stat->awaygoals)); ?>
					<?php else

						:
						?>
                        ----
					<?php endif; ?>
                </td>
            </tr>
		<?php endif; ?>
		<?php endif; ?>

    </table>
    <!-- gesamtübersicht der spiele gegeneinander nach ligen  -->
    <h4><?php echo $this->teams[0]->name . " " . Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_VS") . " " . $this->teams[1]->name; ?></h4>
	<h4><?php echo Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_DATA_OF") . ' ' . $this->teams[0]->name;?> </h4>
    <table class="table table-striped" id="nextmatch-default_stats-anfang-spiele-gegeneinander1">
        <thead>
        <tr>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_COUNT_MATCHES'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WON'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DRAW'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LOST'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_SCOREFOR'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_SCOREAGAINST'); ?>
            </td>
        </tr>
        </thead>
		<?php
		foreach ($this->gesamtspiele as $key => $value)
		{
			?>
            <tr>
                <td>
					<?php
					echo $key;
					?>
                </td>
                <td>
					<?php
					echo $value->gesamtspiele;
					?>
                </td>
                <td>
					<?php
					echo $value->gewonnen;
					?>
                </td>
                <td>
					<?php
					echo $value->unentschieden;
					?>
                </td>
                <td>
					<?php
					echo $value->verloren;
					?>
                </td>
                <td>
					<?php
					echo $value->plustore;
					?>
                </td>
                <td>
					<?php
					echo $value->minustore;
					?>
                </td>
            </tr>
			<?php
		}
		?>
    </table>

   <table class="table table-striped" id="nextmatch-default_stats-anfang-spiele-gegeneinander2">
        <thead>
        <tr>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_LOCAL_WINS'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_LOCAL_DRAWS'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_LOCAL_LOSTS'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY_WINS'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY_DRAWS'); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_AWAY_LOSTS'); ?>
            </td>
        </tr>
        </thead>
		<?php
		foreach ($this->gesamtspiele as $key => $value)
		{
			?>
            <tr>
                <td>
					<?php
					echo $key;
					?>
                </td>
                <td>
					<?php
					echo $value->localwin;
					?>
                </td>
                <td>
					<?php
					echo $value->localdraw;
					?>
                </td>
                <td>
					<?php
					echo $value->locallost;
					?>
                </td>
                <td>
					<?php
					echo $value->awaywin;
					?>
                </td>
                <td>
					<?php
					echo $value->awaydraw;
					?>
                </td>
                <td>
					<?php
					echo $value->awaylost;
					?>
                </td>
            </tr>
			<?php
		}
		?>
    </table>

    <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY_COUNT_RESULT'); ?></h4>
	
	<div class="d-flex align-items-start">
		<div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
			<button class="nav-link active" id="v-pills-countall-tab" data-bs-toggle="pill" data-bs-target="#v-pills-countall" type="button" role="tab" aria-controls="v-pills-countall" aria-selected="true"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_OVERALL'); ?></button>
			<button class="nav-link" id="v-pills-counthome-tab" data-bs-toggle="pill" data-bs-target="#v-pills-counthome" type="button" role="tab" aria-controls="v-pills-counthome" aria-selected="false"><?php echo $this->teams[0]->name . " " . Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_VS") . " " . $this->teams[1]->name; ?></button>
			<button class="nav-link" id="v-pills-countaway-tab" data-bs-toggle="pill" data-bs-target="#v-pills-countaway" type="button" role="tab" aria-controls="v-pills-countaway" aria-selected="false"><?php echo $this->teams[1]->name . " " . Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_VS") . " " . $this->teams[0]->name; ?></button>
		</div>
	
	<div class="tab-content" id="v-pills-tabContent">

		<div class="tab-pane fade show active" id="v-pills-countall" role="tabpanel" aria-labelledby="v-pills-countall-tab">
			<table class="table table-striped" id="history-1">
						<tr>
							<td> <?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_SCORE'); ?>
							</td>
							<td> <?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_SCORE_FRECUENCY'); ?>
							</td>
						</tr>	
						<?php
						ksort($this->statgames['gesamt']);

						foreach ($this->statgames['gesamt'] as $key => $value)
						{
							?>
                            <tr>
                                <td>
									<?php
									echo $key;
									?>
                                </td>
                                <td>
									<?php
									echo $value;
									?>
                                </td>
                            </tr>
							<?php
						}
						?>
			</table>
		</div>
		
		<div class="tab-pane fade" id="v-pills-counthome" role="tabpanel" aria-labelledby="v-pills-counthome-tab">
			<table class="table table-striped" id="history-2">
						<tr>
							<td> <?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_SCORE'); ?>
							</td>
							<td> <?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_SCORE_FRECUENCY'); ?>
							</td>
						</tr>				
						<?php
						ksort($this->statgames['home']);

						foreach ($this->statgames['home'] as $key => $value)
						{
							?>
                            <tr>
                                <td>
									<?php
									echo $key;
									?>
                                </td>
                                <td>
									<?php
									echo $value;
									?>
                                </td>
                            </tr>
							<?php
						}
						?>
			</table>
		</div>
		
		<div class="tab-pane fade" id="v-pills-countaway" role="tabpanel" aria-labelledby="v-pills-countaway-tab">
			<table class="table table-striped" id="history-3">
						<tr>
							<td> <?php echo Text::_('COM_SPORTSMANAGEMENT_RESULTS_SCORE'); ?>
							</td>
							<td> <?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_SCORE_FRECUENCY'); ?>
							</td>
						</tr>				
						<?php
						ksort($this->statgames['away']);

						foreach ($this->statgames['away'] as $key => $value)
						{
							?>
                            <tr>
                                <td>
									<?php
									echo $key;
									?>
                                </td>
                                <td>
									<?php
									echo $value;
									?>
                                </td>
                            </tr>
							<?php
						}
						?>
			</table>
		</div>	
	</div>
	

    </div>
</div>
<!-- Main END -->
<br/>
