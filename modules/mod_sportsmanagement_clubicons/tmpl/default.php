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
/**
 * Encode valid club-icon module classes and labels as UTF-8 HTML.
 *
 * Joomla module settings may contain arrays or invalid UTF-8 from older
 * installations. Unsupported values are ignored instead of casting.
 *
 * @param mixed $value Raw module value.
 * @return string Escaped HTML-safe text or an empty string.
 */
$escape = static fn (mixed $value): string => htmlspecialchars(
    is_string($value) || is_numeric($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
$tableClass = $escape($params->get('table_class', 'table'));
$moduleClass = $escape($params->get('moduleclass_sfx', ''));
$newWindow = (int) $params->get('teamlink', 0) === 5 && (int) $params->get('newwindow', 0) === 1;
$rawAlignment = $params->get('iconpos', 'middle');
$alignment = match (is_string($rawAlignment) ? $rawAlignment : 'middle') {
    'top' => 'align-top',
    'bottom' => 'align-bottom',
    default => 'align-middle',
};

// Only render ranked teams that have module data, and calculate row breaks
// from the actual number of visible cells.
$displayRows = [];
foreach (is_array($ranking ?? null) ? $ranking : [] as $row) {
    if (!is_object($row) || !is_array($teams ?? null)) {
        continue;
    }
    $projectTeamId = (int) ($row->projectteamid ?? 0);
    if ($projectTeamId > 0 && isset($teams[$projectTeamId])) {
        $displayRows[] = $row;
    }
}
$displayCount = count($displayRows);
?>
<?php if ($displayCount > 0) : ?>
<div class="mod-sportsmanagement-clubicons <?php echo $moduleClass; ?>" id="<?php echo $escape(($module->module ?? 'mod_sportsmanagement_clubicons') . '-' . (int) ($module->id ?? 0)); ?>">
    <table id="clubicons<?php echo (int) $module->id; ?>" class="<?php echo $tableClass; ?>">
        <tbody>
        <tr>
            <?php $column = 0; ?>
            <?php foreach ($displayRows as $row) : ?>
                <?php
                $projectTeamId = (int) ($row->projectteamid ?? 0);
                $team = $teams[$projectTeamId];
                $link = is_string($team['link'] ?? null) ? $team['link'] : '';
                $logoUrl = is_string($team['logo_url'] ?? null) ? $team['logo_url'] : '';
                $name = is_string($team['name'] ?? null) ? $team['name'] : '';
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
                <?php if ($column % $iconsPerRow === 0 && $column < $displayCount) : ?>
                    </tr><tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>
