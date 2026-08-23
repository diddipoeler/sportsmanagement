<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$availableLabel = $this->individualProject
    ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_PERSONS')
    : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_TEAMS');
$assignedLabel = $this->individualProject
    ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_PERSONS')
    : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_TEAMS');
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=projectteams&layout=editlist&tmpl=component&pid=' . (int) $this->project_id); ?>"
    method="post"
    id="adminForm"
    name="adminForm"
>
    <fieldset class="options-form">
        <legend>
            <?php echo $this->individualProject
                ? Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_ASSIGN_TITLE', '<strong>' . $this->escape((string) $this->project->name) . '</strong>')
                : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_TITLE', '<strong>' . $this->escape((string) $this->project->name) . '</strong>'); ?>
        </legend>

        <div class="row align-items-center g-3">
            <div class="col-md-5">
                <label class="form-label" for="availableTeams"><strong><?php echo $availableLabel; ?></strong></label>
                <select class="form-select" id="availableTeams" multiple size="18">
                    <?php foreach ($this->availableTeamOptions as $option) : ?>
                        <option value="<?php echo (int) $option->value; ?>"><?php echo $this->escape((string) $option->text); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-grid gap-2">
                <button class="btn btn-outline-secondary" type="button" data-move="selected-right">&gt;</button>
                <button class="btn btn-outline-secondary" type="button" data-move="all-right">&gt;&gt;</button>
                <button class="btn btn-outline-secondary" type="button" data-move="selected-left">&lt;</button>
                <button class="btn btn-outline-secondary" type="button" data-move="all-left">&lt;&lt;</button>
            </div>

            <div class="col-md-5">
                <label class="form-label" for="assignedTeams"><strong><?php echo $assignedLabel; ?></strong></label>
                <select class="form-select" id="assignedTeams" name="project_teamslist[]" multiple size="18">
                    <?php foreach ($this->assignedTeamOptions as $option) : ?>
                        <option value="<?php echo (int) $option->value; ?>" selected><?php echo $this->escape((string) $option->text); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </fieldset>

    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="task" value="projectteams.assign">
    <?php echo HTMLHelper::_('form.token'); ?>

    <div class="mt-3 d-flex gap-2 justify-content-end">
        <button class="btn btn-primary" type="submit"><?php echo Text::_('JSAVE'); ?></button>
        <button class="btn btn-secondary" type="button" onclick="window.parent.Joomla.Modal.getCurrent().close();">
            <?php echo Text::_('JCANCEL'); ?>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const available = document.getElementById('availableTeams');
    const assigned = document.getElementById('assignedTeams');
    const move = (from, to, all) => {
        Array.from(from.options).filter((option) => all || option.selected).forEach((option) => {
            option.selected = true;
            to.add(option);
        });
    };

    document.querySelectorAll('[data-move]').forEach((button) => {
        button.addEventListener('click', () => {
            switch (button.dataset.move) {
                case 'selected-right': move(available, assigned, false); break;
                case 'all-right': move(available, assigned, true); break;
                case 'selected-left': move(assigned, available, false); break;
                case 'all-left': move(assigned, available, true); break;
            }
        });
    });

    document.getElementById('adminForm').addEventListener('submit', () => {
        Array.from(assigned.options).forEach((option) => { option.selected = true; });
    });
});
</script>
