<?php
/**
 * Joomla 5/6 altered-decision layout for SportsManagement matches.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage match
 * @file       edit_altdecision.php
 * @author     diddipoeler, stony, svdoldie und donclumsy
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$hasAltDecision = (int) $this->match->alt_decision === 1;
$homeDecision = $hasAltDecision
    ? (isset($this->match->team1_result_decision) ? (string) $this->match->team1_result_decision : 'X')
    : '';
$awayDecision = $hasAltDecision
    ? (isset($this->match->team2_result_decision) ? (string) $this->match->team2_result_decision : 'X')
    : '';
$decisionInfo = $hasAltDecision ? (string) ($this->match->decision_info ?? '') : '';
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const altDecision = document.getElementById('alt_decision');
    const altDecisionFields = document.getElementById('alt_decision_enter');
    const controlledFields = [
        document.getElementById('team1_result_decision'),
        document.getElementById('team2_result_decision'),
        document.getElementById('decision_info')
    ];

    if (!altDecision || !altDecisionFields) {
        return;
    }

    const toggleAltDecision = function () {
        const enabled = altDecision.value === '1';

        altDecisionFields.hidden = !enabled;
        controlledFields.forEach(function (field) {
            if (field) {
                field.disabled = !enabled;
            }
        });
    };

    altDecision.addEventListener('change', toggleAltDecision);
    toggleAltDecision();
});
</script>

<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD'); ?></legend>
    <table class="admintable">
        <tr>
            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL'); ?></td>
            <td><?php echo $this->lists['count_result']; ?></td>
        </tr>
        <tr>
            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_SUB_DEC'); ?></td>
            <td colspan="2">
                <select name="alt_decision" id="alt_decision">
                    <option value="0"<?php echo $hasAltDecision ? '' : ' selected'; ?>>
                        <?php echo Text::_('JNO'); ?>
                    </option>
                    <option value="1"<?php echo $hasAltDecision ? ' selected' : ''; ?>>
                        <?php echo Text::_('JYES'); ?>
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div id="alt_decision_enter"<?php echo $hasAltDecision ? '' : ' hidden'; ?>>
                    <table class="adminForm" cellpadding="0" cellspacing="7" border="0">
                        <tr>
                            <td class="key">
                                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE') . ' ' . $this->escape($this->match->hometeam); ?>
                            </td>
                            <td>
                                <input type="text"
                                       class="inputbox"
                                       id="team1_result_decision"
                                       name="team1_result_decision"
                                       size="3"
                                       value="<?php echo $this->escape($homeDecision); ?>"
                                       <?php echo $hasAltDecision ? '' : 'disabled'; ?>>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE') . ' ' . $this->escape($this->match->awayteam); ?>
                            </td>
                            <td>
                                <input type="text"
                                       class="inputbox"
                                       id="team2_result_decision"
                                       name="team2_result_decision"
                                       size="3"
                                       value="<?php echo $this->escape($awayDecision); ?>"
                                       <?php echo $hasAltDecision ? '' : 'disabled'; ?>>
                            </td>
                        </tr>
                        <tr>
                            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_REASON_NEW_SCORE'); ?></td>
                            <td>
                                <input type="text"
                                       class="inputbox"
                                       id="decision_info"
                                       name="decision_info"
                                       size="30"
                                       value="<?php echo $this->escape($decisionInfo); ?>"
                                       <?php echo $hasAltDecision ? '' : 'disabled'; ?>>
                            </td>
                        </tr>
                        <tr>
                            <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_TEAM_WON'); ?></td>
                            <td><?php echo $this->lists['team_won']; ?></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</fieldset>
