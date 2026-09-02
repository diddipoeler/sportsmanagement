<?php
/**
 * Joomla 5/6 Teaminfo training layout.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$trainingData = is_array($this->trainingData ?? null) ? $this->trainingData : [];

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING')];
echo $this->loadTemplate('jsm_notes');

if (!$trainingData) {
    $this->tips[] = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NODATA');
}

echo $this->loadTemplate('jsm_tips');
?>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_DAY'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_START'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_END'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_LOCATION'); ?></th>
                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NOTE'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $noteIndex = 0; ?>
            <?php foreach ($trainingData as $training) : ?>
                <?php
                $startSeconds = max(0, (int) ($training->time_start ?? 0));
                $endSeconds = max(0, (int) ($training->time_end ?? 0));
                $startTime = sprintf('%02d:%02d', intdiv($startSeconds, 3600), intdiv($startSeconds % 3600, 60));
                $endTime = sprintf('%02d:%02d', intdiv($endSeconds, 3600), intdiv($endSeconds % 3600, 60));
                $notes = trim((string) ($training->notes ?? ''));
                $day = (int) ($training->dayofweek ?? 0);
                ?>
                <tr>
                    <td><?php echo $escape($this->daysOfWeek[$day] ?? ''); ?></td>
                    <td><?php echo $escape($startTime); ?></td>
                    <td><?php echo $escape($endTime); ?></td>
                    <td><?php echo $escape($training->place ?? ''); ?></td>
                    <td>
                        <?php if ($notes !== '') : ?>
                            <?php ++$noteIndex; ?>
                            *<sup><?php echo $noteIndex; ?></sup>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php $noteIndex = 0; ?>
            <?php foreach ($trainingData as $training) : ?>
                <?php $notes = trim((string) ($training->notes ?? '')); ?>
                <?php if ($notes === '') : ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php ++$noteIndex; ?>
                <tr>
                    <td class="text-end">*<sup><?php echo $noteIndex; ?></sup></td>
                    <td colspan="4"><?php echo $escape($notes); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
