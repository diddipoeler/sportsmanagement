<?php
/**
 * Joomla 5/6 native layout for the SportsManagement Ranking module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

$ranking = $list['ranking'] ?? [];
if (!$ranking) {
    echo '<p class="modjlgranking">' . Text::_('NO ITEMS') . '</p>';
    return;
}

$e = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$project = $list['project'];
$columns = $list['columns'] ?? [];
$columnNames = $list['column_names'] ?? [];
$colors = $list['colors'] ?? [];
$favTeamId = 0;
$visible = $params->get('visible_team', '');
if (preg_match('/^\s*(\d+)/', is_array($visible) ? (string) reset($visible) : (string) $visible, $match)) {
    $favTeamId = (int) $match[1];
}
if ($favTeamId <= 0) {
    $favTeamId = (int) ($project->fav_team ?? 0);
}
$favEntireRow = (int) $params->get('fav_team_highlight_type', 0) === 1;
?>
<div class="container-fluid mod-sm-ranking">
    <?php if ($params->get('show_project_name', 0)) : ?><p class="projectname"><?php echo $e($project->name); ?></p><?php endif; ?>
    <div class="table-responsive">
        <table class="<?php echo $e($params->get('table_class', 'table')); ?>">
            <thead>
            <tr>
                <?php if ((int) $params->get('showRankColumn', 1) === 1) : ?><th><?php echo Text::_('MOD_SPORTSMANAGEMENT_RANKING_COLUMN_RANK'); ?></th><?php endif; ?>
                <th><?php echo Text::_('MOD_SPORTSMANAGEMENT_RANKING_COLUMN_TEAM'); ?></th>
                <?php foreach ($columnNames as $name) : ?><th><?php echo Text::_((string) $name); ?></th><?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($ranking as $item) :
                $color = '';
                if ((int) $params->get('show_rank_colors', 0)) {
                    foreach ($colors as $colorItem) {
                        if ((int) $item->rank >= (int) ($colorItem['from'] ?? 0) && (int) $item->rank <= (int) ($colorItem['to'] ?? 0)) {
                            $color = (string) ($colorItem['color'] ?? '');
                        }
                    }
                }
                $isFavourite = (int) ($item->team->id ?? 0) === $favTeamId;
                if ($isFavourite && $favEntireRow && trim((string) ($project->fav_team_color ?? '')) !== '') {
                    $color = (string) $project->fav_team_color;
                }
                $styles = [];
                if ($color !== '') {
                    $styles[] = 'background-color:' . $color;
                }
                if ($isFavourite && $favEntireRow && (int) $params->get('fav_team_bold', 0)) {
                    $styles[] = 'font-weight:bold';
                }
                if ($isFavourite && $favEntireRow && (string) ($project->fav_team_text_color ?? '') !== '') {
                    $styles[] = 'color:' . (string) $project->fav_team_text_color;
                }
                $style = $styles ? ' style="' . $e(implode(';', $styles)) . '"' : '';
                ?>
                <tr<?php echo $style; ?>>
                    <?php if ((int) $params->get('showRankColumn', 1) === 1) : ?><td><?php echo (int) $item->rank; ?></td><?php endif; ?>
                    <td>
                        <?php if ((int) $params->get('show_logo', 0) && (string) $item->logo_url !== '') : ?>
                            <img src="<?php echo $e($item->logo_url); ?>" alt="<?php echo $e($item->display_team_name); ?>" class="teamlogo" style="max-width:20px;height:auto;">
                        <?php endif; ?>
                        <?php if ((string) $item->team_url !== '') : ?><a href="<?php echo $e($item->team_url); ?>"><?php echo $e($item->display_team_name); ?></a><?php else : ?><?php echo $e($item->display_team_name); ?><?php endif; ?>
                    </td>
                    <?php foreach ($columns as $column) : ?><td><?php echo $e($item->column_values[$column] ?? '?'); ?></td><?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($params->get('show_ranking_link', 1) && !empty($list['full_table_url'])) : ?>
        <p><a href="<?php echo $e($list['full_table_url']); ?>"><?php echo Text::_('MOD_SPORTSMANAGEMENT_RANKING_VIEW_FULL_TABLE'); ?></a></p>
    <?php endif; ?>

    <?php if (!empty($list['can_refresh']) && !empty($list['module_id'])) :
        $buttonId = 'jsm-ranking-refresh-' . (int) $list['module_id'];
        $statusId = $buttonId . '-status';
        $token = Session::getFormToken();
        ?>
        <p class="mod-sm-ranking-refresh">
            <button type="button" class="btn btn-secondary" id="<?php echo $e($buttonId); ?>" data-url="<?php echo $e($list['refresh_url']); ?>">
                <?php echo Text::_('MOD_SPORTSMANAGEMENT_ISHD_UPDATE_LABEL'); ?>
            </button>
            <span id="<?php echo $e($statusId); ?>" aria-live="polite"></span>
        </p>
        <script>
        (() => {
            const button = document.getElementById(<?php echo json_encode($buttonId); ?>);
            const status = document.getElementById(<?php echo json_encode($statusId); ?>);
            if (!button || !status) return;
            button.addEventListener('click', async () => {
                button.disabled = true;
                status.textContent = '…';
                const body = new URLSearchParams();
                body.set('module_id', <?php echo json_encode((string) (int) $list['module_id']); ?>);
                body.set(<?php echo json_encode($token); ?>, '1');
                try {
                    const response = await fetch(button.dataset.url, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                        body: body.toString(),
                        credentials: 'same-origin'
                    });
                    const payload = await response.json();
                    if (!response.ok || payload.success === false) {
                        throw new Error(payload.message || response.statusText);
                    }
                    const data = payload.data || {};
                    status.textContent = data.updated ? '✓ ' + (data.pending ?? '') : '✓ 0';
                } catch (error) {
                    status.textContent = '⚠ ' + (error.message || 'Error');
                } finally {
                    button.disabled = false;
                }
            });
        })();
        </script>
    <?php endif; ?>
</div>
