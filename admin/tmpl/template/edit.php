<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$id = (int) ($this->item->id ?? 0);
$fieldsets = $this->form->getFieldsets();
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=template&layout=edit&id=' . $id . '&pid=' . (int) $this->project_id); ?>"
      method="post" name="adminForm" id="template-form" class="form-validate">
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <label class="form-label fw-semibold" for="new_id"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TEMPLATE'); ?></label>
            <?php echo $this->lists['templates'] ?? ''; ?>
        </div>
        <div class="col-lg-6 d-flex align-items-end">
            <div class="text-muted">
                <?php echo $this->escape((string) ($this->project->name ?? '')); ?>
                <?php if ($this->templatename !== '') : ?>
                    &mdash; <?php echo $this->escape($this->templatename); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($fieldsets) : ?>
        <?php echo HTMLHelper::_('uitab.startTabSet', 'templateSettingsTabs', [
            'active' => 'template-settings-' . preg_replace('/[^a-z0-9_-]+/i', '-', (string) array_key_first($fieldsets)),
            'recall' => true,
            'breakpoint' => 768,
        ]); ?>

        <?php foreach ($fieldsets as $fieldset) :
            $fieldsetName = (string) $fieldset->name;
            $tabId = 'template-settings-' . preg_replace('/[^a-z0-9_-]+/i', '-', $fieldsetName);
            $label = (string) ($fieldset->label ?: $fieldsetName);
        ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'templateSettingsTabs', $tabId, Text::_($label)); ?>
            <div class="options-form mb-4">
                <?php if (!empty($fieldset->description)) : ?>
                    <div class="alert alert-info"><?php echo Text::_((string) $fieldset->description); ?></div>
                <?php endif; ?>

                <?php foreach ($this->form->getFieldset($fieldsetName) as $field) : ?>
                    <?php if (strtolower((string) $field->type) === 'hidden') : ?>
                        <?php echo $field->input; ?>
                    <?php else : ?>
                        <div class="control-group mb-3">
                            <div class="control-label"><?php echo $field->label; ?></div>
                            <div class="controls"><?php echo $field->input; ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endforeach; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    <?php endif; ?>

    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
