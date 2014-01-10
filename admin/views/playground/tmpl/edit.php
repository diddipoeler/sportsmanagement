<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
jimport('joomla.html.pane');
$params = $this->form->getFieldsets('params');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
 
	
<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('details') as $field) :?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; 
                
                if ( $field->name == 'jform[country]' )
                {
                echo Countries::getCountryFlag($field->value);    
                }
                if ( $field->name == 'jform[club_id]' )
                {
                //echo sportsmanagementHelper::getPictureClub($field->value); 
                $picture = sportsmanagementHelper::getPictureClub($field->value);
                //echo $picture;
                //echo JHtml::image($picture, 'Club', array('title' => 'Club','width' => '50' )); 
                echo JHtml::_('image', $picture, 'Club',array('title' => 'Club','width' => '50px' ));  
                }
                
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
		<input type="hidden" name="task" value="playground.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   