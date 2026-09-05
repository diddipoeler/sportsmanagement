<?php
/**
 * Joomla 5/6 Top Tipper ranking layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!$predictionGame || !$predictionProject) {
    return;
}

$gameId = (int) ($predictionGame->id ?? 0);
$config = $topTipperConfig ?? [];
$tableClass = trim((string) $params->get('table_class', 'table'));
$tableClass = str_replace('table-condensed', 'table-sm', $tableClass);
$moduleClass = trim((string) $params->get('moduleclass_sfx', ''));
$buildRoute = static function (string $view, array $values) use ($databaseSelector): string {
    return SiteRouteHelper::view($view, array_merge([
        'cfg_which_database' => (int) $databaseSelector,
    ], $values));
};
?>
<div class="jsm-top-tipper<?php echo $moduleClass !== '' ? ' ' . htmlspecialchars($moduleClass, ENT_QUOTES, 'UTF-8') : ''; ?>"
     id="mod-sportsmanagement-top-tipper-<?php echo (int) $module->id; ?>">

    <?php if (!empty($config['show_project_name']) || !empty($config['show_project_name_selector'])) : ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <?php if (!empty($config['show_project_name'])) : ?>
                <strong>
                    <?php echo htmlspecialchars((string) ($predictionGame->name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    <?php if (!empty($predictionProject->projectName)) : ?>
                        <span class="text-muted">— <?php echo htmlspecialchars((string) $predictionProject->projectName, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </strong>
            <?php endif; ?>

            <?php if (!empty($config['show_project_name_selector']) && count($predictionProjects) > 1) : ?>
                <form method="get" class="d-flex align-items-center gap-1">
                    <label class="visually-hidden" for="top-tipper-project-<?php echo (int) $module->id; ?>">
                        <?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_SELECT_PREDICTION_GAME_LABEL'); ?>
                    </label>
                    <select class="form-select form-select-sm" name="pj" id="top-tipper-project-<?php echo (int) $module->id; ?>">
                        <?php foreach ($predictionProjects as $project) :
                            $value = (int) ($project->project_id ?? 0);
                            ?>
                            <option value="<?php echo $value; ?>"<?php echo $value === (int) $projectId ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars((string) ($project->projectName ?? $value), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?php echo Text::_('JGO'); ?></button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($config['show_tip_link_ranking_round']) || !empty($config['show_tip_ranking'])) : ?>
        <div class="d-flex flex-wrap gap-2 mb-2">
            <?php if (!empty($config['show_tip_link_ranking_round']) && $resultsUrl !== '') : ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars($resultsUrl, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_ROUND_RESULTS_TITLE'); ?>
                </a>
            <?php endif; ?>
            <?php if (!empty($config['show_tip_ranking']) && $rankingUrl !== '') : ?>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars($rankingUrl, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_HEAD_RANKING_IMAGE_TITLE'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($config['show_rankingnav']) && $roundOptions) : ?>
        <form method="get" class="row g-2 align-items-end mb-3">
            <input type="hidden" name="pj" value="<?php echo (int) $projectId; ?>">
            <div class="col-12 col-md-4">
                <label class="form-label" for="top-tipper-type-<?php echo (int) $module->id; ?>">
                    <?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK'); ?>
                </label>
                <select class="form-select form-select-sm" name="type" id="top-tipper-type-<?php echo (int) $module->id; ?>">
                    <?php foreach ($typeOptions as $option) : ?>
                        <option value="<?php echo (int) $option->value; ?>"<?php echo (int) $option->value === (int) $rankingType ? ' selected' : ''; ?>>
                            <?php echo htmlspecialchars((string) $option->text, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="top-tipper-from-<?php echo (int) $module->id; ?>">From</label>
                <select class="form-select form-select-sm" name="from" id="top-tipper-from-<?php echo (int) $module->id; ?>">
                    <?php foreach ($roundOptions as $option) :
                        $value = (int) strtok((string) ($option->value ?? '0'), ':');
                        ?>
                        <option value="<?php echo $value; ?>"<?php echo $value === (int) $fromRound ? ' selected' : ''; ?>>
                            <?php echo htmlspecialchars((string) ($option->text ?? $value), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="top-tipper-to-<?php echo (int) $module->id; ?>">To</label>
                <select class="form-select form-select-sm" name="to" id="top-tipper-to-<?php echo (int) $module->id; ?>">
                    <?php foreach ($roundOptions as $option) :
                        $value = (int) strtok((string) ($option->value ?? '0'), ':');
                        ?>
                        <option value="<?php echo $value; ?>"<?php echo $value === (int) $toRound ? ' selected' : ''; ?>>
                            <?php echo htmlspecialchars((string) ($option->text ?? $value), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_FILTER'); ?>
                </button>
            </div>
        </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="<?php echo htmlspecialchars($tableClass, ENT_QUOTES, 'UTF-8'); ?> align-middle">
            <thead>
            <tr>
                <th scope="col" class="text-center"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK'); ?></th>
                <?php if (!empty($config['show_user_icon'])) : ?>
                    <th scope="col" class="text-center"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_AVATAR'); ?></th>
                <?php endif; ?>
                <th scope="col"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_MEMBER'); ?></th>
                <?php if (!empty($config['show_tip_details'])) : ?>
                    <th scope="col" class="text-center"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_DETAILS'); ?></th>
                <?php endif; ?>
                <th scope="col" class="text-end"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_POINTS'); ?></th>
                <?php if (!empty($config['show_average_points'])) : ?>
                    <th scope="col" class="text-end"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_AVERAGE'); ?></th>
                <?php endif; ?>
                <?php if (!empty($config['show_count_tips'])) : ?>
                    <th scope="col" class="text-end"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_PREDICTIONS'); ?></th>
                <?php endif; ?>
                <?php if (!empty($config['show_count_joker'])) : ?>
                    <th scope="col" class="text-end"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_JOKERS'); ?></th>
                <?php endif; ?>
                <?php if (!empty($config['show_count_topptips'])) : ?>
                    <th scope="col" class="text-end"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_TOPS'); ?></th>
                <?php endif; ?>
                <?php if (!empty($config['show_count_difftips'])) : ?>
                    <th scope="col" class="text-end"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_MARGINS'); ?></th>
                <?php endif; ?>
                <?php if (!empty($config['show_count_tendtipps'])) : ?>
                    <th scope="col" class="text-end"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_TENDENCIES'); ?></th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rankingRows as $memberId => $row) :
                $member = $row['member'] ?? null;
                if (!is_object($member)) {
                    continue;
                }
                $memberId = (int) $memberId;
                $isCurrent = $memberId === (int) $currentMemberId;
                $canProfile = !empty($member->show_profile) || $isCurrent;
                $name = (string) (($member->aliasName ?? '') ?: ($member->name ?? ''));
                $avatar = ltrim((string) ($member->avatar ?? ''), '/');
                $avatarUrl = $avatar !== '' && is_file(JPATH_ROOT . '/' . $avatar)
                    ? rtrim(Uri::root(), '/') . '/' . $avatar
                    : '';
                $memberUrl = $canProfile ? $buildRoute('predictionusers', [
                    'prediction_id' => $gameId,
                    'uid' => $memberId,
                ]) : '';
                $detailsUrl = $canProfile ? $buildRoute('predictionresults', [
                    'prediction_id' => $gameId,
                    'uid' => $memberId,
                    'pj' => (int) $projectId,
                    'r' => (int) $roundId,
                ]) : '';
                $predictionCount = max(0, (int) ($row['predictionsCount'] ?? 0));
                $points = (int) ($row['totalPoints'] ?? 0);
                ?>
                <tr<?php echo $isCurrent ? ' class="table-warning"' : ''; ?>>
                    <td class="text-center"><?php echo htmlspecialchars((string) ($row['rank'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <?php if (!empty($config['show_user_icon'])) : ?>
                        <td class="text-center">
                            <?php if ($avatarUrl !== '') : ?>
                                <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                     alt="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                                     class="jsm-top-tipper-avatar" loading="lazy" width="25" height="25">
                            <?php else : ?>
                                <span class="icon-user" aria-hidden="true"></span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?php if (!empty($config['show_user_link']) && $memberUrl !== '') : ?>
                            <a href="<?php echo htmlspecialchars($memberUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></a>
                        <?php else : ?>
                            <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </td>
                    <?php if (!empty($config['show_tip_details'])) : ?>
                        <td class="text-center">
                            <?php if ($detailsUrl !== '') : ?>
                                <a href="<?php echo htmlspecialchars($detailsUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                   aria-label="<?php echo htmlspecialchars(Text::sprintf('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PRED_RANK_SHOW_DETAILS_OF', $name), ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="icon-search" aria-hidden="true"></span>
                                </a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td class="text-end"><?php echo $points; ?></td>
                    <?php if (!empty($config['show_average_points'])) : ?>
                        <td class="text-end"><?php echo number_format($predictionCount > 0 ? $points / $predictionCount : 0, 2); ?></td>
                    <?php endif; ?>
                    <?php if (!empty($config['show_count_tips'])) : ?>
                        <td class="text-end"><?php echo $predictionCount; ?></td>
                    <?php endif; ?>
                    <?php if (!empty($config['show_count_joker'])) : ?>
                        <td class="text-end"><?php echo (int) ($row['totalJoker'] ?? 0); ?></td>
                    <?php endif; ?>
                    <?php if (!empty($config['show_count_topptips'])) : ?>
                        <td class="text-end"><?php echo (int) ($row['totalTop'] ?? 0); ?></td>
                    <?php endif; ?>
                    <?php if (!empty($config['show_count_difftips'])) : ?>
                        <td class="text-end"><?php echo (int) ($row['totalDiff'] ?? 0); ?></td>
                    <?php endif; ?>
                    <?php if (!empty($config['show_count_tendtipps'])) : ?>
                        <td class="text-end"><?php echo (int) ($row['totalTend'] ?? 0); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($config['show_tip_ranking_text']) && $rankingUrl !== '') : ?>
        <p class="text-center mb-0">
            <a href="<?php echo htmlspecialchars($rankingUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo Text::_('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_TIP_RANKING_TEXT'); ?>
            </a>
        </p>
    <?php endif; ?>
</div>
