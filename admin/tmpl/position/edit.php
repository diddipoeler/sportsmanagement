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

$renderOptions = static function (array $items): void {
    foreach ($items as $item) {
        ?>
        <option value="<?php echo (int) ($item->value ?? 0); ?>"><?php
            echo htmlspecialchars((string) ($item->text ?? ''), ENT_QUOTES, 'UTF-8');
        ?></option>
        <?php
    }
};

$renderAssignment = static function (
    string $availableId,
    string $availableName,
    array $available,
    string $assignedId,
    string $assignedName,
    array $assigned,
    string $availableLabel,
    string $assignedLabel,
    string $hint
) use ($renderOptions): void {
    $availableSize = max(10, count($available));
    $assignedSize = max(10, count($assigned));
    ?>
    <div class="row g-3 align-items-center">
        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="<?php echo $availableId; ?>">
                <?php echo Text::_($availableLabel); ?>
            </label>
            <select
                name="<?php echo $availableName; ?>[]"
                id="<?php echo $availableId; ?>"
                class="form-select"
                multiple
                size="<?php echo $availableSize; ?>"
                style="min-height:18rem"
            ><?php $renderOptions($available); ?></select>
        </div>

        <div class="col-12 col-lg-1 d-flex flex-lg-column justify-content-center gap-2">
            <button
                type="button"
                class="btn btn-outline-secondary"
                data-jsm-move
                data-source="<?php echo $availableId; ?>"
                data-destination="<?php echo $assignedId; ?>"
                aria-label="<?php echo htmlspecialchars(Text::_('JTOOLBAR_ASSIGN'), ENT_QUOTES, 'UTF-8'); ?>"
            >&gt;&gt;</button>
            <button
                type="button"
                class="btn btn-outline-secondary"
                data-jsm-move
                data-source="<?php echo $assignedId; ?>"
                data-destination="<?php echo $availableId; ?>"
                aria-label="<?php echo htmlspecialchars(Text::_('JTOOLBAR_UNPUBLISH'), ENT_QUOTES, 'UTF-8'); ?>"
            >&lt;&lt;</button>
        </div>

        <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold" for="<?php echo $assignedId; ?>">
                <?php echo Text::_($assignedLabel); ?>
            </label>
            <select
                name="<?php echo $assignedName; ?>[]"
                id="<?php echo $assignedId; ?>"
                class="form-select"
                multiple
                size="<?php echo $assignedSize; ?>"
                style="min-height:18rem"
            ><?php $renderOptions($assigned); ?></select>
        </div>

        <div class="col-12 col-lg-1 d-flex flex-lg-column justify-content-center gap-2">
            <button
                type="button"
                class="btn btn-outline-secondary"
                data-jsm-order="up"
                data-target="<?php echo $assignedId; ?>"
            ><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?></button>
            <button
                type="button"
                class="btn btn-outline-secondary"
                data-jsm-order="down"
                data-target="<?php echo $assignedId; ?>"
            ><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?></button>
        </div>

        <div class="col-12 col-lg-2">
            <div class="alert alert-info mb-0"><?php echo Text::_($hint); ?></div>
        </div>
    </div>
    <?php
};

$positionId = (int) ($this->item->id ?? 0);
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=position&layout=edit&id=' . $positionId); ?>"
    method="post"
    name="adminForm"
    id="position-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'positionTabs', [
        'active' => 'position-details',
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'positionTabs', 'position-details', Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('details')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'positionTabs', 'position-picture', Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('picture')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'positionTabs', 'position-events', Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EVENTS_LEGEND')); ?>
    <div class="options-form mb-4">
        <?php
        $renderAssignment(
            'eventslist',
            'eventslist',
            $this->availableEvents,
            'position_eventslist',
            'position_eventslist',
            $this->assignedEvents,
            'COM_SPORTSMANAGEMENT_ADMIN_POSITION_EXISTING_EVENTS',
            'COM_SPORTSMANAGEMENT_ADMIN_POSITION_ASSIGNED_EVENTS_TO_POS',
            'COM_SPORTSMANAGEMENT_ADMIN_POSITION_EVENTS_HINT'
        );
        ?>
        <input type="hidden" name="sync_position_events" value="1">
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'positionTabs', 'position-statistics', Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_STATISTICS_LEGEND')); ?>
    <div class="options-form mb-4">
        <?php
        $renderAssignment(
            'statistic',
            'statistic',
            $this->availableStatistics,
            'position_statistic',
            'position_statistic',
            $this->assignedStatistics,
            'COM_SPORTSMANAGEMENT_ADMIN_POSITION_EXISTING_STATISTICS',
            'COM_SPORTSMANAGEMENT_ADMIN_POSITION_ASSIGNED_STATS_TO_POS',
            'COM_SPORTSMANAGEMENT_ADMIN_POSITION_STATS_HINT'
        );
        ?>
        <input type="hidden" name="sync_position_statistics" value="1">
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
