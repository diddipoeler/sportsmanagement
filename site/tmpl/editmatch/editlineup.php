<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editlineup.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$isGolfBillard = (string) ($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD';
?>
<form name="editmatch" id="editmatch" method="post" action="<?php echo $escape($this->uri->toString()); ?>">
    <fieldset>
        <div class="fltrt">
            <?php if ($isGolfBillard) : ?>
                <button type="button" data-submit-task="editmatch.saverosterbillard">
                    <?php echo Text::_('JSAVE'); ?>
                </button>
            <?php else : ?>
                <button
                    type="button"
                    data-submit-task="editmatch.saveroster"
                    data-select-all-before-submit="select.position-starters option, select.position-staff option"
                >
                    <?php echo Text::_('JSAVE'); ?>
                </button>
            <?php endif; ?>
            <button type="button" data-submit-task="editmatch.cancel">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
        <div class="configuration">
            <?php echo $escape(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELU_TITLE', (string) $this->teamname)); ?>
        </div>
    </fieldset>
    <div class="clear"></div>
    <div id="lineup">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'player']); ?>
        <?php if ($isGolfBillard) : ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'player', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS', true)); ?>
            <?php echo $this->loadTemplate('golfbillardplayer'); ?>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php else : ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'player', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS', true)); ?>
            <?php echo $this->loadTemplate('players'); ?>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'substitutions', Text::_('COM_SPORTSMANAGEMENT_TABS_SUBST', true)); ?>
            <?php echo $this->loadTemplate('substitutions'); ?>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'staff', Text::_('COM_SPORTSMANAGEMENT_TABS_STAFF', true)); ?>
            <?php echo $this->loadTemplate('staff'); ?>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'players_trikot_numbers', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS', true)); ?>
            <?php echo $this->loadTemplate('players_trikot_numbers'); ?>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php endif; ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="view" value="">
        <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
        <input type="hidden" name="p" value="<?php echo (int) EditmatchModel::$projectid; ?>">
        <input type="hidden" name="r" value="<?php echo (int) EditmatchModel::$roundid; ?>">
        <input type="hidden" name="s" value="<?php echo (int) EditmatchModel::$seasonid; ?>">
        <input type="hidden" name="division" value="<?php echo (int) EditmatchModel::$divisionid; ?>">
        <input type="hidden" name="cfg_which_database" value="<?php echo (int) EditmatchModel::$cfg_which_database; ?>">
        <input type="hidden" name="close" id="close" value="0">
        <input type="hidden" name="id" value="<?php echo (int) ($this->match->id ?? 0); ?>">
        <input type="hidden" name="changes_check" value="0" id="changes_check">
        <input type="hidden" name="team" value="<?php echo (int) $this->tid; ?>" id="team">
        <input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
