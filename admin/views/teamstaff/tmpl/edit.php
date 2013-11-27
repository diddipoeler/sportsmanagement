<?php 
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
?>

<!-- import the functions to move the events between selection lists	-->
<?php
//$version = urlencode(sportsmanagementHelper::getVersion());
//echo JHtml::script( 'JL_eventsediting.js?v='.$version, 'administrator/components/com_sportsmanagement/assets/js/' );
?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" id="adminForm" name="adminForm">

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
        
        <fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_PERSON_DETAILS'); ?></legend>
			<table class="adminformlist">
			<?php foreach($this->form->getFieldset('persondetails') as $field) :?>
				<tr><td><?php echo $field->label; ?></td>
				<td><?php echo $field->input;?>
                </td></tr>
			<?php endforeach; ?>
			</table>
		</fieldset>
        
	</div>
 <div class="width-40 fltrt">
		<?php
		echo JHtml::_('sliders.start');
		foreach ($fieldsets as $fieldset) :
			if ( $fieldset->name == 'details' || $fieldset->name == 'persondetails' ) :
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
        
<div class="clr"></div>   
<div>        
		<input type="hidden" name="eventschanges_check"	value="0" id="eventschanges_check" />
		<input type="hidden" name="project_team_id"		value="<?php echo $this->item->projectteam_id; ?>" />
    <input type="hidden" name="team_id"		value="<?php echo $this->team_id; ?>" />
    <input type="hidden" name="pid"		value="<?php echo $this->project_id; ?>" />
		
		<input type="hidden" name="task"				value="teamstaff.edit" />
	</div>
	<?php echo JHtml::_( 'form.token' ); ?>
</form>