<?php
/**
 * SportsManagement hit list items layout for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

foreach (($this->model_hits ?? []) as $key => $values) :
?>
    <table class="<?php echo htmlspecialchars((string) $this->tableclass, ENT_QUOTES, 'UTF-8'); ?>">
        <thead>
            <tr>
                <th colspan="2"><?php echo htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($values as $row) : ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($row->name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int) ($row->hits ?? 0); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>
