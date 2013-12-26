<div id='editcell'>
	<a name='page_top'></a>
	<table class='adminlist'>
		<thead><tr><th><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_XML_IMPORT_TABLE_TITLE_3'); ?></th></tr></thead>
		<tbody><tr><td><?php echo '&nbsp;'; ?></td></tr></tbody>
	</table>
	<?php
	if (is_array($this->importData))
	{
		foreach ($this->importData as $key => $value)
		{
			?>
			<fieldset>
				<legend><?php echo JText::_($key); ?></legend>
				<table class='adminlist'><tr><td><?php echo $value; ?></td></tr></table>
			</fieldset>
			<?php
		}
	}
	if (JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0))
	{
		?><fieldset>
			<legend><?php echo JText::_('Post data from importform was:'); ?></legend>
			<table class='adminlist'><tr><td><?php echo '<pre>'.print_r($this->postData,true).'</pre>'; ?></td></tr></table>
		</fieldset><?php
	}
	?>
</div>
<p style='text-align:right;'><a href='#page_top'><?php echo JText::_('top'); ?></a></p>
<?php
if (JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0))
{
	echo '<center><hr>';
		echo JText::sprintf('Memory Limit is %1$s',ini_get('memory_limit')) . '<br />';
		echo JText::sprintf('Memory Peak Usage was %1$s Bytes',number_format(memory_get_peak_usage(true),0,'','.')) . '<br />';
		echo JText::sprintf('Time Limit is %1$s seconds',ini_get('max_execution_time')) . '<br />';
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = ($endtime - $this->starttime);
		echo JText::sprintf('This page was created in %1$s seconds',$totaltime);
	echo '<hr></center>';
}
?>