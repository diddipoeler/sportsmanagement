<?php
/** Project-team assignment modal. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
    <fieldset>
        <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-primary" type="button" onclick="
                document.querySelectorAll('#project_teamslist option, #project_teamslist_name option').forEach((option) => { option.selected = true; });
                Joomla.submitform('projectteams.assign', this.form);
            ">
                <?php echo Text::_('JSAVE'); ?>
            </button>
            <button class="btn btn-secondary" type="reset"><?php echo Text::_('JCLEAR'); ?></button>
            <button class="btn btn-secondary" type="button" onclick="Joomla.submitform('projectteam.cancelmodal', this.form);">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </fieldset>

    <fieldset class="adminform mt-3">
        <legend>
            <?php echo $this->project->project_art_id != 3
                ? Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_TITLE', '<i>' . $this->project->name . '</i>')
                : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTPERSONS_ASSIGN_TITLE', '<i>' . $this->project->name . '</i>'); ?>
        </legend>

        <div class="mb-3"><?php echo $this->lists['countrylist']; ?></div>
        <div class="row align-items-center">
            <div class="col-md-5">
                <strong>
                    <?php echo $this->project->project_art_id != 3
                        ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_TEAMS')
                        : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_AVAIL_PERSONS'); ?>
                </strong>
                <br />
                <?php echo $this->lists['teams']; ?>
            </div>
            <div class="col-md-2 text-center d-grid gap-2">
                <button class="btn btn-secondary" type="button" onclick="move_list_items('teamslist','project_teamslist','project_teamslist_name');">&gt;</button>
                <button class="btn btn-secondary" type="button" onclick="move_list_items_all('teamslist','project_teamslist');">&gt;&gt;</button>
                <button class="btn btn-secondary" type="button" onclick="move_list_items('project_teamslist','teamslist');">&lt;</button>
                <button class="btn btn-secondary" type="button" onclick="move_list_items_all('project_teamslist','teamslist');">&lt;&lt;</button>
            </div>
            <div class="col-md-5">
                <strong>
                    <?php echo $this->project->project_art_id != 3
                        ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_TEAMS')
                        : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ASSIGN_PROJ_PERSONS'); ?>
                </strong>
                <br />
                <?php echo $this->lists['project_teams']; ?>
                <div class="d-none"><?php echo $this->lists['project_teamslist_name']; ?></div>
            </div>
        </div>
    </fieldset>

    <input type="hidden" name="option" value="com_sportsmanagement" />
    <input type="hidden" name="project_id" value="<?php echo (int) $this->project->id; ?>" />
    <input type="hidden" name="pid" value="<?php echo (int) $this->project->id; ?>" />
    <input type="hidden" name="editlist_season_id" value="<?php echo (int) $this->project->season_id; ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
