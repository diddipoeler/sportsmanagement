<?php
/**
 * Joomla 5/6 default layout for mod_sportsmanagement_count_rekord.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$moduleClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
$tableClass = htmlspecialchars((string) $params->get('table_class', 'table'), ENT_QUOTES, 'UTF-8');
$moduleId = htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES, 'UTF-8');
?>
<div id="<?php echo $moduleId; ?>" class="<?php echo $moduleClass; ?>">
    <?php if ($list) : ?>
        <table class="<?php echo $tableClass; ?>">
            <tbody>
            <?php foreach ($list as $row) : ?>
                <tr>
                    <td><?php echo $row->text; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
