<?php
/**
 * Joomla 5/6 Team Stats Ranking layout.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Language\Text;

if (!$project || !$stat || $ranking === []) {
    echo '<div class="alert alert-info mb-0">'
        . htmlspecialchars(Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATS_RANKING_NO_ITEMS'), ENT_QUOTES, 'UTF-8')
        . '</div>';
    return;
}

$teamNameType = (string) $params->get('teamnametype', 'short_name');
if (!in_array($teamNameType, ['name', 'short_name', 'middle_name'], true)) {
    $teamNameType = 'short_name';
}

$tableClass = str_replace(
    'table-condensed',
    'table-sm',
    trim((string) $params->get('table_class', 'table'))
);
$moduleClass = trim((string) $params->get('moduleclass_sfx', ''));
$linkView = (string) $params->get('teamlink', '');
$showLogo = (int) $params->get('show_logo', 0);
$lastRank = null;

$teamUrl = static function (object $team) use ($linkView, $project, $databaseSelector): string {
    if ($linkView === '') {
        return '';
    }

    $query = [
        'cfg_which_database' => (int) $databaseSelector,
        's' => (string) ($project->season_slug ?? $project->season_id ?? ''),
        'p' => (string) ($project->slug ?? $project->id ?? ''),
    ];

    switch ($linkView) {
        case 'teaminfo':
        case 'roster':
        case 'teamplan':
            $query['tid'] = (string) ($team->team_slug ?? $team->id);
            $query['ptid'] = 0;
            if ($linkView !== 'teaminfo') {
                $query['division'] = 0;
            }
            if ($linkView === 'teamplan') {
                $query['mode'] = 0;
            }
            break;

        case 'clubinfo':
            $query['cid'] = (string) ($team->club_slug ?? $team->club_id ?? 0);
            break;

        default:
            return '';
    }

    return SiteRouteHelper::view($linkView, $query);
};
?>
<div class="jsm-teamstats-ranking<?php echo $moduleClass !== '' ? ' ' . htmlspecialchars($moduleClass, ENT_QUOTES, 'UTF-8') : ''; ?>">
    <?php if ((int) $params->get('show_project_name', 0) === 1) : ?>
        <p class="projectname mb-2">
            <?php echo htmlspecialchars((string) $project->name, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="<?php echo htmlspecialchars($tableClass, ENT_QUOTES, 'UTF-8'); ?> align-middle statranking">
            <thead>
            <tr>
                <th scope="col" class="text-center">
                    <?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATS_RANKING_COL_RANK'); ?>
                </th>
                <?php if ($showLogo > 0) : ?>
                    <th scope="col" class="text-center"></th>
                <?php endif; ?>
                <th scope="col">
                    <?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATS_RANKING_COL_TEAM'); ?>
                </th>
                <th scope="col" class="text-end">
                    <?php if ((int) $params->get('show_event_icon', 1) === 1 && !empty($stat->icon)
                        && $stat->icon !== 'media/com_sportsmanagement/event_icons/event.gif') : ?>
                        <img src="<?php echo htmlspecialchars((string) $stat->icon, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars(Text::_((string) $stat->name), ENT_QUOTES, 'UTF-8'); ?>"
                             class="jsm-teamstats-stat-icon" loading="lazy">
                    <?php else : ?>
                        <?php echo htmlspecialchars(Text::_((string) $stat->name), ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($ranking as $index => $item) :
                $team = $teams[(int) $item['team_id']] ?? null;
                if (!$team) {
                    continue;
                }

                $name = trim((string) ($team->{$teamNameType} ?? ''));
                if ($name === '') {
                    $name = (string) ($team->name ?? '');
                }
                $url = $teamUrl($team);
                $rank = (int) $item['rank'];
                $rankLabel = $lastRank === $rank ? '–' : (string) $rank;
                $lastRank = $rank;
                $rowClass = $index % 2 === 0
                    ? trim((string) $params->get('style_class1', ''))
                    : trim((string) $params->get('style_class2', ''));
                ?>
                <tr<?php echo $rowClass !== '' ? ' class="' . htmlspecialchars($rowClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
                    <td class="text-center"><?php echo htmlspecialchars($rankLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                    <?php if ($showLogo > 0) : ?>
                        <td class="text-center teamlogo">
                            <?php if ($showLogo === 1 && !empty($team->logo_big)) : ?>
                                <img src="<?php echo htmlspecialchars((string) $team->logo_big, ENT_QUOTES, 'UTF-8'); ?>"
                                     alt="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                                     class="jsm-teamstats-logo" loading="lazy">
                            <?php elseif ($showLogo === 2 && !empty($team->country)) : ?>
                                <span class="badge text-bg-light" title="<?php echo htmlspecialchars((string) $team->country, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(strtoupper((string) $team->country), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?php if ($url !== '') : ?>
                            <a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php else : ?>
                            <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?php echo htmlspecialchars((string) $item['total'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
