<?php 
defined('_JEXEC') or die('Restricted access');

$extended = $this->form->getFieldsets('extended');
?>
<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_EXTENDED' );?>
			</legend>
<?php foreach ($extended as $name => $fieldset): ?>
		
	<?php if (isset($fieldset->description) && trim($fieldset->description)): ?>
		<p class="tip"><?php echo $this->escape(JText::_($fieldset->description));?></p>
	<?php endif;?>
		<fieldset class="panelform" >
			<ul class="adminformlist">
	<?php foreach ($this->form->getFieldset($name) as $field) : ?>
				<li><?php echo $field->label; ?><?php echo $field->input; ?></li>
	<?php endforeach; ?>
			</ul>
		</fieldset>
<?php endforeach; ?>
 

<?PHP

?>
</fieldset>