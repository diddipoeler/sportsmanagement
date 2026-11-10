<?php
/**
 * Native Joomla 5/6 combined league ranking table.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escapeRanking = static fn (string $value): string =>
    htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<?php if ($this->standings === []) : ?>
    <p class="text-muted"><?= $escapeRanking(Text::_('JGLOBAL_NO_MATCHING_RESULTS')) ?></p>
<?php else : ?>
    <?php foreach ($this->standings as $group) : ?>
        <?php if ($group['division'] > 0) : ?>
            <h3 class="h5">
                <?= $escapeRanking(Text::_('COM_SPORTSMANAGEMENT_DIVISION')) ?>
                <?= (int) $group['division'] ?>
            </h3>
        <?php endif; ?>
        <div class="table-responsive mb-3">
            <table class="table table-striped table-sm">
                <thead><tr>
                    <th scope="col"><?= $escapeRanking(Text::_('COM_SPORTSMANAGEMENT_RANKING_RANK')) ?></th>
                    <th scope="col"><?= $escapeRanking(Text::_('COM_SPORTSMANAGEMENT_TEAM')) ?></th>
                    <th scope="col"><?= $escapeRanking(Text::_('COM_SPORTSMANAGEMENT_PLAYED')) ?></th>
                    <th scope="col"><?= $escapeRanking(Text::_('COM_SPORTSMANAGEMENT_WINS')) ?></th>
                    <th scope="col"><?= $escapeRanking(Text::_('COM_SPORTSMANAGEMENT_TIES')) ?></th>
                    <th scope="col"><?= $escapeRanking(Text::_('COM_SPORTSMANAGEMENT_LOSSES')) ?></th>
                    <th scope="col"><?= $escapeRanking(Text::_('COM_SPORTSMANAGEMENT_POINTS')) ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ($group['rows'] as $row) : ?>
                    <tr>
                        <td><?= (int) $row['rank'] ?></td>
                        <th scope="row"><?= $escapeRanking($row['name']) ?></th>
                        <td><?= $escapeRanking($row['played']) ?></td>
                        <td><?= $escapeRanking($row['wins']) ?></td>
                        <td><?= $escapeRanking($row['draws']) ?></td>
                        <td><?= $escapeRanking($row['losses']) ?></td>
                        <td><?= $escapeRanking($row['points']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
