<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editstats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<form name="adminForm" id="adminForm" method="post" action="<?php echo $this->uri->toString(); ?>">
    <div id="jlstatsform">
        <fieldset>
            <div class="fltrt">
                <button type="button" onclick="Joomla.submitform('editmatch.savestats', this.form);">
                    <?php echo Text::_('JSAVE'); ?>
                </button>
                <button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
                    <?php echo Text::_('JCANCEL'); ?>
                </button>
            </div>
            <div class="configuration"></div>
        </fieldset>
        <div class="clear"></div>

        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'home']); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'home', Text::_($this->teams->team1, true)); ?>
        <?php echo $this->loadTemplate('home'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'away', Text::_($this->teams->team2, true)); ?>
        <?php echo $this->loadTemplate('away'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="">
        <input type="hidden" name="close" id="close" value="0">
        <input type="hidden" name="task" value="">
        <input type="hidden" name="p" value="<?php echo EditmatchModel::$projectid; ?>">
        <input type="hidden" name="r" value="<?php echo EditmatchModel::$roundid; ?>">
        <input type="hidden" name="s" value="<?php echo EditmatchModel::$seasonid; ?>">
        <input type="hidden" name="id" value="<?php echo $this->match->id; ?>">
        <input type="hidden" name="match_id" value="<?php echo $this->match->id; ?>">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
<div style="clear: both"></div>
