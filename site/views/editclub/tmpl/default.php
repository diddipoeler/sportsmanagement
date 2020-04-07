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
 * @subpackage editclub
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<form name="adminForm" id="adminForm" method="post" action="<?php echo $this->uri->toString(); ?>">

<?php
		// Save and close
		$close = Factory::getApplication()->input->getInt('close', 0);

if ($close == 1)
{
	?><script>
			window.addEvent('domready', function() {
				$('cancel').onclick();  
			});
			</script>
	<?php
}
	?>
		<fieldset>
		<div class="fltrt">
					<button type="button" onclick="Joomla.submitform('editclub.apply', this.form);">
		<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE');?></button>
					<button type="button" onclick="Joomla.submitform('editclub.save', this.form);">
		<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE');?></button>
					<button type="button" onclick="Joomla.submitform('editclub.cancel', this.form);">
<?php echo Text::_('JCANCEL');?></button>
				</div>
			<legend>
		<?php
		echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_CLUB_LEGEND_DESC', '<i>' . $this->item->name . '</i>');
		?>
	  </legend>
</fieldset>

		  
	  
			
<?php

echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));

foreach ($fieldsets as $fieldset)
:
	switch ($fieldset->name)
	{
		case 'details':
			// Case 'picture':
			// case 'extended':
			echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));
			echo $this->loadTemplate($fieldset->name);
			echo HTMLHelper::_('bootstrap.endTab');
		break;
	}
endforeach;

echo HTMLHelper::_('bootstrap.endTabSet');

?>
<div class="clr"></div>
<input type="hidden" name="option" value="com_sportsmanagement" />
<input type="hidden" name="close" id="close" value="0"/>
<input type="hidden" name="cid" value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="p" value="<?php echo Factory::getApplication()->input->getInt('p', 0); ?>" />   
<input type="hidden" name="task" value="" />	
<?php echo HTMLHelper::_('form.token'); ?>
</form>
