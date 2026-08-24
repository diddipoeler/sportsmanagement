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

$renderNestedForm = static function ($form) use ($renderFields): void {
    foreach ($form->getFieldsets() as $fieldset) {
        $fields = $form->getFieldset($fieldset->name);
        if (!$fields) {
            continue;
        }
        ?>
        <fieldset class="options-form mb-3">
            <?php if (!empty($fieldset->label)) : ?>
                <legend><?php echo Text::_((string) $fieldset->label); ?></legend>
            <?php endif; ?>
            <?php $renderFields($fields); ?>
        </fieldset>
        <?php
    }
};

$tabId = static function (string $name): string {
    return 'player-' . (preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'details');
};

$visibleFieldsets = [];
foreach ($this->form->getFieldsets() as $fieldset) {
    $name = (string) $fieldset->name;
    if (in_array($name, ['extended', 'extra_fields'], true) || !$this->form->getFieldset($name)) {
        continue;
    }
    $visibleFieldsets[] = $fieldset;
}
$firstFieldset = reset($visibleFieldsets);
$activeTab = $firstFieldset ? $tabId((string) $firstFieldset->name) : 'player-details';
$playerId = (int) ($this->item->id ?? 0);
$tmpl = $this->tmpl !== '' ? '&tmpl=' . rawurlencode($this->tmpl) : '';
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=player&layout=edit&id=' . $playerId . $tmpl); ?>"
    method="post"
    name="adminForm"
    id="player-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'playerTabs', [
        'active' => $activeTab,
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php foreach ($visibleFieldsets as $fieldset) : ?>
        <?php
        $name = (string) $fieldset->name;
        $label = Text::_((string) ($fieldset->label ?: $name));
        echo HTMLHelper::_('uitab.addTab', 'playerTabs', $tabId($name), $label);
        ?>
        <div class="options-form mb-4">
            <?php $renderFields($this->form->getFieldset($name)); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php if ($this->checkextrafields && !empty($this->lists['ext_fields'])) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'playerTabs', 'player-extra-fields', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS')); ?>
        <div class="options-form mb-4">
            <?php foreach ((array) $this->lists['ext_fields'] as $extraField) : ?>
                <div class="mb-3">
                    <label class="form-label"><?php echo htmlspecialchars((string) ($extraField->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></label>
                    <textarea class="form-control" name="extraf[]" rows="3"><?php echo htmlspecialchars((string) ($extraField->fvalue ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <input type="hidden" name="extra_id[]" value="<?php echo (int) ($extraField->id ?? 0); ?>">
                    <input type="hidden" name="extra_value_id[]" value="<?php echo (int) ($extraField->value_id ?? 0); ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php if (is_object($this->extended) && method_exists($this->extended, 'getFieldsets')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'playerTabs', 'player-extended', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED')); ?>
        <div class="mb-4"><?php $renderNestedForm($this->extended); ?></div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php if (is_object($this->extendeduser) && method_exists($this->extendeduser, 'getFieldsets')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'playerTabs', 'player-extended-user', Text::_('COM_SPORTSMANAGEMENT_EXT_EXTENDED_USER_PREFERENCES')); ?>
        <div class="mb-4"><?php $renderNestedForm($this->extendeduser); ?></div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="jform[season_ids][]" value="0">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
