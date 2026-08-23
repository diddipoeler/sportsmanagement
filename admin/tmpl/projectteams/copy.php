<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=projectteams&layout=copy&tmpl=component&pid=' . (int) $this->project_id); ?>"
    method="post"
    id="adminForm"
    name="adminForm"
>
    <fieldset class="options-form">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_COPY_DEST'); ?></legend>
        <div class="control-group">
            <div class="control-label">
                <label for="dest"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_PROJECT'); ?></label>
            </div>
            <div class="controls">
                <?php echo HTMLHelper::_(
                    'select.genericlist',
                    $this->copyProjectOptions,
                    'dest',
                    'class="form-select" required',
                    'value',
                    'text',
                    0
                ); ?>
            </div>
        </div>
    </fieldset>

    <?php foreach ($this->ptids as $ptid) : ?>
        <input type="hidden" name="ptids[]" value="<?php echo (int) $ptid; ?>">
    <?php endforeach; ?>
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="task" value="projectteams.storecopy">
    <?php echo HTMLHelper::_('form.token'); ?>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?php echo Text::_('JTOOLBAR_COPY'); ?></button>
        <button type="button" class="btn btn-secondary" onclick="window.parent.Joomla.Modal.getCurrent().close();">
            <?php echo Text::_('JCANCEL'); ?>
        </button>
    </div>
</form>
