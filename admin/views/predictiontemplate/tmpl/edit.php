<?php defined('_JEXEC') or die('Restricted access');

JHTMLBehavior::formvalidation();
JHTML::_('behavior.tooltip');JHTML::_('behavior.modal');

//$component_text = 'COM_JOOMLEAGUE_';

$i    = 1;
?>
<style type="text/css">
	<!--
	fieldset.panelform label, fieldset.panelform div.paramrow label, fieldset.panelform span.faux-label {
		max-width: 255px;
		min-width: 255px;
		padding: 0 5px 0 0;
	}
	-->
</style>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	
	<?php
	
	?>
	<fieldset class="adminform">
		<legend><?php echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_TEMPLATE_LEGEND', '<i>' . JText::_('COM_JOOMLEAGUE_FES_' . strtoupper($this->form->getName()) . '_NAME') . '</i>', '<i>' . $this->predictionGame->name . '</i>'); ?></legend>
		<fieldset class="adminform">
			<?php
			echo JText::_('COM_JOOMLEAGUE_FES_' . strtoupper($this->form->getName()) . '_DESCR');
			?>
		</fieldset>

		<?php
		echo JHtml::_('tabs.start','tabs', array('useCookie'=>1));
        $fieldSets = $this->form->getFieldsets();
        foreach ($fieldSets as $name => $fieldSet) :
            $label = $fieldSet->name;
            echo JHtml::_('tabs.panel',JText::_($label), 'panel'.$i++);
			?>
			<fieldset class="panelform">
				<?php
				if (isset($fieldSet->description) && !empty($fieldSet->description)) :
					echo '<fieldset class="adminform">'.JText::_($fieldSet->description).'</fieldset>';
				endif;
				?>
				<ul class="config-option-list">
				<?php foreach ($this->form->getFieldset($name) as $field): ?>
					<li>
					<?php if (!$field->hidden) : ?>
					<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
					</li>
				<?php endforeach; ?>
				</ul>
			</fieldset>
 
    <div class="clr"></div>
    <?php endforeach; ?>
    <?php echo JHtml::_('tabs.end'); ?>
	<div>		
		<input type='hidden' name='user_id' value='<?php echo $this->user->id; ?>'/>
		<input type="hidden" name="cid[]" value="<?php echo $this->template->id; ?>"/>
		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
