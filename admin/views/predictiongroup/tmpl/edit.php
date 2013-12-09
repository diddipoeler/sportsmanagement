<?php 
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" >
<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('details') as $field) :?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; 
                    
                ?></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>
    
	<div class="clr"></div>
	
	
	<input type="hidden" name="task" value="predictiongroup.edit" />
	<?php echo JHtml::_('form.token')."\n"; ?>
</fieldset>		
</form>