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

$itemId = (int) ($this->item->id ?? 0);
$fieldsets = $this->form->getFieldsets();
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jlextassociation&layout=edit&id=' . $itemId); ?>"
    method="post"
    name="adminForm"
    id="jlextassociation-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'jlextassociation-tabs', [
        'active' => 'jlextassociation-' . (array_key_first($fieldsets) ?: 'details'),
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php foreach ($fieldsets as $fieldsetName => $fieldset) : ?>
        <?php
        $fieldsetName = (string) $fieldsetName;
        $label = (string) ($fieldset->label ?? '');
        $description = (string) ($fieldset->description ?? '');
        ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'jlextassociation-tabs', 'jlextassociation-' . $fieldsetName, Text::_($label !== '' ? $label : $fieldsetName)); ?>
        <div class="options-form mb-4">
            <?php if ($description !== '') : ?>
                <p class="text-muted"><?php echo Text::_($description); ?></p>
            <?php endif; ?>
            <?php $renderFields($this->form->getFieldset($fieldsetName)); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
