<?php
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

$clubnameId = (int) ($this->item->id ?? 0);
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=clubname&layout=edit&id=' . $clubnameId); ?>"
    method="post"
    name="adminForm"
    id="clubname-form"
    class="form-validate"
>
    <fieldset class="options-form mb-4">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
        <?php $renderFields($this->form->getFieldset('details')); ?>
    </fieldset>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
