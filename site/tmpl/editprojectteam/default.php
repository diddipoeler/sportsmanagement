<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editprojectteam
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$fieldsets = $this->form->getFieldsets();
?>
<script>
Joomla.submitbutton = function (task) {
    const form = document.getElementById('adminForm');

    if (!form) {
        return;
    }

    if (
        task === 'editprojectteam.cancel'
        || !document.formvalidator
        || document.formvalidator.isValid(form)
    ) {
        Joomla.submitform(task, form);
    }
};
</script>
<form name="adminForm" id="adminForm" class="form-validate" method="post" action="<?php echo $this->uri->toString(); ?>">
    <fieldset class="adminform">
        <div class="fltrt">
            <button type="button" onclick="Joomla.submitbutton('editprojectteam.apply');">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE'); ?>
            </button>
            <button type="button" onclick="Joomla.submitbutton('editprojectteam.save');">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE'); ?>
            </button>
            <button type="button" onclick="Joomla.submitbutton('editprojectteam.cancel');">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
        <legend>
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_EDIT') . ' ' . $this->item->name; ?>
        </legend>
    </fieldset>

    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']); ?>
    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php switch ($fieldset->name) :
            case 'details': ?>
                <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true)); ?>
                <?php echo $this->loadTemplate($fieldset->name); ?>
                <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
                <?php break; ?>
            <?php case 'picture': ?>
                <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true)); ?>
                <?php echo $this->loadTemplate($fieldset->name); ?>
                <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
                <?php break; ?>
        <?php endswitch; ?>
    <?php endforeach; ?>
    <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

    <div class="clr"></div>
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="ptid" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="p" value="<?php echo Factory::getApplication()->input->getInt('p', 0); ?>">
    <input type="hidden" name="tid" value="<?php echo Factory::getApplication()->input->getInt('tid', 0); ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
