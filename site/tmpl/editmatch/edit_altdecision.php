<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       edit_altdecision.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$altDecision = (int) ($this->match->alt_decision ?? 0);
?>
<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD'); ?></legend>
    <table class="admintable">
        <tbody>
        <?php foreach ($this->form->getFieldset('altdecision') as $field) : ?>
            <tr>
                <td class="key"><?php echo $field->label; ?></td>
                <td><?php echo $field->input; ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_SUB_DEC'); ?></td>
            <td colspan="2">
                <select name="alt_decision" id="alt_decision">
                    <option value="0"<?php echo $altDecision === 0 ? ' selected="selected"' : ''; ?>>
                        <?php echo Text::_('JNO'); ?>
                    </option>
                    <option value="1"<?php echo $altDecision === 1 ? ' selected="selected"' : ''; ?>>
                        <?php echo Text::_('JYES'); ?>
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div id="alt_decision_enter" style="display:<?php echo $altDecision === 0 ? 'none' : 'block'; ?>">
                    <table class="adminForm" cellspacing="7">
                        <tbody>
                        <tr>
                            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE') . ' ' . $this->match->hometeam; ?></td>
                            <td>
                                <input
                                    type="text"
                                    class="inputbox"
                                    id="team1_result_decision"
                                    name="team1_result_decision"
                                    size="3"
                                    value="<?php echo $altDecision === 1 ? ($this->match->team1_result_decision ?? 'X') : ''; ?>"
                                    <?php echo $altDecision === 0 ? 'disabled' : ''; ?>
                                >
                            </td>
                        </tr>
                        <tr>
                            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE') . ' ' . $this->match->awayteam; ?></td>
                            <td>
                                <input
                                    type="text"
                                    class="inputbox"
                                    id="team2_result_decision"
                                    name="team2_result_decision"
                                    size="3"
                                    value="<?php echo $altDecision === 1 ? ($this->match->team2_result_decision ?? 'X') : ''; ?>"
                                    <?php echo $altDecision === 0 ? 'disabled' : ''; ?>
                                >
                            </td>
                        </tr>
                        <tr>
                            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_REASON_NEW_SCORE'); ?></td>
                            <td>
                                <input
                                    type="text"
                                    class="inputbox"
                                    id="decision_info"
                                    name="decision_info"
                                    size="30"
                                    value="<?php echo $altDecision === 1 ? ($this->match->decision_info ?? '') : ''; ?>"
                                    <?php echo $altDecision === 0 ? 'disabled' : ''; ?>
                                >
                            </td>
                        </tr>
                        <tr>
                            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_TEAM_WON'); ?></td>
                            <td><?php echo $this->lists['team_won']; ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</fieldset>
