<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editeventsbb.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$tabsOptions = ['active' => 'panel1'];
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div id="gamesevents">
    <form method="post" id="adminForm">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'ID-Tabs-J31-Group', $tabsOptions); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel1', $escape($this->teams->team1 ?? '')); ?>
        <?php echo $this->loadTemplate('home'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel2', $escape($this->teams->team2 ?? '')); ?>
        <?php echo $this->loadTemplate('away'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="view" value="match">
        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="boxchecked" id="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<div style="clear: both"></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adminForm');

    if (!form) {
        return;
    }

    const updateCheckedCount = function () {
        const boxchecked = document.getElementById('boxchecked');

        if (boxchecked) {
            boxchecked.value = String(form.querySelectorAll('.event-player-check:checked').length);
        }
    };

    form.addEventListener('change', function (event) {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const checkboxId = target.dataset.playerCheckbox;

        if (checkboxId) {
            const checkbox = document.getElementById(checkboxId);

            if (checkbox instanceof HTMLInputElement) {
                checkbox.checked = true;
            }
        }

        if (checkboxId || target.classList.contains('event-player-check')) {
            updateCheckedCount();
        }
    });

    updateCheckedCount();
});
</script>
