<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$this->pointsChart) {
    return;
}
$max = max(1, $this->pointsChartMax);
?>
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SEASON_POINTS'); ?></h2>
<div class="<?php echo htmlspecialchars($this->divclassrow, ENT_QUOTES, 'UTF-8'); ?> table-responsive" id="pointsflashchart">
    <table class="table table-striped">
        <tbody>
        <?php foreach ($this->pointsChart as $row) : ?>
            <?php $value = (int) ($row['value'] ?? 0); $width = max(0, min(100, ($value / $max) * 100)); ?>
            <tr>
                <th scope="row"><?php echo htmlspecialchars((string) ($row['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></th>
                <td style="width:60%">
                    <div class="progress" role="progressbar" aria-valuenow="<?php echo $value; ?>" aria-valuemin="0" aria-valuemax="<?php echo $max; ?>">
                        <div class="progress-bar" style="width:<?php echo number_format($width, 2, '.', ''); ?>%"></div>
                    </div>
                </td>
                <td><?php echo $value; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
