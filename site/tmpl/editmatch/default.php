<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$altDecision = (int) ($this->match->alt_decision ?? 0);
$assets = $this->getDocument()->getWebAssetManager();
$assets->useScript('form.validate');
$assets->registerAndUseScript(
    'com_sportsmanagement.editmatch-form',
    'components/com_sportsmanagement/assets/js/editmatch-form.js'
);
?>
<form
    name="editperson"
    id="editperson"
    class="form-validate"
    method="post"
    action="<?php echo $escape($this->uri->toString()); ?>"
    data-editmatch-form
>
    <fieldset class="adminform">
        <div class="fltrt">
            <button type="button" data-editmatch-submit-task="editmatch.apply">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE'); ?>
            </button>
            <button type="button" data-editmatch-submit-task="editmatch.save">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE'); ?>
            </button>
            <button type="button" data-editmatch-submit-task="editmatch.cancel">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </fieldset>

    <fieldset class="adminform">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD'); ?></legend>
        <table class="admintable">
            <tbody>
            <?php foreach ($this->form->getFieldset('matchdetails') as $field) : ?>
                <tr>
                    <td class="key"><?php echo $field->label; ?></td>
                    <td><?php echo $field->input; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </fieldset>

    <fieldset class="adminform">
        <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD'); ?></legend>
        <table class="admintable">
            <tbody>
            <tr>
                <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL'); ?></td>
                <td colspan="3"><?php echo $this->lists['count_result']; ?></td>
            </tr>
            <tr>
                <td class="key"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_SUB_DEC'); ?></td>
                <td colspan="3">
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
                <td colspan="4">
                    <div id="alt_decision_enter"<?php echo $altDecision === 0 ? ' hidden' : ''; ?>>
                        <table class="adminForm" cellspacing="7">
                            <tbody>
                            <tr>
                                <td class="key">
                                    <?php echo $escape(
                                        Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE')
                                        . ' '
                                        . ($this->match->hometeam ?? '')
                                    ); ?>
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="inputbox"
                                        id="team1_result_decision"
                                        name="team1_result_decision"
                                        size="4"
                                        value="<?php echo $escape($altDecision === 1 ? ($this->match->team1_result_decision ?? 'X') : ''); ?>"
                                        <?php echo $altDecision === 0 ? 'disabled' : ''; ?>
                                    >
                                </td>
                            </tr>
                            <tr>
                                <td class="key">
                                    <?php echo $escape(
                                        Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE')
                                        . ' '
                                        . ($this->match->awayteam ?? '')
                                    ); ?>
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="inputbox"
                                        id="team2_result_decision"
                                        name="team2_result_decision"
                                        size="4"
                                        value="<?php echo $escape($altDecision === 1 ? ($this->match->team2_result_decision ?? 'X') : ''); ?>"
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
                                        value="<?php echo $escape($altDecision === 1 ? ($this->match->decision_info ?? '') : ''); ?>"
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

    <div class="clr"></div>
    <input type="hidden" name="assignperson" value="0" id="assignperson">
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
