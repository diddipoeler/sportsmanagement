<?php
/**
 * Native Joomla 5/6 frontend layout for editing a match.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage editmatch
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
    <div class="btn-toolbar justify-content-end gap-2 mb-4" role="toolbar">
        <button type="button" class="btn btn-success" data-editmatch-submit-task="editmatch.apply">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE'); ?>
        </button>
        <button type="button" class="btn btn-primary" data-editmatch-submit-task="editmatch.save">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE'); ?>
        </button>
        <button type="button" class="btn btn-secondary" data-editmatch-submit-task="editmatch.cancel">
            <?php echo Text::_('JCANCEL'); ?>
        </button>
    </div>

    <fieldset class="options-form mb-4">
        <legend class="h5"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD'); ?></legend>
        <?php foreach ($this->form->getFieldset('matchdetails') as $field) : ?>
            <?php if (strtolower((string) $field->type) === 'hidden') : ?>
                <?php echo $field->input; ?>
                <?php continue; ?>
            <?php endif; ?>
            <div class="control-group mb-3">
                <div class="control-label"><?php echo $field->label; ?></div>
                <div class="controls"><?php echo $field->input; ?></div>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <fieldset class="options-form">
        <legend class="h5"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD'); ?></legend>

        <div class="control-group mb-3">
            <div class="control-label">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL'); ?>
            </div>
            <div class="controls">
                <?php echo $this->lists['count_result']; ?>
            </div>
        </div>

        <div class="control-group mb-3">
            <div class="control-label">
                <label for="alt_decision"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_SUB_DEC'); ?></label>
            </div>
            <div class="controls">
                <select class="form-select w-auto" name="alt_decision" id="alt_decision">
                    <option value="0"<?php echo $altDecision === 0 ? ' selected' : ''; ?>>
                        <?php echo Text::_('JNO'); ?>
                    </option>
                    <option value="1"<?php echo $altDecision === 1 ? ' selected' : ''; ?>>
                        <?php echo Text::_('JYES'); ?>
                    </option>
                </select>
            </div>
        </div>

        <div id="alt_decision_enter" class="mt-4"<?php echo $altDecision === 0 ? ' hidden' : ''; ?>>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="team1_result_decision">
                        <?php echo $escape(
                            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE')
                            . ' '
                            . ($this->match->hometeam ?? '')
                        ); ?>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="team1_result_decision"
                        name="team1_result_decision"
                        value="<?php echo $escape($altDecision === 1 ? ($this->match->team1_result_decision ?? 'X') : ''); ?>"
                        <?php echo $altDecision === 0 ? 'disabled' : ''; ?>
                    >
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label" for="team2_result_decision">
                        <?php echo $escape(
                            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_NEW_SCORE')
                            . ' '
                            . ($this->match->awayteam ?? '')
                        ); ?>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="team2_result_decision"
                        name="team2_result_decision"
                        value="<?php echo $escape($altDecision === 1 ? ($this->match->team2_result_decision ?? 'X') : ''); ?>"
                        <?php echo $altDecision === 0 ? 'disabled' : ''; ?>
                    >
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label" for="decision_info">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_REASON_NEW_SCORE'); ?>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="decision_info"
                        name="decision_info"
                        value="<?php echo $escape($altDecision === 1 ? ($this->match->decision_info ?? '') : ''); ?>"
                        <?php echo $altDecision === 0 ? 'disabled' : ''; ?>
                    >
                </div>

                <div class="col-12">
                    <div class="form-label"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_TEAM_WON'); ?></div>
                    <?php echo $this->lists['team_won']; ?>
                </div>
            </div>
        </div>
    </fieldset>

    <input type="hidden" name="assignperson" value="0" id="assignperson">
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
