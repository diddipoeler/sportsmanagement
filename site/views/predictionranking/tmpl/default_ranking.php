<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');

$project = $this->rankingProject;
if (!$project) {
    echo '<div class="alert alert-warning">' . Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING') . '</div>';
    return;
}

$groupOptions = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_GROUPS'))];
$groupOptions = array_merge($groupOptions, $this->groupOptions);
$roundOptions = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_ROUNDS'))];
$roundOptions = array_merge($roundOptions, $this->roundOptions);
$currentMemberId = (int) ($this->predictionMember->id ?? $this->predictionMember->pmID ?? 0);
$logoForTeam = static function (?object $team, string $size): string {
    if (!$team) {
        return '';
    }
    $value = (string) ($team->{$size} ?? '');
    return $value !== '' ? $value : (string) ($team->team_picture ?? '');
};
?>

<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" method="post" class="mb-3">
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="task" value="predictionranking.selectprojectround">
    <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>">
    <input type="hidden" name="r" value="<?php echo (int) $this->roundID; ?>">
    <input type="hidden" name="cfg_which_database" value="<?php echo (int) $this->databaseSelector; ?>">

    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label" for="pred-project"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_PROJECT'); ?></label>
            <?php echo HTMLHelper::_('select.genericlist', $this->projectOptions, 'pj', 'id="pred-project" class="form-select"', 'value', 'text', $this->projectID); ?>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="pred-rank-mode"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_SUBTITLE_01'); ?></label>
            <?php echo HTMLHelper::_('select.genericlist', $this->lists['ranking_array'], 'pggrouprank', 'id="pred-rank-mode" class="form-select"', 'value', 'text', $this->groupRanking); ?>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="pred-group"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER_GROUP'); ?></label>
            <?php echo HTMLHelper::_('select.genericlist', $groupOptions, 'pggroup', 'id="pred-group" class="form-select"', 'value', 'text', $this->predictionGroupID); ?>
        </div>
        <?php if (!empty($this->config['show_rankingnav'])) : ?>
            <div class="col-md-2">
                <label class="form-label" for="pred-from"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'); ?></label>
                <?php echo HTMLHelper::_('select.genericlist', $roundOptions, 'from', 'id="pred-from" class="form-select"', 'value', 'text', $this->fromRoundID); ?>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="pred-to"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_TO_MATCHDAY'); ?></label>
                <?php echo HTMLHelper::_('select.genericlist', $roundOptions, 'to', 'id="pred-to" class="form-select"', 'value', 'text', $this->toRoundID); ?>
            </div>
        <?php else : ?>
            <input type="hidden" name="from" value="<?php echo (int) $this->fromRoundID; ?>">
            <input type="hidden" name="to" value="<?php echo (int) $this->toRoundID; ?>">
        <?php endif; ?>
        <input type="hidden" name="type" value="<?php echo (int) $this->rankingType; ?>">
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_FILTER'); ?></button>
        </div>
    </div>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<div class="table-responsive">
