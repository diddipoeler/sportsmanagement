<?php defined('_JEXEC') or die('Restricted access');

if (count($this->history) > 0)
{
	?>
<h2>

<?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER');	?></h2>
<!-- staff history START -->
<table width="96%" align="center" border="0" cellpadding="0"
	cellspacing="0">
	<tr>
		<td><br />
			<table class="gameshistory">
				<tr class="sectiontableheader">
					<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
					<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
					<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
				</tr>
					<?php
					$k=0;
					foreach ($this->history AS $station)
					{
						$link1=sportsmanagementHelperRoute::getRefereeRoute($station->project_slug,$this->referee->slug);
						?>
						<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
							<td class="td_l"><?php echo JHtml::link($link1,$station->project_name); ?></td>
							<td class="td_l"><?php echo $station->season_name; ?></td>
							<td class="td_l"><?php echo ($station->position_name ? JText::_($station->position_name) : ""); ?></td>
						</tr>
						<?php
						$k=(1-$k);
					}
					?>
				</table>
		</td>
	</tr>
</table>
<br />
<br />
<!-- staff history END -->

<?php
}
?>