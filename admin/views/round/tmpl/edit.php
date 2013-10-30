<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" id="adminForm" name="adminForm" >
	<div class="col50">
	<?php
	echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));
	echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'), 'panel1');
	echo $this->loadTemplate('details');

	echo JHTML::_('tabs.end');
	?>		
	</div>
	<div class="clr"></div>

	<input type="hidden" name="project_id" value="<?php echo $this->project->id; ?>" />
	<input type="hidden" name="pid" value="<?php echo $this->project->id; ?>" />
	<input type="hidden" name="task" value="round.edit" />
	<?php echo JHTML::_('form.token'); ?>
</form>