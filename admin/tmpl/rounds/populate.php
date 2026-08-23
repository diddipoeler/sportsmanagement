<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$projectName = $this->project ? (string) $this->project->name : '';
?>
<form
    method="post"
    id="adminForm"
    name="adminForm"
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=rounds&layout=populate&pid=' . $this->project_id . '&division_id=' . $this->division_id); ?>"
>
    <div class="card">
        <div class="card-body">
            <p><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_DESC'); ?></p>
            <h3 class="h5">
                <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_LEGEND', '<strong>' . $this->escape($projectName) . '</strong>'); ?>
            </h3>

            <div class="row g-3 mt-1">
                <div class="col-12 col-lg-6">
                    <label class="form-label" for="scheduling"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_LABEL'); ?></label>
                    <select class="form-select" name="scheduling" id="scheduling">
                        <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_SINGLE_ROUND_ROBIN'); ?></option>
                        <option value="1"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_DOUBLE_ROUND_ROBIN'); ?></option>
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label" for="time"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_STARTTIME_LABEL'); ?></label>
                    <input class="form-control" type="time" name="time" id="time" value="20:00">
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label" for="interval"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_ROUNDS_INTERVAL_LABEL'); ?></label>
                    <input class="form-control" type="number" min="1" name="interval" id="interval" value="7">
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label" for="start"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_STARTDATE_LABEL'); ?></label>
                    <?php echo HTMLHelper::calendar(
                        date('Y-m-d'),
                        'start',
                        'start',
                        '%Y-%m-%d',
                        ['class' => 'form-control', 'todayBtn' => true, 'weekNumbers' => false]
                    ); ?>
                </div>

                <div class="col-12">
                    <label class="form-label" for="roundname"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_NEW_ROUND_NAME_LABEL'); ?></label>
                    <input
                        class="form-control"
                        type="text"
                        name="roundname"
                        id="roundname"
                        value="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_NEW_ROUND_NAME')); ?>"
                    >
                </div>

                <div class="col-12">
                    <label class="form-label" for="teamsorder"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TEAMS_ORDER_LABEL'); ?></label>
                    <select class="form-select" name="teamsorder[]" id="teamsorder" multiple size="14">
                        <?php foreach ($this->teams as $team) : ?>
                            <option value="<?php echo (int) $team->value; ?>"><?php echo $this->escape((string) $team->text); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="btn-group mt-2" role="group">
                        <button class="btn btn-secondary" type="button" id="teams-up"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TEAMS_ORDER_UP'); ?></button>
                        <button class="btn btn-secondary" type="button" id="teams-down"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TEAMS_ORDER_DOWN'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('teamsorder');
    const up = document.getElementById('teams-up');
    const down = document.getElementById('teams-down');

    const move = (direction) => {
        const options = Array.from(select.options);
        const selected = options.filter((option) => option.selected);

        if (!selected.length) {
            return;
        }

        const ordered = direction < 0 ? selected : selected.slice().reverse();
        ordered.forEach((option) => {
            const sibling = direction < 0 ? option.previousElementSibling : option.nextElementSibling;
            if (!sibling || sibling.selected) {
                return;
            }
            if (direction < 0) {
                select.insertBefore(option, sibling);
            } else {
                select.insertBefore(sibling, option);
            }
        });
    };

    up.addEventListener('click', () => move(-1));
    down.addEventListener('click', () => move(1));

    // Joomla toolbar buttons submit through Joomla.submitform(), which can bypass
    // the browser's submit event. Select every ordered team immediately before
    // the native populate task is posted so the complete order reaches the model.
    const originalSubmitform = Joomla.submitform;
    if (typeof originalSubmitform === 'function') {
        Joomla.submitform = function (task, form, validate) {
            if (task === 'round.startpopulate') {
                Array.from(select.options).forEach((option) => { option.selected = true; });
            }
            return originalSubmitform.call(this, task, form, validate);
        };
    }
});
</script>
