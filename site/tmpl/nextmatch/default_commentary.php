<?php
/**
 * Native Joomla 5/6 match commentary for the next-match view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<!-- START of match commentary -->
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?> table-responsive" id="nextmatch">
    <?php if (!empty($this->matchcommentary)) : ?>
        <table class="table">
            <tr>
                <td class="contentheading">
                    <?php echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY'); ?>
                </td>
            </tr>
        </table>

        <table class="table">
            <?php foreach ($this->matchcommentary as $commentary) : ?>
                <tr>
                    <td class="list">
                        <dl><?php echo $commentary->event_time; ?></dl>
                    </td>
                    <td class="list">
                        <dl><?php echo $commentary->notes; ?></dl>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
