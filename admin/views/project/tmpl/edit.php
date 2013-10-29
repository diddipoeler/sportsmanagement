<?php defined('_JEXEC') or die('Restricted access');

?>
<form action="index.php" method="post" id="adminForm">
<fieldset class="adminform">
			<legend><?php echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_PROJECT_LEGEND_DESC','<i>'.$this->project->name.'</i>'); ?></legend>
	<div class="col50">
	<?php
	echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));
	echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_DETAILS'), 'panel1');
	echo $this->loadTemplate('details');

	echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_DATE'), 'panel2');
	echo $this->loadTemplate('date');

	echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_PROJECT'), 'panel3');
	echo $this->loadTemplate('project');

	echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_COMPETITION'), 'panel4');
	echo $this->loadTemplate('competition');

	echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_FAVORITE'), 'panel5');
	echo $this->loadTemplate('favorite');

	echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_PICTURE'), 'panel6');
	echo $this->loadTemplate('picture');

	echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_EXTENDED'), 'panel7');
	echo $this->loadTemplate('extended');

	echo JHTML::_('tabs.end');
	?>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_joomleague" /> 
	<input type="hidden" name="task" value="" /> 
	<input type="hidden"name="oldseason" value="<?php echo $this->project->season_id; ?>" />
	<input type="hidden" name="oldleague" value="<?php echo $this->project->league_id; ?>" /> 
	<input type="hidden" name="cid[]" value="<?php echo $this->project->id; ?>" />
	<?php echo JHTML::_('form.token')."\n"; ?>
	</div>
	</fieldset>
</form>
