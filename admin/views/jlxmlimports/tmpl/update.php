<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id='editcell'>
	<a name='page_top'></a>
	<table class='adminlist'>
		<thead><tr><th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TABLE_TITLE_4'); ?></th></tr></thead>
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
    
    
	if (JComponentHelper::getParams($this->option)->get('show_debug_info',0))
	{
		?><fieldset>
			<legend><?php echo JText::_('Post data from importform was:'); ?></legend>
			<table class='adminlist'><tr><td><?php echo '<pre>'.print_r($this->xml,true).'</pre>'; ?></td></tr></table>
		</fieldset><?php
	}
	?>
</div>
