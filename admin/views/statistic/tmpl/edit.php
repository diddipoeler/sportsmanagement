<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

// echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PARAM'), 'panel3');
// echo $this->loadTemplate('param');
// echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_GPARAM'), 'panel4');
// echo $this->loadTemplate('gparam');

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

<div class="width-40 fltrt">
		<?php
		echo JHtml::_('sliders.start');
		foreach ($fieldsets as $fieldset) :
			if ($fieldset->name == 'details') :
				continue;
			endif;
			echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
		if (isset($fieldset->description) && !empty($fieldset->description)) :
				echo '<p class="tab-description">'.JText::_($fieldset->description).'</p>';
			endif;
		//echo $this->loadTemplate($fieldset->name);
        $this->fieldset = $fieldset->name;
        echo $this->loadTemplate('fieldsets');
		endforeach; ?>
		<?php echo JHtml::_('sliders.end'); ?>

	
	</div>
    	
	<div class="clr"></div>
  	
 
	<div>
		<input type="hidden" name="task" value="statistic.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
