<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editlineup_players.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_START_LU'); ?></legend>
    <table class="adminlist">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_ROSTER'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_ASSIGNED'); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="2">
                <span class="red">
                    <?php if (!empty($this->preFillSuccess)) : ?>
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_PREFILL_DONE'); ?><br><br>
                    <?php endif; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td style="text-align:center; vertical-align:middle;">
                <?php
                if (isset($this->lists['team_players'])) {
                    echo $this->lists['team_players'];
                } else {
                    echo Text::_('JGLOBAL_NO_MATCHING_RESULTS');
                }
                ?>
            </td>
            <td style="text-align:center; vertical-align:top;">
                <table>
                    <tbody>
                    <?php if (isset($this->positions)) : ?>
                        <?php foreach ($this->positions as $positionId => $pos) : ?>
                            <tr>
                                <td style="text-align:center; vertical-align:middle;">
                                    <br>
                                    <input
                                        type="button"
                                        value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RIGHT'); ?>"
                                        onclick="move_list_items('roster', 'position<?php echo (int) $positionId; ?>');"
                                    >
                                    <input
                                        type="button"
                                        value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEFT'); ?>"
                                        onclick="move_list_items('position<?php echo (int) $positionId; ?>', 'roster');"
                                    >
                                </td>
                                <td>
                                    <b><?php echo Text::_($pos->text); ?></b><br>
                                    <?php echo $this->lists['team_players' . $positionId]; ?>
                                </td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <br>
                                    <input
                                        type="button"
                                        class="inputbox move-up"
                                        value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>"
                                        onclick="move_up('position<?php echo (int) $positionId; ?>');"
                                    ><br>
                                    <input
                                        type="button"
                                        class="inputbox move-down"
                                        value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?>"
                                        onclick="move_down('position<?php echo (int) $positionId; ?>');"
                                    >
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>
