<?php
/**
 * Native Joomla 5/6 administrator position statistics fieldset layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$selectAssigned = "document.querySelectorAll('#position_statistic option').forEach((option) => { option.selected = true; });";
?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_STATISTICS_LEGEND'); ?></legend>
    <table class="table">
        <tr>
            <td style="width:auto;">
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EXISTING_STATISTICS'); ?></strong><br>
                <?php echo $this->lists['statistic'] ?? ''; ?>
            </td>
            <td style="width:auto;">
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="move_list_items('statistic','position_statistic');<?php echo $selectAssigned; ?>"
                >&gt;&gt;</button>
                <br><br>
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="move_list_items('position_statistic','statistic');<?php echo $selectAssigned; ?>"
                >&lt;&lt;</button>
            </td>
            <td style="width:auto;">
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_ASSIGNED_STATS_TO_POS'); ?></strong><br>
                <?php echo $this->lists['position_statistic'] ?? ''; ?>
            </td>
            <td class="text-center" style="width:auto;">
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="move_up('position_statistic');<?php echo $selectAssigned; ?>"
                ><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?></button>
                <br><br>
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="move_down('position_statistic');<?php echo $selectAssigned; ?>"
                ><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?></button>
            </td>
            <td style="width:auto;">
                <fieldset class="adminform">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_STATS_HINT'); ?>
                </fieldset>
            </td>
        </tr>
    </table>
</fieldset>
