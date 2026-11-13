<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$renderFields = static function (array $fields): void {
    foreach ($fields as $field) {
        if (strtolower((string) $field->type) === 'hidden') {
            echo $field->input;
            continue;
        }
        ?>
        <div class="control-group mb-3">
            <div class="control-label"><?php echo $field->label; ?></div>
            <div class="controls"><?php echo $field->input; ?></div>
        </div>
        <?php
    }
};

$roundId = (int) ($this->item->id ?? 0);
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=round&layout=edit&id=' . $roundId . '&pid=' . $this->project_id); ?>"
    method="post"
    name="adminForm"
    id="round-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'round-edit-tabs', ['active' => 'details']); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'round-edit-tabs', 'details', Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS')); ?>
    <fieldset class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('details')); ?>
    </fieldset>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'round-edit-tabs', 'picture', Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE')); ?>
    <fieldset class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('picture')); ?>
    </fieldset>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'round-edit-tabs', 'notes', Text::_('COM_SPORTSMANAGEMENT_TABS_NOTES')); ?>
    <fieldset class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('notes')); ?>
    </fieldset>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="pid" value="<?php echo $this->project_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
