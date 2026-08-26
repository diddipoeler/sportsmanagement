<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editclub
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
$close = Factory::getApplication()->input->getInt('close', 0);
?>
<script>
Joomla.submitbutton = function (task) {
    const form = document.getElementById('adminForm');

    if (!form) {
        return;
    }

    if (
        task === 'editclub.cancel'
        || !document.formvalidator
        || document.formvalidator.isValid(form)
    ) {
        Joomla.submitform(task, form);
    }
};

<?php if ($close === 1) : ?>
document.addEventListener('DOMContentLoaded', function () {
    Joomla.submitbutton('editclub.cancel');
});
<?php endif; ?>
</script>
<form name="adminForm" id="adminForm" class="form-validate" method="post" action="<?php echo $this->uri->toString(); ?>">
    <fieldset>
        <div class="fltrt">
            <button type="button" onclick="Joomla.submitbutton('editclub.apply');">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE'); ?>
            </button>
            <button type="button" onclick="Joomla.submitbutton('editclub.save');">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE'); ?>
            </button>
            <button type="button" onclick="Joomla.submitbutton('editclub.cancel');">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
        <legend>
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_CLUB_LEGEND_DESC',
                '<i>' . htmlspecialchars((string) ($this->item->name ?? ''), ENT_QUOTES, 'UTF-8') . '</i>'
            ); ?>
        </legend>
    </fieldset>

    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']); ?>
    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php if (in_array($fieldset->name, ['details', 'picture'], true)) : ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true)); ?>
            <?php echo $this->loadTemplate($fieldset->name); ?>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

    <div class="clr"></div>
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="close" id="close" value="0">
    <input type="hidden" name="cid" value="<?php echo (int) ($this->item->id ?? 0); ?>">
    <input type="hidden" name="id" value="<?php echo (int) ($this->item->id ?? 0); ?>">
    <input type="hidden" name="p" value="<?php echo Factory::getApplication()->input->getInt('p', 0); ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
