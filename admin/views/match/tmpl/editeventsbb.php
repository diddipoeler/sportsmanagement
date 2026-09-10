<?php
/**
 * SportsManagement match event tabs for Joomla 5/6.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage match
 * @file       editeventsbb.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2026 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$escape   = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$homeTeam = $escape($this->teams->team1 ?? '');
$awayTeam = $escape($this->teams->team2 ?? '');
?>
<div id="gamesevents">
    <form method="post" id="adminForm" name="adminForm" class="form-validate">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'match-events-tabs', ['active' => 'panel1']); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'match-events-tabs', 'panel1', $homeTeam); ?>
        <?php echo $this->loadTemplate('home'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'match-events-tabs', 'panel2', $awayTeam); ?>
        <?php echo $this->loadTemplate('away'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="view" value="match">
        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<div style="clear: both"></div>
