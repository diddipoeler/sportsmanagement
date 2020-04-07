<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
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
<div class="<?php echo $this->divclassrow;?> table-responsive" id="nextmatch">
<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_H2H'); ?></h4>
<table class="table">
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

	$routeparameter = array();
	$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
	$routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);

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
						$routeparameter['p'] = $this->home_highest_home_win->project_slug;
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
						$routeparameter['p'] = $this->away_highest_away_win->project_slug;
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
						$routeparameter['p'] = $this->home_highest_home_def->project_slug;
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
						$routeparameter['p'] = $this->away_highest_away_def->project_slug;
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
						$routeparameter['p'] = $this->home_highest_away_win->project_slug;
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
						$routeparameter['p'] = $this->away_highest_home_win->project_slug;
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
						$routeparameter['p'] = $this->home_highest_away_def->project_slug;
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
						$routeparameter['p'] = $this->away_highest_home_def->project_slug;
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
<table class="table table-striped">
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
  
<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY_COUNT_RESULT'); ?></h4>
<div class="panel-group" id="countresult">  

<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#countresult" href="#countall"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_OVERALL'); ?></a>
</h4>
</div>
<div id="countall" class="panel-collapse collapse">
<div class="panel-body">
<table class="table <?php echo $this->config['table_class'] ?>">
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
</div>
</div>

  
<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#countresult" href="#counthome"><?php echo $this->teams[0]->name . " " . Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_VS") . " " . $this->teams[1]->name; ?></a>
</h4>
</div>
<div id="counthome" class="panel-collapse collapse">
<div class="panel-body">  
<table class="table <?php echo $this->config['table_class'] ?>">
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
</div>
</div>  
  
<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#countresult" href="#countaway"><?php echo $this->teams[1]->name . " " . Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_VS") . " " . $this->teams[0]->name; ?></a>
</h4>
</div>
<div id="countaway" class="panel-collapse collapse">
<div class="panel-body">  
<table class="table <?php echo $this->config['table_class'] ?>">
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
</div>
<!-- Main END -->
<br/>
