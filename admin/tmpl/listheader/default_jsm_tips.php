<?php
/**
 * Native Joomla 5/6 administrator tips box for the list header.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$tips = isset($this->tips) && is_array($this->tips) ? $this->tips : [];

if ($tips === []) {
    return;
}
?>
<div class="color-box">
    <div class="shadow">
        <div class="fas fa-quote-right fa-2x fa-pull-left" title="<?php echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TIP'), ENT_QUOTES, 'UTF-8'); ?>"><i></i></div>
        <div class="tip-box">
            <p>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TIP'); ?></strong>
                <?php echo implode('<br>', $tips); ?>
            </p>
        </div>
    </div>
</div>
