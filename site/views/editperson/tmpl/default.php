<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage editperson
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
if (document.formvalidator.isValid(document.id('editperson'))) {
Joomla.submitform(task, document.getElementById('editperson'));
}
}
</script>
<form name="adminForm" id="adminForm" method="post" action="<?php echo $this->uri->toString(); ?>">
<?php

?>
<fieldset class="adminform">
<div class="fltrt">
<button type="button" onclick="Joomla.submitform('editperson.apply', this.form);">
    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE');?></button>
<button type="button" onclick="Joomla.submitform('editperson.save', this.form);">
    <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE');?></button>
<button type="button" onclick="Joomla.submitform('editperson.cancel', this.form);">
    <?php echo Text::_('JCANCEL');?></button>
</div>
<legend>
<?php
echo Text::sprintf('COM_SPORTSMANAGEMENT_PERSON_LEGEND_DESC', '<i>'.$this->item->firstname.'</i>', '<i>'.$this->item->lastname.'</i>');
?>
</legend>
</fieldset>

<?php
echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));  
foreach ($fieldsets as $fieldset) :

    switch ( $fieldset->name )
    {
    case 'details':
        //case 'picture':
        //case 'extended':
        echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));
        echo $this->loadTemplate($fieldset->name);
        echo HTMLHelper::_('bootstrap.endTab');
        break;  
    }

endforeach;

echo HTMLHelper::_('bootstrap.endTabSet');
?>  
  
  
<div class="clr"></div>
<input type="hidden" name="assignperson" value="0" id="assignperson" />
<input type="hidden" name="option" value="com_sportsmanagement" />
<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="pid" value="<?php echo $this->item->id; ?>" />   
  
<input type="hidden" name="p" value="<?php echo Factory::getApplication()->input->getInt('p', 0); ?>" />
<input type="hidden" name="tid" value="<?php echo Factory::getApplication()->input->getInt('tid', 0); ?>" />
  
<input type="hidden" name="task" value="" />
<?php echo HTMLHelper::_('form.token')."\n"; ?>
</form>
