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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
//jimport('joomla.html.pane');
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
<form name="editperson" id="editperson" method="post" action="<?php echo Route::_('index.php'); ?>">
<?php

		?>
	<fieldset class="adminform">
	<div class="fltrt">
					<button type="button" onclick="Joomla.submitform('editperson.apply', this.form);">
						<?php echo Text::_('JAPPLY');?></button>
					<button type="button" onclick="Joomla.submitform('editperson.save', this.form);">
						<?php echo Text::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo Text::_('JCANCEL');?></button>
				</div>
	<legend>
  <?php 
  echo Text::sprintf('COM_SPORTSMANAGEMENT_PERSON_LEGEND_DESC','<i>'.$this->item->firstname.'</i>','<i>'.$this->item->lastname.'</i>');
  ?>
  </legend>
  </fieldset>
 
<?php 
echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));    
foreach ($fieldsets as $fieldset) :
echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true)); 
echo HTMLHelper::_('bootstrap.endTab');
endforeach; 

echo HTMLHelper::_('bootstrap.endTabSet');
?>	
	
	
<div class="clr"></div>

    
	<input type="hidden" name="assignperson" value="0" id="assignperson" />
	<input type="hidden" name="option" value="com_sportsmanagement" /> 
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token')."\n"; ?>
	
</form>
