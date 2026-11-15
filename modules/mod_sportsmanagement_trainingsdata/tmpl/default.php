<?php
/**
 * Joomla 5/6 layout for the SportsManagement trainings data module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) 2015 diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Escape a legacy TrainingsData field for text and HTML attributes.
 *
 * Stored fields and module settings can contain arrays or malformed UTF-8.
 * Reject non-scalar values to avoid PHP 8 conversion warnings.
 *
 * @param mixed $value Raw training or Joomla module field.
 * @return string Safe HTML text or empty output for unsupported values.
 */
$escape = static fn (mixed $value): string => htmlspecialchars(
    is_string($value) || is_numeric($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
$showNotes = in_array($params->get('show_training_note', 1), [1, '1', true], true);
$showHeader = in_array($params->get('show_training_modul_header', 1), [1, '1', true], true);
$trainingRows = array_values(array_filter(
    is_array($trainingsdata ?? null) ? $trainingsdata : [],
    'is_object'
));
$daysOfWeek = [
    1 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'),
    2 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TUESDAY'),
    3 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WEDNESDAY'),
    4 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_THURSDAY'),
    5 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FRIDAY'),
    6 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SATURDAY'),
    7 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SUNDAY'),
];
/**
 * Format bounded seconds since midnight as hours and minutes.
 *
 * Rejects arrays, negative values, partial numeric strings and overflows,
 * instead of relying on PHP's permissive legacy integer casts.
 *
 * @param mixed $seconds Seconds since midnight from the training record.
 * @return string HH:MM string, or 00:00 for invalid values.
 */
$formatTime = static function (mixed $seconds): string {
    if (!is_int($seconds) && (!is_string($seconds)
        || preg_match('/^[0-9]{1,5}$/D', $seconds) !== 1)) {
        return '00:00';
    }

    $value = filter_var($seconds, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 86399],
    ]);

    if ($value === false) {
        return '00:00';
    }

    return sprintf('%02d:%02d', intdiv($value, 3600), intdiv($value % 3600, 60));
};
$moduleClass = $escape($params->get('moduleclass_sfx', ''));
$tableClass = $escape($params->get('table_class', 'table'));
?>
<div class="<?= $moduleClass ?>" id="<?= $escape($module->module ?? 'mod_sportsmanagement_trainingsdata') ?>-<?= (int) $module->id ?>">
    <?php if ($showHeader) : ?>
        <h4 class="scheduler-border"><?= Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING') ?></h4>
    <?php endif; ?>

    <?php if ($trainingRows) : ?>
        <div class="table-responsive">
            <table class="<?= $tableClass ?>">
                <thead>
                    <tr class="sectiontableheader">
                        <th scope="col" class="text-nowrap"><?= Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_DAY') ?></th>
                        <th scope="col" class="text-nowrap"><?= Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_START') ?></th>
                        <th scope="col" class="text-nowrap"><?= Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_END') ?></th>
                        <th scope="col" class="text-nowrap"><?= Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_LOCATION') ?></th>
                        <?php if ($showNotes) : ?>
                            <th scope="col" class="text-nowrap"><?= Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NOTE') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $noteNumber = 0; ?>
                    <?php foreach ($trainingRows as $training) : ?>
                        <?php
                        $notes = (is_string($training->notes ?? null) ? trim($training->notes) : '');
                        $currentNote = null;
                        if ($showNotes && $notes !== '') {
                            $currentNote = ++$noteNumber;
                        }
                        ?>
                        <tr>
                            <td><?= $escape($daysOfWeek[filter_var($training->dayofweek ?? null, FILTER_VALIDATE_INT) ?: 0] ?? '') ?></td>
                            <td><?= $formatTime($training->time_start ?? 0) ?></td>
                            <td><?= $formatTime($training->time_end ?? 0) ?></td>
                            <td><?= $escape($training->place ?? '') ?></td>
                            <?php if ($showNotes) : ?>
                                <td><?= $currentNote !== null ? '*<sup>' . $currentNote . '</sup>' : '' ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($showNotes) : ?>
                        <?php $noteNumber = 0; ?>
                        <?php foreach ($trainingRows as $training) : ?>
                            <?php $notes = (is_string($training->notes ?? null) ? trim($training->notes) : ''); ?>
                            <?php if ($notes === '') { continue; } ?>
                            <?php $noteNumber++; ?>
                            <tr class="training-note">
                                <td class="text-end">*<sup><?= $noteNumber ?></sup></td>
                                <td colspan="4"><?= nl2br($escape($notes)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="alert alert-warning" role="alert">
            <h4><?= Text::_('COM_SPORTSMANAGEMENT_ERROR') ?></h4>
            <?= Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NODATA') ?>
        </div>
    <?php endif; ?>
</div>
