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

$statisticId = (int) ($this->item->id ?? 0);
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=statistic&layout=edit&id=' . $statisticId); ?>"
    method="post"
    name="adminForm"
    id="statistic-form"
    class="form-validate"
>
    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <fieldset class="options-form mb-4">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
                <?php $renderFields($this->form->getFieldset('details')); ?>
            </fieldset>
        </div>

        <div class="col-12 col-xl-5">
            <fieldset class="options-form mb-4">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'); ?></legend>
                <?php $renderFields($this->form->getFieldset('picture')); ?>
            </fieldset>
        </div>
    </div>

    <fieldset class="options-form mb-4">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_PARAMS'); ?></legend>
        <?php if ($this->formparams) : ?>
            <?php foreach ($this->formparams->getFieldsets() as $fieldset) : ?>
                <?php $fields = $this->formparams->getFieldset($fieldset->name); ?>
                <?php if (!$fields) : ?>
                    <?php continue; ?>
                <?php endif; ?>
                <fieldset class="options-form mb-3">
                    <?php if (!empty($fieldset->label)) : ?>
                        <legend><?php echo Text::_((string) $fieldset->label); ?></legend>
                    <?php endif; ?>
                    <?php $renderFields($fields); ?>
                </fieldset>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-muted mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
        <?php endif; ?>
    </fieldset>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
