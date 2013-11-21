<?php 
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();


?>
<script type="text/javascript">

function change_published () {
  if (document.adminForm.published0.checked == true) {
    var deaktiviert=true;
  } else {
    var deaktiviert=false;
  }
  document.adminForm.mode.disabled=deaktiviert;
  document.adminForm.overview.disabled=deaktiviert;
  document.adminForm.joker0.disabled=deaktiviert;
  document.adminForm.joker1.disabled=deaktiviert;
  document.adminForm.joker_limit_select0.disabled=deaktiviert;
  document.adminForm.joker_limit_select1.disabled=deaktiviert;
  document.adminForm.champ0.disabled=deaktiviert;
  document.adminForm.champ1.disabled=deaktiviert;

  document.adminForm.points_correct_result.disabled=deaktiviert;
    document.adminForm.points_correct_result_joker.disabled=deaktiviert;
  document.adminForm.points_correct_diff.disabled=deaktiviert;
    document.adminForm.points_correct_diff_joker.disabled=deaktiviert;
  document.adminForm.points_correct_draw.disabled=deaktiviert;
    document.adminForm.points_correct_draw_joker.disabled=deaktiviert;
  document.adminForm.points_correct_tendence.disabled=deaktiviert;
    document.adminForm.points_correct_tendence_joker.disabled=deaktiviert;
  document.adminForm.points_tipp.disabled=deaktiviert;
    document.adminForm.points_tipp_joker.disabled=deaktiviert;

    document.adminForm.joker_limit.disabled=deaktiviert;

    document.adminForm.points_tipp_champ.disabled=deaktiviert;

  if (deaktiviert == false){
  change_joker();
  change_jokerlimit();
  change_champ();
}
}

function change_joker () {
  if (document.adminForm.joker0.checked == true) {
    var deaktiviert=true;
  } else {
    var deaktiviert=false;
  }
  alert(deaktiviert);
  document.adminForm.points_correct_result_joker.disabled=deaktiviert;
  document.adminForm.points_correct_diff_joker.disabled=deaktiviert;
  document.adminForm.points_correct_draw_joker.disabled=deaktiviert;
  document.adminForm.points_correct_tendence_joker.disabled=deaktiviert;
  document.adminForm.points_tipp_joker.disabled=deaktiviert;
}

function change_jokerlimit () {
  if (document.adminForm.joker_limit_select0.checked == true) {
    var deaktiviert=true;
  } else {
    var deaktiviert=false;
  }
  document.adminForm.joker_limit.disabled=deaktiviert;
}

function change_champ () {
  if (document.adminForm.champ0.checked == true) {
    var deaktiviert=true;
  } else {
    var deaktiviert=false;
  }
  document.adminForm.points_tipp_champ.disabled=deaktiviert;
  document.adminForm.league_champ.disabled=deaktiviert;
}

</script>

<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
	<div class='col50'>
    
     <fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('predictionproject.store', this.form)">
				<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>
	</fieldset>
    
		
		
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
		echo $this->loadTemplate($fieldset->name);
		endforeach; ?>
		<?php echo JHtml::_('sliders.end'); ?>

	
	</div>    
            
</div>        

		<div class='clr'></div>
	<div>	
		<input type='hidden' name='id'					value='<?php echo $this->item->id; ?>' />
		<input type='hidden' name='task'					value='predictionproject.edit' />
		<input type='hidden' name='psapply'					value='1' />
	</div>
	<?php 
    echo JHTML::_('form.token'); 

    ?>
</form>
<script type="text/javascript">change_published();</script>