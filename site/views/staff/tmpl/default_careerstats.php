<?php defined('_JEXEC') or die('Restricted access'); ?>

<!-- Player stats History START -->
<h2><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS' );	?></h2>
<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<br/>
			<table id="stats_history" width="96%" align="center" cellspacing="0" cellpadding="0" border="0">
				<tr class="sectiontableheader">
					<th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
					<th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
					<th class="td_c"><?php
						$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED');
						echo JHTML::image(	'images/com_joomleague/database/events/'.$this->project->fs_sport_type_name.'/played.png',
											$imageTitle,array(' title' => $imageTitle,' width' => 20,' height' => 20));
						?></th>
					<?php
					if ($this->config['show_careerstats'])
					{
						if (!empty($stats))
						{
							foreach ($this->stats as $stat){ ?><th class="td_c"><?php echo $stat->getImage(); ?></th><?php }
						}
					}
					?>
				</tr>
				<?php
				$k=0;
				$career=array();
				$career['played']=0;
				$mod = JModel::getInstance('Staff','sportsmanagementModel');
				if (count($this->history) > 0)
				{
					foreach ($this->history as $player_hist)
					{
						$model = $this->getModel();
						$present=$model->getPresenceStats($player_hist->project_id,$player_hist->pid);
						?>
						<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
							<td class="td_l" nowrap="nowrap"><?php
								echo $player_hist->project_name;
								# echo " (".$player_hist->project_id.")";
								?></td>
							<td class="td_l" class="nowrap"><?php echo $player_hist->team_name; ?></td>
							<!-- Player stats History - played start -->
							<td class="td_c"><?php
								echo ($present > 0) ? $present : '-';
								$career['played'] += $present;
								?></td>
							<!-- Player stats History - allevents start -->
							<?php
							if ($this->config['show_careerstats'])
							{
								if (!empty($staffstats))
								{
									foreach ($this->stats as $stat)
									{
										?>
										<td class="td_c">
											<?php echo (isset($this->staffstats[$stat->id][$player_hist->project_id]) ? $this->staffstats[$stat->id][$player_hist->project_id] : '-'); ?>
										</td>
										<?php
									}
								}
							}
							?>
							<!-- Player stats History - allevents end -->
						</tr>
						<?php
						$k=(1-$k);
					}
				}
				?>
				<tr class="career_stats_total">
					<td class="td_r" colspan="2"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_CAREER_TOTAL'); ?></b></td>
					<td class="td_c"><?php echo ($career['played'] > 0) ? $career['played'] : '-'; ?></td>
					<?php // stats per project
					if ($this->config['show_careerstats'])
					{
						if (!empty($historystats))
						{
							foreach ($this->stats as $stat)
							{
								?>
								<td class="td_c">
									<?php echo (isset($this->historystats[$stat->id]) ? $this->historystats[$stat->id] : '-'); ?>
								</td>
								<?php
							}
						}
					}
					?>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br/>
<!-- staff stats History END -->
