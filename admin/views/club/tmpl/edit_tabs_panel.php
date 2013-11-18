<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
//jimport( 'joomla.html.html.tabs' );
jimport('joomla.html.pane');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');

$options = array(
    'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
    'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
    'useCookie' => true, // this must not be a string. Don't use quotes.
);

?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
 
<div  class="col50">
<?php
	echo JHtml::_('tabs.start', 'config-tabs-'.'_configuration', $options);
		$fieldSets = $this->form->getFieldsets();
		
        foreach ($fieldSets as $name => $fieldSet) :
			$label = empty($fieldSet->label) ? $this->option.'_'.$name.'_FIELDSET_LABEL' : $fieldSet->label;
			echo JHtml::_('tabs.panel', JText::_($label), 'publishing-details');
			if (isset($fieldSet->description) && !empty($fieldSet->description)) :
				echo '<p class="tab-description">'.JText::_($fieldSet->description).'</p>';
			endif;
            
            //echo 'fieldsetname - '.$name.'<br>';
            
	?>
			<div style="position:relative;" class="width-60 fltlft">
            <fieldset class="adminform">
            <legend><?php echo JText::_($fieldSet->label); ?></legend>
			<?php
			foreach ($this->form->getFieldset($name) as $field):
			?>
			
				<?php if (!$field->hidden) : ?>
				<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			
			<?php
			endforeach;
                       
            
			?>
			</fieldset>
</div>
<?php  
switch ($name)
            {
            case 'details':
            echo $this->loadTemplate('maps');
            break;    
            case 'extended':
            echo $this->loadTemplate('extended');
            break;
            case 'extra_fields':
            echo $this->loadTemplate('extra_fields');
            break;
            }
            ?>
	<div class="clr"></div>
	<?php
		endforeach;
	echo JHtml::_('tabs.end');
	?>

        

	
	</div>
 <div class="clr"></div>
	<div>
		<input type="hidden" name="task" value="club.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
