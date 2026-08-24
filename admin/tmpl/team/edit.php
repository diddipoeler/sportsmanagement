<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$renderFields = static function (array $fields): void {
    foreach ($fields as $field) {
        if (strtolower((string) $field->type) === 'hidden') {
            echo $field->input;
            continue;
        }
        ?>
        <div class="control-group mb-3">
            <div class="control-label"><?php echo $field->label; ?></div>
            <div class="controls"><?php echo $field->input; ?></div>
        </div>
        <?php
    }
};

$renderNestedForm = static function ($form) use ($renderFields): void {
    foreach ($form->getFieldsets() as $fieldset) {
        $fields = $form->getFieldset($fieldset->name);
        if (!$fields) {
            continue;
        }
        ?>
        <fieldset class="options-form mb-3">
            <?php if (!empty($fieldset->label)) : ?>
                <legend><?php echo Text::_((string) $fieldset->label); ?></legend>
            <?php endif; ?>
            <?php $renderFields($fields); ?>
        </fieldset>
        <?php
    }
};

$tabId = static function (string $name): string {
    return 'team-' . (preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'details');
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
    <?php echo HTMLHelper::_('uitab.startTabSet', 'teamTabs', [
        'active' => 'team-details',
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
        <?php
        $name = (string) $fieldset->name;
        if (in_array($name, ['extended', 'extra_fields', 'training'], true)) {
            continue;
        }
        $fields = $this->form->getFieldset($name);
        if (!$fields) {
            continue;
        }
        $label = Text::_((string) ($fieldset->label ?: $name));
        echo HTMLHelper::_('uitab.addTab', 'teamTabs', $tabId($name), $label);
        ?>
        <div class="options-form mb-4">
            <?php $renderFields($fields); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'teamTabs', 'team-training', Text::_('COM_SPORTSMANAGEMENT_TABS_TRAINING')); ?>
    <div class="options-form mb-4">
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
                                            onchange="Joomla.submitbutton('team.apply');"
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
                                    <td><input class="form-control" type="time" name="time_start[<?php echo $trainingId; ?>]" value="<?php echo $formatTime($training->time_start ?? 0); ?>"></td>
                                    <td><input class="form-control" type="time" name="time_end[<?php echo $trainingId; ?>]" value="<?php echo $formatTime($training->time_end ?? 0); ?>"></td>
                                    <td><input class="form-control" type="text" name="place[<?php echo $trainingId; ?>]" value="<?php echo htmlspecialchars((string) ($training->place ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></td>
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
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php if ($this->checkextrafields && !empty($this->lists['ext_fields'])) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'teamTabs', 'team-extra-fields', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS')); ?>
        <div class="options-form mb-4">
            <?php foreach ((array) $this->lists['ext_fields'] as $extraField) : ?>
                <div class="mb-3">
                    <label class="form-label"><?php echo htmlspecialchars((string) ($extraField->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></label>
                    <textarea class="form-control" name="extraf[]" rows="3"><?php echo htmlspecialchars((string) ($extraField->fvalue ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <input type="hidden" name="extra_id[]" value="<?php echo (int) ($extraField->id ?? 0); ?>">
                    <input type="hidden" name="extra_value_id[]" value="<?php echo (int) ($extraField->value_id ?? 0); ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php if (is_object($this->extended) && method_exists($this->extended, 'getFieldsets')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'teamTabs', 'team-extended', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED')); ?>
        <div class="mb-4"><?php $renderNestedForm($this->extended); ?></div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php if (is_object($this->extendeduser) && method_exists($this->extendeduser, 'getFieldsets')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'teamTabs', 'team-extended-user', Text::_('COM_SPORTSMANAGEMENT_EXT_EXTENDED_USER_PREFERENCES')); ?>
        <div class="mb-4"><?php $renderNestedForm($this->extendeduser); ?></div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <!-- Preserve an explicit empty season selection so unchecking all seasons removes all links. -->
    <input type="hidden" name="jform[season_ids][]" value="">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