<table class="<?php echo htmlspecialchars((string) $this->config['table_class'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars((string) $this->config['table_class_responsive'], ENT_QUOTES, 'UTF-8'); ?>">
    <thead>
    <tr>
        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK'); ?></th>
        <?php if ($this->groupRanking) : ?>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER_GROUP'); ?></th>
            <?php if (!empty($this->config['show_tip_details'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_DETAILS'); ?></th><?php endif; ?>
        <?php else : ?>
            <?php if (!empty($this->config['show_user_icon'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_AVATAR'); ?></th><?php endif; ?>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER'); ?></th>
            <?php if (!empty($this->config['show_pred_group'])) : ?><th><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_MEMBER_GROUP'); ?></th><?php endif; ?>
            <?php if (!empty($this->config['show_champion_tip'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_CHAMPION_TIP'); ?></th><?php endif; ?>
            <?php if (!empty($this->config['show_final4_tip'])) : ?><th colspan="4" class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_FINAL4_TIP'); ?></th><?php endif; ?>
            <?php if (!empty($this->config['show_tip_details'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_DETAILS'); ?></th><?php endif; ?>
        <?php endif; ?>
        <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_POINTS'); ?></th>
        <?php if (!empty($this->config['show_average_points'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_AVERAGE'); ?></th><?php endif; ?>
        <?php if (!empty($this->config['show_count_tips'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_PREDICTIONS'); ?></th><?php endif; ?>
        <?php if (!empty($this->config['show_count_joker'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_JOKERS'); ?></th><?php endif; ?>
        <?php if (!empty($this->config['show_count_topptips'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_TOPS'); ?></th><?php endif; ?>
        <?php if (!empty($this->config['show_count_difftips'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_MARGINS'); ?></th><?php endif; ?>
        <?php if (!empty($this->config['show_count_tendtipps'])) : ?><th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_TENDENCIES'); ?></th><?php endif; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->rankingRows as $key => $row) : ?>
        <?php
        $member = $row['member'] ?? null;
        $isCurrent = !$this->groupRanking && (int) $key === $currentMemberId;
        $style = $isCurrent ? 'background-color:' . htmlspecialchars((string) $this->config['background_color_ranking'], ENT_QUOTES, 'UTF-8') . ';color:black;' : '';
        $detailsUrl = JSMPredictionHelperRoute::getPredictionResultsRoute(
            $this->predictionGameID,
            $this->roundID,
            $this->projectID,
            $this->groupRanking ? 0 : (int) $key,
            '',
            $this->groupRanking ? (int) $row['pg_group_id'] : 0,
            $this->databaseSelector
        );
        ?>
        <tr<?php echo $style ? ' style="' . $style . '"' : ''; ?>>
            <td class="text-center"><?php echo htmlspecialchars((string) ($row['rank'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            <?php if ($this->groupRanking) : ?>
                <td><?php echo htmlspecialchars((string) ($row['pg_group_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                <?php if (!empty($this->config['show_tip_details'])) : ?>
                    <td class="text-center"><a href="<?php echo htmlspecialchars($detailsUrl, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_DETAILS'); ?>">&#128269;</a></td>
                <?php endif; ?>
            <?php else : ?>
                <?php if (!empty($this->config['show_user_icon'])) : ?>
                    <?php
                    $avatar = (string) ($member->avatar ?? '');
                    $avatarPath = JPATH_ROOT . '/' . ltrim($avatar, '/');
                    if ($avatar === '' || !is_file($avatarPath)) {
                        $avatar = 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
                    }
                    ?>
                    <td class="text-center"><?php echo HTMLHelper::image($avatar, (string) ($member->name ?? ''), ['width' => (int) $this->config['show_user_icon_width']]); ?></td>
                <?php endif; ?>
                <td>
                    <?php
                    $displayName = (string) (($member->aliasName ?? '') ?: ($member->name ?? ''));
                    if (!empty($this->config['link_name_to']) && (!empty($member->show_profile) || $isCurrent)) {
                        $memberUrl = JSMPredictionHelperRoute::getPredictionMemberRoute($this->predictionGameID, (int) $key, 0, $this->projectID, 0, $this->roundID, $this->databaseSelector);
                        echo '<a href="' . htmlspecialchars($memberUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '</a>';
                    } else {
                        echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
                    }
                    ?>
                </td>
                <?php if (!empty($this->config['show_pred_group'])) : ?><td><?php echo htmlspecialchars((string) ($row['pg_group_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td><?php endif; ?>

                <?php if (!empty($this->config['show_champion_tip'])) : ?>
                    <td class="text-center">
                        <?php if (!empty($row['showTips']) && !empty($row['champion']['teamId'])) : ?>
                            <?php
                            $championTeam = $row['champion']['team'] ?? null;
                            $logo = !empty($this->config['show_champion_tip_club_logo']) ? $logoForTeam($championTeam, (string) $this->config['champion_logo_size']) : '';
                            if ($logo !== '') {
                                echo HTMLHelper::image($logo, (string) ($championTeam->name ?? ''), ['style' => 'max-height:20px']);
                            } else {
                                echo '&#9917;';
                            }
                            if (!empty($this->config['show_champion_tip_result']) && $row['champion']['points'] !== false) {
                                echo '<sub>' . (int) $row['champion']['points'] . '</sub>';
                            }
                            ?>
                        <?php else : ?>--<?php endif; ?>
                    </td>
                <?php endif; ?>

                <?php if (!empty($this->config['show_final4_tip'])) : ?>
                    <?php for ($slot = 0; $slot < 4; $slot++) : ?>
                        <?php $tip = $row['final4'][$slot] ?? null; ?>
                        <td class="text-center">
                            <?php if (!empty($row['showTips']) && $tip) : ?>
                                <?php
                                $finalTeam = $tip['team'] ?? null;
                                $logo = !empty($this->config['show_final4_tip_club_logo']) ? $logoForTeam($finalTeam, (string) $this->config['final4_logo_size']) : '';
                                if ($logo !== '') {
                                    echo HTMLHelper::image($logo, (string) ($finalTeam->name ?? ''), ['style' => 'max-height:20px']);
                                } else {
                                    echo '&#9917;';
                                }
                                if (!empty($this->config['show_final4_tip_result']) && $tip['points'] !== false) {
                                    echo '<sub>' . (int) $tip['points'] . '</sub>';
                                }
                                ?>
                            <?php else : ?>--<?php endif; ?>
                        </td>
                    <?php endfor; ?>
                <?php endif; ?>

                <?php if (!empty($this->config['show_tip_details'])) : ?>
                    <td class="text-center">
                        <?php if (!empty($member->show_profile) || $isCurrent) : ?>
                            <a href="<?php echo htmlspecialchars($detailsUrl, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_DETAILS'); ?>">&#128269;</a>
                        <?php else : ?>&nbsp;<?php endif; ?>
                    </td>
                <?php endif; ?>
            <?php endif; ?>

            <td class="text-center"><?php echo (int) ($row['totalPoints'] ?? 0); ?></td>
            <?php if (!empty($this->config['show_average_points'])) : ?>
                <td class="text-center"><?php echo !empty($row['predictionsCount']) ? number_format((float) $row['totalPoints'] / (int) $row['predictionsCount'], 2) : number_format(0, 2); ?></td>
            <?php endif; ?>
            <?php if (!empty($this->config['show_count_tips'])) : ?><td class="text-center"><?php echo (int) ($row['predictionsCount'] ?? 0); ?></td><?php endif; ?>
            <?php if (!empty($this->config['show_count_joker'])) : ?><td class="text-center"><?php echo (int) ($row['totalJoker'] ?? 0); ?></td><?php endif; ?>
            <?php if (!empty($this->config['show_count_topptips'])) : ?><td class="text-center"><?php echo (int) ($row['totalTop'] ?? 0); ?></td><?php endif; ?>
            <?php if (!empty($this->config['show_count_difftips'])) : ?><td class="text-center"><?php echo (int) ($row['totalDiff'] ?? 0); ?></td><?php endif; ?>
            <?php if (!empty($this->config['show_count_tendtipps'])) : ?><td class="text-center"><?php echo (int) ($row['totalTend'] ?? 0); ?></td><?php endif; ?>
        </tr>
    <?php endforeach; ?>
    <?php if (!$this->rankingRows) : ?>
        <tr><td colspan="20" class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOT_AVAILABLE'); ?></td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>

<?php if ($this->pagination && empty($this->config['show_all_user']) && $this->pagination->pagesTotal > 1) : ?>
    <div class="pagination justify-content-center">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
<?php endif; ?>
