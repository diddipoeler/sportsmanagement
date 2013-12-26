<?php
if (JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0))
{
	echo '<p>'.JText::sprintf('JL_ADMIN_DFBNET_IMPORT_HINT2',$this->revisionDate).'</p>';
}

$url = 'components/com_joomleague/extensions/jlextdfbnetplayerimport/admin/assets/images/dfbnet-logo.gif';
$alt = 'Lmo Logo';
// $attribs['width'] = '170px';
// $attribs['height'] = '26px';
$attribs['align'] = 'left';
$logo = JHtml::_('image', $url, $alt, $attribs);

// create the CSS
$iconStyle = '.icon-48-dfbnet { background-image: url(components/com_joomleague/extensions/jlextdfbnetplayerimport/admin/assets/images/dfbnet-logo-20.gif) }';

// add the CSS to the document
$document =& JFactory::getDocument();
$document->addStyleDeclaration($iconStyle);
// set the toolbar title and icon suffix
JToolBarHelper::title('JL_ADMIN_DFBNET_IMPORT_TITLE_3_3', 'dfbnet');


?>
<div id='editcell'>
	<a name='page_top'></a>
	<table class='adminlist'>
		<thead><tr><th>
    <?php echo JHtml::_('image', $url, $alt, $attribs);; ?>
    <?php echo JText::_('JL_ADMIN_DFBNET_IMPORT_TABLE_TITLE_3'); ?></th></tr></thead>
		<tbody><tr><td><?php echo '&nbsp;'; ?></td></tr></tbody>
	</table>
	<?php
	
	/*
	echo 'tmpl info<br>';
	echo '<pre>';
  print_r($this->importData);
  echo '</pre>';
  */
    
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