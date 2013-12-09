<?php 
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');


$params = $this->form->getFieldsets('params');
// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();


?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" >

<div class="width-100 fltlft">
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
		
		<div>
		<input type="hidden" name="user_id" value="0" />
		<input type="hidden" name="project_id" value="0" />
		<input type="hidden" name="prediction_id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="task" value="predictiongame.edit" />
	</div>
<?php echo JHtml::_('form.token')."\n"; ?>
</form>