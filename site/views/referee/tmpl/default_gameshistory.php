<?php defined('_JEXEC') or die('Restricted access'); ?>
<!-- Player stats History START -->


<?php if (count($this->games))
{
	?>
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY'); ?></h2>
<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td><br />
			<table class="gameshistory">
				<thead>
					<tr class="sectiontableheader">
						<th colspan="6"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_GAMES'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$k=0;
				foreach ($this->games as $game)
				{
					$report_link=sportsmanagementHelperRoute::getMatchReportRoute($this->project->slug,$game->id);
					?>

					<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
						<td><?php
						echo JHTML::link($report_link,strftime($this->config['games_date_format'],strtotime($game->match_date)));
						?>
						</td>
						<td class="td_r"><?php echo $this->teams[$game->projectteam1_id]->name; ?>
						</td>
						<td class="td_r"><?php echo $game->team1_result; ?></td>
						<td class="td_c"><?php echo $this->overallconfig['seperator']; ?>
						</td>
						<td class="td_l"><?php echo $game->team2_result; ?></td>
						<td class="td_l"><?php echo $this->teams[$game->projectteam2_id]->name; ?>
						</td>
					</tr>
							<?php
							$k=(1-$k);
						}
						?>
					</tbody>
			</table>
		</td>
	</tr>
</table>
<br />
	<?php
}
?>