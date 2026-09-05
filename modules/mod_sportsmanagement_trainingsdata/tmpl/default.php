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

$showNotes = (int) $params->get('show_training_note', 1) === 1;
$daysOfWeek = [
    1 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'),
    2 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TUESDAY'),
    3 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WEDNESDAY'),
    4 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_THURSDAY'),
    5 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FRIDAY'),
    6 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SATURDAY'),
    7 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SUNDAY'),
];
$formatTime = static function (mixed $seconds): string {
    $value = max(0, (int) $seconds);

    return sprintf('%02d:%02d', intdiv($value, 3600), intdiv($value % 3600, 60));
};
$moduleClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
$tableClass = htmlspecialchars((string) $params->get('table_class', 'table'), ENT_QUOTES, 'UTF-8');
?>
<div class="<?= $moduleClass ?>" id="<?= htmlspecialchars((string) $module->module, ENT_QUOTES, 'UTF-8') ?>-<?= (int) $module->id ?>">
    <?php if ((int) $params->get('show_training_modul_header', 1) === 1) : ?>
        <h4 class="scheduler-border"><?= Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING') ?></h4>
    <?php endif; ?>

    <?php if (!empty($trainingsdata)) : ?>
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
                    <?php foreach ($trainingsdata as $training) : ?>
                        <?php
                        $notes = trim((string) ($training->notes ?? ''));
                        $currentNote = null;
                        if ($showNotes && $notes !== '') {
                            $currentNote = ++$noteNumber;
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($daysOfWeek[(int) ($training->dayofweek ?? 0)] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $formatTime($training->time_start ?? 0) ?></td>
                            <td><?= $formatTime($training->time_end ?? 0) ?></td>
                            <td><?= htmlspecialchars((string) ($training->place ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <?php if ($showNotes) : ?>
                                <td><?= $currentNote !== null ? '*<sup>' . $currentNote . '</sup>' : '' ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($showNotes) : ?>
                        <?php $noteNumber = 0; ?>
                        <?php foreach ($trainingsdata as $training) : ?>
                            <?php $notes = trim((string) ($training->notes ?? '')); ?>
                            <?php if ($notes === '') { continue; } ?>
                            <?php $noteNumber++; ?>
                            <tr class="training-note">
                                <td class="text-end">*<sup><?= $noteNumber ?></sup></td>
                                <td colspan="4"><?= nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')) ?></td>
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
