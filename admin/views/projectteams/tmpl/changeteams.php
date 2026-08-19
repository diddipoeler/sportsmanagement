<?php
/** Project-team replacement modal. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h3><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGEASSIGN_TEAMS'); ?></h3>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" type="button" onclick="Joomla.submitform('projectteam.storechangeteams', this.form);">
                    <?php echo Text::_('JSAVE'); ?>
                </button>
                <button class="btn btn-secondary" type="button" onclick="Joomla.submitform('projectteam.cancelmodal', this.form);">
                    <?php echo Text::_('JCANCEL'); ?>
                </button>
            </div>
        </div>

        <div class="row fw-bold border-bottom py-2">
            <div class="col-md-1">#</div>
            <div class="col-md-2"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGE'); ?></div>
            <div class="col-md-4"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_OLD_TEAM'); ?></div>
            <div class="col-md-5"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_NEW_TEAM'); ?></div>
        </div>

        <?php foreach ($this->projectteam as $index => $row) :
            $checkboxId = 'oldteam' . (int) $row->id; ?>
            <div class="row align-items-center border-bottom py-2">
                <div class="col-md-1"><?php echo $index + 1; ?></div>
                <div class="col-md-2">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="oldteamid[]"
                        id="<?php echo $checkboxId; ?>"
                        value="<?php echo (int) $row->id; ?>"
                    />
                </div>
                <div class="col-md-4"><?php echo htmlspecialchars((string) $row->name, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="col-md-5">
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['all_teams'],
                        'newteamid[' . (int) $row->id . ']',
                        'class="form-select" onchange="document.getElementById(\'' . $checkboxId . '\').checked=true"',
                        'value',
                        'text',
                        0
                    ); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_sportsmanagement" />
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
