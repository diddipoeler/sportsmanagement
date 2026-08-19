<?php
/** Project-team copy modal. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
    <fieldset>
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_COPY_DEST'); ?></legend>
        <div class="control-group">
            <div class="control-label">
                <label for="dest"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_PROJECT'); ?></label>
            </div>
            <div class="controls">
                <?php echo $this->lists['projects']; ?>
            </div>
        </div>
    </fieldset>

    <?php foreach ($this->ptids as $ptid) : ?>
        <input type="hidden" name="ptids[]" value="<?php echo (int) $ptid; ?>" />
    <?php endforeach; ?>
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>" />
    <input type="hidden" name="task" value="projectteams.storecopy" />
    <?php echo HTMLHelper::_('form.token'); ?>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">
            <?php echo Text::_('JTOOLBAR_COPY'); ?>
        </button>
    </div>
</form>
