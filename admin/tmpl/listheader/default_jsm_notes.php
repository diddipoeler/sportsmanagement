<?php
/**
 * Native Joomla 5/6 administrator notes box for the list header.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$notes = isset($this->notes) && is_array($this->notes) ? $this->notes : [];

if ($notes === []) {
    return;
}
?>
<div class="color-box">
    <div class="shadow">
        <div class="fas fa-edit fa-2x fa-pull-left" title="<?php echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE'), ENT_QUOTES, 'UTF-8'); ?>"><i></i></div>
        <div class="note-box">
            <p>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE'); ?></strong>
                <?php echo implode('<br>', $notes); ?>
            </p>
        </div>
    </div>
</div>
