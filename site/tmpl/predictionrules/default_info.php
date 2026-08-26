<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_01'); ?></h3>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_01'); ?></p>
<p><?php
if ((int) $this->actJoomlaUser->id < 62) {
    echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_02', '<a href="index.php?option=com_users&view=registration"><b><i>', '</i></b></a>');
} elseif (empty($this->predictionMember->pmID)) {
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_03');
}
?></p>

<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_02'); ?></h3>
<p><?php
if (!empty($this->predictionGame->auto_approve)) {
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_01');
} else {
    echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_02');
}
echo '<br />' . Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_03');
?></p>

<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_03'); ?></h3>
<p><?php
echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_01') . '<br />';
echo empty($this->predictionGame->admin_tipp)
    ? Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_02')
    : Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_03');
?></p>

<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_04'); ?></h3>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_04_01'); ?></p>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_04_02'); ?></p>

<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05'); ?></h3>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_05_01'); ?></p>
<?php if (!empty($this->config['show_points'])) : ?>
    <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_05_02'); ?></p>
    <?php foreach ($this->predictionProjects as $predictionProject) : ?>
        <table class="blog" cellpadding="0" cellspacing="0" border="1">
            <tr>
                <td class="sectiontableheader" style="text-align:center;">
                    <?php
                    echo htmlspecialchars((string) $predictionProject->projectName, ENT_QUOTES, 'UTF-8') . ' - ';
                    echo ((int) $predictionProject->mode === 0)
                        ? Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_STANDARD_MODE')
                        : Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_TOTO_MODE');
                    ?>
                </td>
            </tr>
        </table>
        <table class="blog" cellpadding="0" cellspacing="0">
            <tr>
                <td class="sectiontableheader" style="text-align:center;"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_RESULT'); ?></td>
                <td class="sectiontableheader" style="text-align:center;"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_YOUR_PREDICTION'); ?></td>
                <td class="sectiontableheader" style="text-align:center;"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_POINTS'); ?></td>
                <?php if (!empty($predictionProject->joker) && (int) $predictionProject->mode === 0) : ?>
                    <td class="sectiontableheader" style="text-align:center;"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_JOKER_POINTS'); ?></td>
                <?php endif; ?>
            </tr>
            <?php
            $examples = [
                [2, 1, 1, 2, 1],
                [2, 1, 1, 3, 2],
                [1, 1, 0, 2, 2],
                [1, 2, 1, 1, 3],
                [2, 1, 2, 0, 1],
            ];
            foreach ($examples as $index => [$home, $away, $tip, $tipHome, $tipAway]) :
                ?>
                <tr class="sectiontableentry<?php echo ($index % 2) + 1; ?>">
                    <td class="info"><?php echo $home . ':' . $away; ?></td>
                    <td class="info"><?php echo $tipHome . ':' . $tipAway; ?></td>
                    <td class="info"><?php echo $this->scoreRuleExample($predictionProject, $home, $away, $tip, $tipHome, $tipAway); ?></td>
                    <?php if (!empty($predictionProject->joker) && (int) $predictionProject->mode === 0) : ?>
                        <td class="info"><?php echo $this->scoreRuleExample($predictionProject, $home, $away, $tip, $tipHome, $tipAway, true); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php endif; ?>

<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06'); ?></h3>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_01'); ?></p>
<ul>
    <?php foreach ($this->predictionProjects as $predictionProject) : ?>
        <?php if (!empty($predictionProject->champ)) : ?>
            <li><?php
            $key = !empty($predictionProject->overview)
                ? 'COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06_HALF_SEASON'
                : 'COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06_FULL_SEASON';
            echo Text::sprintf(
                $key,
                '<b>' . (int) $predictionProject->points_tipp_champ . '</b>',
                '<b><i>' . htmlspecialchars((string) $predictionProject->projectName, ENT_QUOTES, 'UTF-8') . '</i></b>'
            );
            ?></li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_02'); ?></p>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_03'); ?></p>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_07'); ?></h3>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_07_01'); ?></p>
<h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_08'); ?></h3>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_08_01'); ?></p>
<p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_08_02'); ?></p>
<br />
