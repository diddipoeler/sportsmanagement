<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$this->rankingChart) {
    return;
}
?>
<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SEASON_RANKS'); ?></h2>
<div class="<?php echo htmlspecialchars($this->divclassrow, ENT_QUOTES, 'UTF-8'); ?> table-responsive" id="rankflashchart">
    <table class="table table-striped">
        <tbody>
        <?php foreach ($this->rankingChart as $row) : ?>
            <?php
            $rank = max(1, (int) ($row['rank'] ?? 1));
            $members = max(1, (int) ($row['members'] ?? 1));
            $score = max(1, $members - $rank + 1);
            $width = max(0, min(100, ($score / $members) * 100));
            ?>
            <tr>
                <th scope="row"><?php echo htmlspecialchars((string) ($row['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></th>
                <td style="width:60%">
                    <div class="progress" role="progressbar" aria-valuenow="<?php echo $rank; ?>" aria-valuemin="1" aria-valuemax="<?php echo $members; ?>">
                        <div class="progress-bar" style="width:<?php echo number_format($width, 2, '.', ''); ?>%"></div>
                    </div>
                </td>
                <td><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_INFO_RANK_OUTPUT', $rank); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
