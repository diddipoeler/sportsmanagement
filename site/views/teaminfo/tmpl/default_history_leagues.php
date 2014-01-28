<?php defined('_JEXEC') or die('Restricted access'); ?>



<fieldset>
<legend>
<strong>
<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY_OVERVIEW_SUMMARY'); ?>
</strong>
</legend>


<table width="100%" class='adminlist'>

<thead>
<tr class="sectiontableheader">
<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES' ); ?>
</th>
<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL' ); ?>
</th>

<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; ">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS' ); ?>
</th>

</tr>
</thead>

<?php
	$k=0;
	foreach ($this->leaguerankoverviewdetail as $league => $summary)
	{
	?>
	<tr
  class="<?php echo ($k == 0)? 'sectiontableentry1' : 'sectiontableentry2'; ?>">
	<td><?php echo $league; ?></td>
	<td><?php echo $summary->match; ?></td>
	
	<td><?php echo $summary->won; echo ' / '; ?>
	<?php echo $summary->draw; echo ' / '; ?>
	<?php echo $summary->loss; ?></td>
	
	<td><?php echo $summary->goalsfor; echo ' : '; ?>
	<?php echo $summary->goalsagain; ?></td>
	
	</tr>
	<?php
	$k = 1 - $k;
	}
	?>

</table>
</fieldset>


