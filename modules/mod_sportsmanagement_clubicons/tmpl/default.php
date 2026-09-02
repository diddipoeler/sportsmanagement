<?php
/**
 * SportsManagement club icons native layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$iconsPerRow = max(1, (int) $params->get('iconsperrow', 20));
$tableClass = htmlspecialchars((string) $params->get('table_class', 'table'), ENT_QUOTES, 'UTF-8');
$moduleClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
$newWindow = (int) $params->get('teamlink', 0) === 5 && (int) $params->get('newwindow', 0) === 1;
$alignment = match ((string) $params->get('iconpos', 'middle')) {
    'top' => 'align-top',
    'bottom' => 'align-bottom',
    default => 'align-middle',
};
?>
<?php if ($count > 0) : ?>
<div class="mod-sportsmanagement-clubicons <?php echo $moduleClass; ?>" id="<?php echo htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES, 'UTF-8'); ?>">
    <table id="clubicons<?php echo (int) $module->id; ?>" class="<?php echo $tableClass; ?>">
        <tbody>
        <tr>
            <?php $column = 0; ?>
            <?php foreach ($ranking as $row) : ?>
                <?php
                $projectTeamId = (int) ($row->projectteamid ?? 0);
                if ($projectTeamId <= 0 || !isset($teams[$projectTeamId])) {
                    continue;
                }
                $team = $teams[$projectTeamId];
                $link = (string) ($team['link'] ?? '');
                $logoUrl = (string) ($team['logo_url'] ?? '');
                $name = (string) ($team['name'] ?? '');
                $column++;
                ?>
                <td class="<?php echo $alignment; ?>">
                    <?php if ($link !== '') : ?>
                        <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $newWindow ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php endif; ?>
                    <?php if ($logoUrl !== '') : ?>
                        <img class="img-zoom" src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                             title="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    <?php endif; ?>
                    <?php if ($link !== '') : ?></a><?php endif; ?>
                </td>
                <?php if ($column % $iconsPerRow === 0) : ?>
                    </tr><tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
