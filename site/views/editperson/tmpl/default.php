<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editperson
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

//echo ' person<br><pre>'.print_r($this->item,true).'</pre>'

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.id('editperson'))) {
			Joomla.submitform(task, document.getElementById('editperson'));
		}
	}
</script>
<form name="editperson" id="editperson" method="post" action="<?php echo JRoute::_('index.php'); ?>">
<?php

		?>
	<fieldset class="adminform">
	<div class="fltrt">
					<button type="button" onclick="Joomla.submitform('editperson.apply', this.form);">
						<?php echo JText::_('JAPPLY');?></button>
					<button type="button" onclick="Joomla.submitform('editperson.save', this.form);">
						<?php echo JText::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo JText::_('JCANCEL');?></button>
				</div>
	<legend>
  <?php 
  echo JText::sprintf('COM_SPORTSMANAGEMENT_PERSON_LEGEND_DESC','<i>'.$this->item->firstname.'</i>','<i>'.$this->item->lastname.'</i>');
  ?>
  </legend>
  </fieldset>
    
<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php 
            foreach($this->form->getFieldset('details') as $field) :
            
            //echo '<pre>'.print_r($field,true).'</pre>';
            ?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; 
                 
                if ( $field->name == 'country' )
                {
                echo JSMCountries::getCountryFlag($field->value);    
                }
                
                if ( $field->name == 'standard_playground' )
                {
                //echo sportsmanagementHelper::getPicturePlayground($field->value);
                $picture = sportsmanagementHelper::getPicturePlayground($field->value);
                //echo $picture;
                //echo JHtml::image($picture, 'Playground', array('title' => 'Playground','width' => '50' )); 
                //echo JHtml::_('image', $picture, 'Playground',array('title' => 'Playground','width' => '50' )); 
?>
<a href="<?php echo JURI::root().$picture;?>" title="<?php echo 'Playground';?>" class="modal">
<img src="<?php echo JURI::root().$picture;?>" alt="<?php echo 'Playground';?>" width="50" />
</a>
<?PHP                   
                }
                
                if ( $field->name == 'website' )
                {
                echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                }
                if ( $field->name == 'twitter' )
                {
                echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                }
                if ( $field->name == 'facebook' )
                {
                echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$field->value.'">';  
                }
                
                $suchmuster = array ("jform[","]");
                $ersetzen = array ('', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
                
                switch ($var_onlinehelp)
                {
                    case 'id':
                    break;
                    default:
                    if ( $field->type != 'Hidden')
                    {
                ?>
                <a	rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
									href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER.'SM-Backend-Felder:'.JFactory::getApplication()->input->getVar( "view").'-'.$var_onlinehelp; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','media/com_sportsmanagement/jl_images/help.png',
													JText::_('COM_SPORTSMANAGEMENT_HELP_LINK'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_HELP_LINK').'"');
									?>
								</a>
                
                <?PHP
                }
                break;
                }
                ?>
                </li>
			<?php 
            
            //echo $field->type;
            
            endforeach; 
            ?>
			</ul>
		</fieldset>
	</div>		

<div class="width-40 fltrt">
		<?php
		echo JHtml::_('sliders.start');
		foreach ($fieldsets as $fieldset) :
			if ( $fieldset->name == 'details' ||  $fieldset->name == 'seasons' ) :
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

    
	<input type="hidden" name="assignperson" value="0" id="assignperson" />
	<input type="hidden" name="option" value="com_sportsmanagement" /> 
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token')."\n"; ?>
	
</form>
