<?php 
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" id="adminForm" name="adminForm">
<fieldset class="adminform">
			<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_LEGEND_DESC','<i>'.$this->item->name.'</i>'); ?></legend>
	<div class="col50">
	<?php
	echo JHtml::_('tabs.start','tabs', array('useCookie'=>1));
	echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'), 'panel1');
	echo $this->loadTemplate('details');

	echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_DATE'), 'panel2');
	echo $this->loadTemplate('date');

	echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PROJECT'), 'panel3');
	echo $this->loadTemplate('project');

	echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_COMPETITION'), 'panel4');
	echo $this->loadTemplate('competition');

	echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_FAVORITE'), 'panel5');
	echo $this->loadTemplate('favorite');

	echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'), 'panel6');
	echo $this->loadTemplate('picture');

	echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel7');
	echo $this->loadTemplate('extended');

	echo JHtml::_('tabs.end');
	?>
	<div class="clr"></div>
	
	<input type="hidden" name="task" value="project.edit" /> 
	<input type="hidden"name="oldseason" value="<?php echo $this->item->season_id; ?>" />
	<input type="hidden" name="oldleague" value="<?php echo $this->item->league_id; ?>" /> 
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<?php echo JHtml::_('form.token')."\n"; ?>
	</div>
	</fieldset>
</form>

<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   