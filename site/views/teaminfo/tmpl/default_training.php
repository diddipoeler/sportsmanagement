<?php defined('_JEXEC') or die('Restricted access'); ?>

<table width="100%" class="contentpaneopen">
	<tr>
		<td class="contentheading"><?php echo '&nbsp;' . JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING'); ?>
		</td>
	</tr>
</table>

<?php

?>

<table class="fixtures" >
	<tr class="sectiontableheader">
		<td><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_DAY'); ?></td>
		<td><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_START'); ?></td>
		<td><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_END'); ?></td>
		<td><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_LOCATION'); ?></td>
		<td><?php //echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NOTE'); ?></td>
	</tr>
	<?php
	$k=0;
	$count_note=0;
	if (!empty($this->trainingData))
	{
		foreach ($this->trainingData as $training)
		{
			$hours=($training->time_start / 3600); $hours=(int)$hours;
			$mins=(($training->time_start - (3600*$hours)) / 60); $mins=(int)$mins;
			$startTime=sprintf('%02d',$hours).':'.sprintf('%02d',$mins);
			$hours=($training->time_end / 3600); $hours=(int)$hours;
			$mins=(($training->time_end - (3600*$hours)) / 60); $mins=(int)$mins;
			$endTime=sprintf('%02d',$hours).':'.sprintf('%02d',$mins);
			?>
			<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
				<td><?php echo $this->daysOfWeek[$training->dayofweek]; ?></td>
				<td><?php echo $startTime; ?></td>
				<td><?php echo $endTime; ?></td>
				<td><?php echo $training->place; ?></td>
				
				
				<?php if($training->notes != ""): 
				$count_note++;
				?>
				<td>*<sup><?php echo $count_note; ?></sup></td>
				<?php else: ?>
				<td><?php echo $training->notes; ?></td>
				<?php endif; ?>
		
			</tr>
		<?php
		$k = 1 - $k;
		}
		$count_note = 0;
		$k=0;
		foreach ($this->trainingData as $training)
		{
		
		?>
		
		<?php if($training->notes != ""):
		$count_note++;
		?>
		<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>note" >
			<td align="right">*<sup><?php echo $count_note; ?></sup></td>
			<td align="left" colspan="4" ><?php echo $training->notes; ?></td>
		</tr>
		<?php endif; ?>
	<?php	
		$k = 1 - $k;
		}
	}
	else
	{
	?>
	<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
		<td align="left" colspan="5" >  <?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NODATA'); ?> </td>
	</tr>
	<?php
	}
	
	?>
</table>
<br/>
<?php

?>