<?php defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" id="adminForm">
<fieldset class="adminform">
			<legend>
      <?php 
      echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_SEASON_LEGEND_DESC','<i>'.$this->item->name.'</i>'); 
      ?>
      </legend>
	<div class="col50">
<?php
echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));
echo JHTML::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'), 'panel1');
echo $this->loadTemplate('details');

echo JHTML::_('tabs.end');
?>
	</div>
	<div class="clr"></div>
	
	<input type="hidden" name="task" value="season.edit" />
	<?php echo JHTML::_('form.token')."\n"; ?>
</fieldset>		
</form>