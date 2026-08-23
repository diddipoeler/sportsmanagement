<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=projectteams&layout=changeteams&tmpl=component&pid=' . (int) $this->project_id); ?>"
    method="post"
    id="adminForm"
    name="adminForm"
>
    <fieldset class="options-form">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGEASSIGN_TEAMS'); ?></legend>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGE'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_OLD_TEAM'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_NEW_TEAM'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->projectteam as $index => $row) :
                        $rowId = (int) $row->id;
                        $checkboxId = 'oldteam' . $rowId; ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <input class="form-check-input" type="checkbox" name="oldteamid[]" id="<?php echo $checkboxId; ?>" value="<?php echo $rowId; ?>">
                            </td>
                            <td>
                                <?php echo $this->escape((string) $row->name); ?>
                                <?php if (!empty($row->seasonname)) : ?>
                                    <span class="text-muted">(<?php echo $this->escape((string) $row->seasonname); ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <select
                                    class="form-select"
                                    name="newteamid[<?php echo $rowId; ?>]"
                                    onchange="document.getElementById('<?php echo $checkboxId; ?>').checked=true"
                                >
                                    <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'); ?></option>
                                    <?php foreach ($this->replacementTeamOptions as $option) : ?>
                                        <option value="<?php echo (int) ($option->value ?? 0); ?>">
                                            <?php echo $this->escape((string) ($option->text ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </fieldset>

    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="task" value="projectteam.storechangeteams">
    <?php echo HTMLHelper::_('form.token'); ?>

    <div class="mt-3 d-flex gap-2 justify-content-end">
        <button class="btn btn-primary" type="submit"><?php echo Text::_('JSAVE'); ?></button>
        <button class="btn btn-secondary" type="button" onclick="window.parent.Joomla.Modal.getCurrent().close();">
            <?php echo Text::_('JCANCEL'); ?>
        </button>
    </div>
</form>
