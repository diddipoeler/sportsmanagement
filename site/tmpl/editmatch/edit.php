<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       edit.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$isGolfBillard = (string) ($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD';

if ($isGolfBillard) :
    ?>
    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'home']); ?>
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'home', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS', true)); ?>
    <?php echo $this->loadTemplate('singlematchbillard'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    <?php
    return;
endif;

$this->getDocument()->getWebAssetManager()->useScript('form.validate');
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const altDecision = document.getElementById('alt_decision');

    if (!altDecision) {
        return;
    }

    const toggleAltDecision = function () {
        const enabled = altDecision.value !== '0';
        const container = document.getElementById('alt_decision_enter');

        if (container) {
            container.style.display = enabled ? 'block' : 'none';
        }

        ['team1_result_decision', 'team2_result_decision', 'decision_info'].forEach(function (id) {
            const field = document.getElementById(id);

            if (field) {
                field.disabled = !enabled;
            }
        });
    };

    altDecision.addEventListener('change', toggleAltDecision);
    toggleAltDecision();
});
</script>

<form name="editmatch" id="editmatch" class="form-validate" method="post" action="<?php echo $escape($this->uri->toString()); ?>">
    <fieldset class="adminform">
        <div class="fltrt"></div>
        <legend></legend>
    </fieldset>

    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'home']); ?>
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'home', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS', true)); ?>
    <?php echo $this->loadTemplate('matchdetails'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu1', Text::_('COM_SPORTSMANAGEMENT_TABS_ALTDECISION', true)); ?>
    <?php echo $this->loadTemplate('altdecision'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu2', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW', true)); ?>
    <?php echo $this->loadTemplate('matchpreview'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu3', Text::_('COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS', true)); ?>
    <?php echo $this->loadTemplate('scoredetails'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu4', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHREPORT', true)); ?>
    <?php echo $this->loadTemplate('matchreport'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu5', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHRELATION', true)); ?>
    <?php echo $this->loadTemplate('matchrelation'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'menu6', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED', true)); ?>
    <?php echo $this->loadTemplate('matchextended'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

    <div class="clr"></div>
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="view" value="editmatch">
    <input type="hidden" name="layout" value="edit">
    <input type="hidden" name="oldlayout" value="<?php echo $escape(EditmatchModel::$oldlayout); ?>">
    <input type="hidden" name="tmpl" value="component">
    <input type="hidden" name="cfg_which_database" value="<?php echo (int) EditmatchModel::$cfg_which_database; ?>">
    <input type="hidden" name="s" value="<?php echo (int) EditmatchModel::$seasonid; ?>">
    <input type="hidden" name="p" value="<?php echo (int) EditmatchModel::$projectid; ?>">
    <input type="hidden" name="r" value="<?php echo (int) EditmatchModel::$roundid; ?>">
    <input type="hidden" name="divisionid" value="<?php echo (int) EditmatchModel::$divisionid; ?>">
    <input type="hidden" name="mode" value="<?php echo $escape(EditmatchModel::$mode); ?>">
    <input type="hidden" name="order" value="<?php echo $escape(EditmatchModel::$order); ?>">
    <input type="hidden" name="task" value="editmatch.saveshort">
    <input type="hidden" name="matchid" value="<?php echo (int) $this->match->id; ?>">
    <input type="hidden" name="sel_r" value="<?php echo (int) EditmatchModel::$roundid; ?>">
    <input type="hidden" name="Itemid" value="<?php echo Factory::getApplication()->getInput()->getInt('Itemid', 1); ?>">
    <input type="hidden" name="boxchecked" value="0" id="boxchecked">
    <input type="hidden" name="checkmycontainers" value="0" id="checkmycontainers">
    <input type="hidden" name="save_data" value="1" class="button">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
