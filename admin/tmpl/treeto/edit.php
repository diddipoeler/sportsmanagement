<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$id = (int) ($this->item->id ?? 0);
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=treeto&layout=edit&id=' . $id . '&pid=' . (int) $this->project_id); ?>"
      method="post" name="adminForm" id="treeto-form" class="form-validate">
    <div class="options-form">
        <?php foreach ($this->form->getFieldset('details') as $field) : ?>
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

    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
