<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Native ranking manipulation/start-point notes. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

if ($this->rankingNotes === []) {
    return;
}
?>
<section class="ranking-notes my-3" aria-labelledby="ranking-notes-title">
    <h3 class="h6" id="ranking-notes-title"><?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_NOTES'); ?></h3>
    <ul class="list-unstyled mb-0">
        <?php foreach ($this->rankingNotes as $note) : ?>
            <li class="<?php echo (float) $note->points < 0 ? 'text-danger' : 'text-success'; ?>">
                <strong><?php echo $this->escape((string) $note->team); ?></strong>:
                <?php echo $this->escape((string) ($note->points + 0)); ?>
                <?php if (trim((string) $note->reason) !== '') : ?>
                    – <?php echo $this->escape((string) $note->reason); ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
