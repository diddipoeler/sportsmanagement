<?php
/**
 * SportsManagement club icons default layout.
 */
\defined('_JEXEC') or die('Restricted access');

$iconsPerRow = max(1, (int) $params->get('iconsperrow', 20));
$tableClass = htmlspecialchars((string) $params->get('table_class', 'table'), ENT_QUOTES, 'UTF-8');
?>
<table id="clubicons<?php echo (int) $module->id; ?>" class="<?php echo $tableClass; ?>">
    <tbody>
    <tr>
        <?php
        $column = 0;
        foreach ((array) $data->ranking as $projectTeamId => $rankingRow) :
            if (!isset($data->teams[$projectTeamId])) {
                continue;
            }

            $team = $data->teams[$projectTeamId];
            $newWindow = (int) $params->get('teamlink', 0) === 5 && (int) $params->get('newwindow', 0) === 1;
            $link = (string) ($team['link'] ?? '');
            $column++;
            ?>
            <td>
                <?php if ($link !== '') : ?>
                    <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $newWindow ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                <?php endif; ?>
                <?php echo $team['logo'] ?? ''; ?>
                <?php if ($link !== '') : ?>
                    </a>
                <?php endif; ?>
            </td>
            <?php if ($column % $iconsPerRow === 0) : ?>
                </tr><tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </tr>
    </tbody>
</table>
