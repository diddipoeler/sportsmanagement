<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$renderForm = static function ($form): void {
    foreach ($form->getFieldsets() as $fieldset) {
        $fields = $form->getFieldset($fieldset->name);
        if (!$fields) {
            continue;
        }

        $legend = $fieldset->label ? Text::_((string) $fieldset->label) : ucfirst((string) $fieldset->name);
        ?>
        <fieldset class="options-form mb-4">
            <legend><?php echo $legend; ?></legend>
            <?php foreach ($fields as $field) : ?>
                <?php if (strtolower((string) $field->type) === 'hidden') : ?>
                    <?php echo $field->input; ?>
                    <?php continue; ?>
                <?php endif; ?>
                <div class="control-group mb-3">
                    <div class="control-label"><?php echo $field->label; ?></div>
                    <div class="controls"><?php echo $field->input; ?></div>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }
};

$projectId = (int) ($this->item->id ?? 0);
$tmpl = $this->tmpl !== '' ? '&tmpl=' . rawurlencode($this->tmpl) : '';
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=project&layout=edit&id=' . $projectId . $tmpl); ?>"
    method="post"
    name="adminForm"
    id="project-form"
    class="form-validate"
>
    <div class="row g-4">
        <div class="col-12 col-xxl-8">
            <?php $renderForm($this->form); ?>
        </div>

        <div class="col-12 col-xxl-4">
            <?php if ($this->checkextrafields && !empty($this->lists['ext_fields'])) : ?>
                <fieldset class="options-form mb-4">
                    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXTRAFIELDS'); ?></legend>
                    <?php foreach ((array) $this->lists['ext_fields'] as $extraField) : ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo htmlspecialchars((string) ($extraField->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></label>
                            <textarea class="form-control" name="extraf[]" rows="3"><?php echo htmlspecialchars((string) ($extraField->fvalue ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <input type="hidden" name="extra_id[]" value="<?php echo (int) ($extraField->id ?? 0); ?>">
                            <input type="hidden" name="extra_value_id[]" value="<?php echo (int) ($extraField->value_id ?? 0); ?>">
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>

            <?php if (is_object($this->extended) && method_exists($this->extended, 'getFieldsets')) : ?>
                <fieldset class="options-form mb-4">
                    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'); ?></legend>
                    <?php $renderForm($this->extended); ?>
                </fieldset>
            <?php endif; ?>

            <?php if (is_object($this->extendeduser) && method_exists($this->extendeduser, 'getFieldsets')) : ?>
                <fieldset class="options-form mb-4">
                    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED_USER'); ?></legend>
                    <?php $renderForm($this->extendeduser); ?>
                </fieldset>
            <?php endif; ?>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
