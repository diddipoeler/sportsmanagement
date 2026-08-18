<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (empty($this->config['show_prediction_heading']) || !$this->predictionGame) {
    return;
}

$pmVar = !empty($this->predictionMember->pmID) ? $this->predictionMember->pmID : 0;
$memberSelectTask = $this->getName() === 'predictionentry' ? 'predictionentry.select' : 'predictionusers.select';
?>
<table class="table">
    <tr>
        <td>
            <?php
            echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_HEAD_ACTUAL_PRED_GAME', '<b><i>' . htmlspecialchars((string) $this->predictionGame->name, ENT_QUOTES, 'UTF-8') . '</i></b>');
            if (!empty($this->showediticon) && $pmVar) {
                echo '&nbsp;&nbsp;';
                $link = JSMPredictionHelperRoute::getPredictionMemberRoute($this->predictionGameID, $pmVar, 'edit', $this->projectID, $this->predictionGroupID, $this->roundID, $this->databaseSelector);
                $imgTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_HEAD_EDIT_IMAGE_TITLE');
                $desc = HTMLHelper::image('media/com_sportsmanagement/jl_images/edit.png', $imgTitle, ['border' => 0, 'title' => $imgTitle]);
                echo HTMLHelper::link($link, $desc);
            }
            ?>
        </td>
        <?php if ($this->getName() === 'predictionusers' || ($this->allowedAdmin && $this->getName() === 'predictionentry')) : ?>
            <td style="text-align:right;">
                <form name="predictionMemberSelect" method="post">
                    <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>" />
                    <input type="hidden" name="pj" value="<?php echo (int) $this->projectID; ?>" />
                    <input type="hidden" name="r" value="<?php echo (int) $this->roundID; ?>" />
                    <input type="hidden" name="pggroup" value="<?php echo (int) $this->predictionGroupID; ?>" />
                    <input type="hidden" name="uid" value="<?php echo (int) $this->predictionMemberID; ?>" />
                    <input type="hidden" name="task" value="<?php echo $memberSelectTask; ?>" />
                    <input type="hidden" name="option" value="com_sportsmanagement" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                    <?php echo $this->lists['predictionMembers'] ?? ''; ?>
                </form>
            </td>
        <?php endif; ?>
        <td style="text-align:right;">
            <ul class="list-inline">
                <?php if (!empty($this->config['show_prediction_button'])) : ?>
                    <?php
                    $links = [
                        ['COM_SPORTSMANAGEMENT_PRED_HEAD_ENTRY_IMAGE_TITLE', 'media/com_sportsmanagement/jl_images/prediction_entry.png', JSMPredictionHelperRoute::getPredictionTippEntryRoute($this->predictionGameID, $this->predictionMemberID, $this->roundID, $this->projectID, '', 0, $this->databaseSelector)],
                        ['COM_SPORTSMANAGEMENT_PRED_HEAD_MEMBER_IMAGE_TITLE', 'media/com_sportsmanagement/jl_images/prediction_member.png', JSMPredictionHelperRoute::getPredictionMemberRoute($this->predictionGameID, $pmVar, '', $this->projectID, $this->predictionGroupID, $this->roundID, $this->databaseSelector)],
                        ['COM_SPORTSMANAGEMENT_PRED_HEAD_RESULTS_IMAGE_TITLE', 'media/com_sportsmanagement/jl_images/prediction_results.png', JSMPredictionHelperRoute::getPredictionResultsRoute($this->predictionGameID, $this->roundID, $this->projectID, $pmVar, '', $this->predictionGroupID, $this->databaseSelector)],
                        ['COM_SPORTSMANAGEMENT_PRED_HEAD_RANKING_IMAGE_TITLE', 'media/com_sportsmanagement/jl_images/prediction_ranking.png', JSMPredictionHelperRoute::getPredictionRankingRoute($this->predictionGameID, $this->projectID, $this->roundID, '', $this->predictionGroupID, 0, 0, 0, 0, $this->databaseSelector)],
                    ];
                    if (!empty($this->config['show_pred_group_link'])) {
                        $links[] = ['COM_SPORTSMANAGEMENT_PRED_HEAD_RANKING_GROUP_IMAGE_TITLE', 'media/com_sportsmanagement/jl_images/teaminfo_icon.png', JSMPredictionHelperRoute::getPredictionRankingRoute($this->predictionGameID, $this->projectID, $this->roundID, '', $this->predictionGroupID, 1, 0, 0, 0, $this->databaseSelector)];
                    }
                    $links[] = ['COM_SPORTSMANAGEMENT_PRED_HEAD_RULES_IMAGE_TITLE', 'media/com_sportsmanagement/jl_images/prediction_rules.png', JSMPredictionHelperRoute::getPredictionRulesRoute($this->predictionGameID, $this->databaseSelector)];
                    foreach ($links as [$titleKey, $image, $url]) :
                        $imgTitle = Text::_($titleKey);
                        $img = HTMLHelper::image(Uri::root() . $image, $imgTitle, ['border' => 0, 'title' => $imgTitle]);
                        ?>
                        <li class="list-inline-item"><?php echo HTMLHelper::link($url, $img, ['title' => $imgTitle]); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </td>
    </tr>
</table>
