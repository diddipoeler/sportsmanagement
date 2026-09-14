<?php
/**
 * Native Joomla 5/6 administrator warnings box for the list header.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$warnings = isset($this->warnings) && is_array($this->warnings) ? $this->warnings : [];

if ($warnings === []) {
    return;
}
?>
<div class="color-box">
    <div class="shadow">
        <div class="fas fa-exclamation-triangle fa-2x fa-pull-left" title="<?php echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WARNING'), ENT_QUOTES, 'UTF-8'); ?>"><i></i></div>
        <div class="warning-box">
            <p>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WARNING'); ?></strong>
                <?php echo implode('<br>', $warnings); ?>
            </p>
        </div>
    </div>
</div>
