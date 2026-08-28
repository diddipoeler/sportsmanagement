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
$params = $this->form->getFieldsets('params');
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=sportsmanagement&layout=edit&id=' . $itemId); ?>"
    method="post"
    name="adminForm"
    id="sportsmanagement-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'sportsmanagementTabs', [
        'active' => 'sportsmanagement-details',
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sportsmanagementTabs', 'sportsmanagement-details', Text::_('COM_HELLOWORLD_HELLOWORLD_DETAILS')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('details')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php foreach ($params as $name => $fieldset) : ?>
        <?php
        $tabId = 'sportsmanagement-' . preg_replace('/[^a-z0-9_-]+/i', '-', (string) $name);
        $label = trim((string) ($fieldset->label ?? '')) !== ''
            ? Text::_((string) $fieldset->label)
            : ucfirst((string) $name);
        ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'sportsmanagementTabs', $tabId, $label); ?>
        <div class="options-form mb-4">
            <?php if (trim((string) ($fieldset->description ?? '')) !== '') : ?>
                <p class="alert alert-info"><?php echo $this->escape(Text::_((string) $fieldset->description)); ?></p>
            <?php endif; ?>
            <?php $renderFields($this->form->getFieldset($name)); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
