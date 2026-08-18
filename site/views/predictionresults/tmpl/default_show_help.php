<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$project = $this->resultsProject;
if (!$project) {
    return;
}

$showScoring = !isset($this->config['show_scoring']) || !empty($this->config['show_scoring']);
$gameMode = (int) ($project->mode ?? 0) === 0
    ? Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_STANDARD_MODE')
    : Text::_('JL_PRED_RESULTS_TOTO_MODE');
$examples = [
    [2, 1, 1, 2, 1],
    [2, 1, 1, 3, 2],
    [1, 1, 0, 2, 2],
    [1, 2, 1, 1, 3],
    [2, 1, 2, 0, 1],
];
?>
<p style="font-weight:bold;"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_NOTICE'); ?></p>
<ul>
    <li><i><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_01'); ?></i></li>
    <?php if (empty($this->config['show_all_user'])) : ?>
        <li><i><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_02'); ?></i></li>
    <?php endif; ?>
    <li><i><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_03'); ?></i></li>
    <li><i><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_04'); ?></i></li>
    <li><i><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_05'); ?></i></li>
    <li><i><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_06', '<b>' . $gameMode . '</b>'); ?></i></li>

    <?php if ($showScoring && $this->currentPredictionMemberID > 0) : ?>
        <li>
            <i><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_07'); ?></i>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_RESULT'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_YOUR_PREDICTION'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_POINTS'); ?></th>
                        <?php if (!empty($project->joker) && (int) $project->mode === 0) : ?>
                            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_JOKER_POINTS'); ?></th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($examples as [$home, $away, $tipp, $tipHome, $tipAway]) : ?>
                        <tr>
                            <td><?php echo $home . ':' . $away; ?></td>
                            <td><?php echo $tipHome . ':' . $tipAway; ?></td>
                            <td><?php echo $this->scoreExample($home, $away, $tipp, $tipHome, $tipAway, false); ?></td>
                            <?php if (!empty($project->joker) && (int) $project->mode === 0) : ?>
                                <td><?php echo $this->scoreExample($home, $away, $tipp, $tipHome, $tipAway, true); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </li>
    <?php else : ?>
        <li><i><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_READ_RULES'); ?></i></li>
    <?php endif; ?>
</ul>
