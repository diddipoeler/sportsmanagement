<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$renderForm = static function ($form, array $skip = []): void {
    foreach ($form->getFieldsets() as $fieldset) {
        if (in_array((string) $fieldset->name, $skip, true)) {
            continue;
        }

        $fields = $form->getFieldset($fieldset->name);
        if (!$fields) {
            continue;
        }

        $legend = $fieldset->label ? Text::_((string) $fieldset->label) : ucfirst((string) $fieldset->name);
        ?>
        <fieldset class="options-form mb-4">
            <legend><?php echo $legend; ?></legend>
            <?php foreach ($fields as $field) : ?>
                <?php if (strtolower((string) $field->type) === 'hidden') : ?>
                    <?php echo $field->input; ?>
                    <?php continue; ?>
                <?php endif; ?>
                <div class="control-group mb-3">
                    <div class="control-label"><?php echo $field->label; ?></div>
                    <div class="controls"><?php echo $field->input; ?></div>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }
};

$formatTime = static function ($seconds): string {
    $seconds = max(0, (int) $seconds);
    return sprintf('%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
};

$teamId = (int) ($this->item->id ?? 0);
$clubId = (int) ($this->item->club_id ?? 0);
$tmpl = $this->tmpl !== '' ? '&tmpl=' . rawurlencode($this->tmpl) : '';
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=team&layout=edit&id=' . $teamId . '&club_id=' . $clubId . $tmpl); ?>"
    method="post"
    name="adminForm"
    id="team-form"
    class="form-validate"
>
    <div class="row g-4">
        <div class="col-12 col-xxl-8">
            <?php $renderForm($this->form, ['extended', 'extra_fields', 'training']); ?>

            <fieldset class="options-form mb-4">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_TRAINING'); ?></legend>

                <?php if ($teamId === 0) : ?>
                    <div class="alert alert-info">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_NO_TRAINING'); ?>
                    </div>
                <?php else : ?>
                    <div class="mb-3">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="add_trainingData"
                                id="add-training-data"
                                value="1"
                                onchange="Joomla.submitbutton('team.apply');"
                            >
                            <label class="form-check-label" for="add-training-data">
                                <?php echo Text::_('JACTION_CREATE'); ?>
                            </label>
                        </div>
                    </div>

                    <?php if ($this->trainingData) : ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col"><?php echo Text::_('JACTION_DELETE'); ?></th>
                                        <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_DAY'); ?></th>
                                        <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_STARTTIME'); ?></th>
                                        <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_ENDTIME'); ?></th>
                                        <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_PLACE'); ?></th>
                                        <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_NOTES'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->trainingData as $training) : ?>
                                        <?php $trainingId = (int) $training->id; ?>
                                        <tr>
                                            <td>
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="delete[]"
                                                    value="<?php echo $trainingId; ?>"
                                                    aria-label="<?php echo Text::_('JACTION_DELETE'); ?>"
                                                >
                                            </td>
                                            <td>
                                                <select class="form-select" name="dayofweek[<?php echo $trainingId; ?>]">
                                                    <?php foreach ($this->daysOfWeek as $day => $label) : ?>
                                                        <option value="<?php echo (int) $day; ?>"<?php echo (int) $training->dayofweek === (int) $day ? ' selected' : ''; ?>>
                                                            <?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input class="form-control" type="time" name="time_start[<?php echo $trainingId; ?>]" value="<?php echo $formatTime($training->time_start ?? 0); ?>">
                                            </td>
                                            <td>
                                                <input class="form-control" type="time" name="time_end[<?php echo $trainingId; ?>]" value="<?php echo $formatTime($training->time_end ?? 0); ?>">
                                            </td>
                                            <td>
                                                <input class="form-control" type="text" name="place[<?php echo $trainingId; ?>]" value="<?php echo htmlspecialchars((string) ($training->place ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            </td>
                                            <td>
                                                <textarea class="form-control" name="notes[<?php echo $trainingId; ?>]" rows="2"><?php echo htmlspecialchars((string) ($training->notes ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                <input type="hidden" name="tdids[]" value="<?php echo $trainingId; ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_NO_TRAINING'); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </fieldset>
        </div>

        <div class="col-12 col-xxl-4">
            <?php if ($this->checkextrafields && !empty($this->lists['ext_fields'])) : ?>
                <fieldset class="options-form mb-4">
                    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'); ?></legend>
                    <?php foreach ((array) $this->lists['ext_fields'] as $extraField) : ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo htmlspecialchars((string) ($extraField->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></label>
                            <textarea class="form-control" name="extraf[]" rows="3"><?php echo htmlspecialchars((string) ($extraField->fvalue ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <input type="hidden" name="extra_id[]" value="<?php echo (int) ($extraField->id ?? 0); ?>">
                            <input type="hidden" name="extra_value_id[]" value="<?php echo (int) ($extraField->value_id ?? 0); ?>">
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>

            <?php if (is_object($this->extended) && method_exists($this->extended, 'getFieldsets')) : ?>
                <div class="mb-4"><?php $renderForm($this->extended); ?></div>
            <?php endif; ?>

            <?php if (is_object($this->extendeduser) && method_exists($this->extendeduser, 'getFieldsets')) : ?>
                <div class="mb-4"><?php $renderForm($this->extendeduser); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Preserve an explicit empty season selection so unchecking all seasons removes all links. -->
    <input type="hidden" name="jform[season_ids][]" value="">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
