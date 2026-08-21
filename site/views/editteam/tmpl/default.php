<?php
/** Joomla 5/6 frontend team editor template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$fieldsets = $this->form->getFieldsets();
?>
<form name="adminForm" id="adminForm" class="form-validate" method="post" action="<?php echo $this->uri->toString(); ?>" data-jsm-editteam-form>
    <fieldset class="adminform">
        <div class="btn-toolbar justify-content-end gap-2">
            <button type="button" class="btn btn-success" data-jsm-task="editteam.apply">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE'); ?>
            </button>
            <button type="button" class="btn btn-primary" data-jsm-task="editteam.save">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE'); ?>
            </button>
            <button type="button" class="btn btn-secondary" data-jsm-task="editteam.cancel" data-jsm-skip-validation>
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_EDIT') . ' ' . $this->item->name; ?></legend>
    </fieldset>

    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']); ?>
    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php if ($fieldset->name === 'details') : ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true)); ?>
            <?php echo $this->loadTemplate($fieldset->name); ?>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="p" value="<?php echo $this->projectId; ?>">
    <input type="hidden" name="tid" value="<?php echo $this->teamId; ?>">
    <input type="hidden" name="ptid" value="<?php echo $this->projectTeamId; ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
