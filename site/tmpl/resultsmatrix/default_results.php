<?php
/**
 * Native Joomla 5/6 match results list embedded in the matrix page.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escapeFixture = static fn (string $text): string =>
    htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<?php if ($this->fixtures === []) : ?>
    <p class="text-muted"><?= $escapeFixture(Text::_('JGLOBAL_NO_MATCHING_RESULTS')) ?></p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
            <tr>
                <th scope="col"><?= $escapeFixture(Text::_('COM_SPORTSMANAGEMENT_ROUND')) ?></th>
                <th scope="col"><?= $escapeFixture(Text::_('COM_SPORTSMANAGEMENT_HOME_TEAM')) ?></th>
                <th scope="col"><?= $escapeFixture(Text::_('COM_SPORTSMANAGEMENT_RESULTS')) ?></th>
                <th scope="col"><?= $escapeFixture(Text::_('COM_SPORTSMANAGEMENT_AWAY_TEAM')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->fixtures as $match) : ?>
                <tr>
                    <td><?= $escapeFixture($match['round']) ?></td>
                    <td><?= $escapeFixture($match['home']) ?></td>
                    <td><?= $escapeFixture($match['score']) ?></td>
                    <td><?= $escapeFixture($match['away']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
