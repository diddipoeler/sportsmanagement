<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage match
 * @file       editstats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2026 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$input   = Factory::getApplication()->input;
$close   = $input->getInt('close', 0);
$refresh = $input->getBool('refresh', 0);
?>
<script>
    window.SportsManagementMatchStats = window.SportsManagementMatchStats || {};

    window.SportsManagementMatchStats.closeParentDialog = function (refreshParent) {
        if (window.parent === window) {
            return;
        }

        if (refreshParent) {
            window.parent.location.reload();
            return;
        }

        try {
            const frame = window.frameElement;
            const modalElement = frame ? frame.closest('.modal') : null;
            const BootstrapModal = window.parent.bootstrap?.Modal;

            if (modalElement && BootstrapModal) {
                const modal = BootstrapModal.getInstance(modalElement)
                    || BootstrapModal.getOrCreateInstance(modalElement);

                modal.hide();
                return;
            }
        } catch (error) {
            // Fall through to JoomlaDialog cross-window messaging.
        }

        window.parent.postMessage({messageType: 'joomla:cancel'}, window.location.origin);
    };

    window.SportsManagementMatchStats.submit = function (task, closeAfterSave) {
        const form = document.getElementById('adminform');

        if (!form) {
            return;
        }

        if (closeAfterSave) {
            const closeField = document.getElementById('close');

            if (closeField) {
                closeField.value = '1';
            }
        }

        Joomla.submitform(task, form);
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-match-stats-task]').forEach(function (button) {
            button.addEventListener('click', function () {
                window.SportsManagementMatchStats.submit(
                    button.dataset.matchStatsTask || '',
                    button.dataset.closeAfterSave === '1'
                );
            });
        });

        const cancelButton = document.querySelector('[data-match-stats-cancel]');

        if (cancelButton) {
            cancelButton.addEventListener('click', function () {
                window.SportsManagementMatchStats.closeParentDialog(<?php echo $refresh ? 'true' : 'false'; ?>);
            });
        }

        <?php if ($close === 1) : ?>
        window.SportsManagementMatchStats.closeParentDialog(<?php echo $refresh ? 'true' : 'false'; ?>);
        <?php endif; ?>
    });
</script>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" id="adminform" method="post"
      style="display:inline" name="adminform">
    <div class="fltrt">
        <button type="button" data-match-stats-task="matches.savestats">
            <?php echo Text::_('JAPPLY'); ?>
        </button>
        <button type="button" data-match-stats-task="matches.savestats" data-close-after-save="1">
            <?php echo Text::_('JSAVE'); ?>
        </button>
        <button id="cancel" type="button" data-match-stats-cancel>
            <?php echo Text::_('JCANCEL'); ?>
        </button>
    </div>
    <div class="configuration">

    </div>
    <div class="clear"></div>
    <?php
    $tabsOptions = [
        'active' => 'panel1',
    ];
    echo HTMLHelper::_('bootstrap.startTabSet', 'match-stats-tabs', $tabsOptions);
    echo HTMLHelper::_('bootstrap.addTab', 'match-stats-tabs', 'panel1', Text::_($this->teams->team1));
    echo $this->loadTemplate('home');
    echo HTMLHelper::_('bootstrap.endTab');
    echo HTMLHelper::_('bootstrap.addTab', 'match-stats-tabs', 'panel2', Text::_($this->teams->team2));
    echo $this->loadTemplate('away');
    echo HTMLHelper::_('bootstrap.endTab');
    echo HTMLHelper::_('bootstrap.endTabSet');
    ?>

    <input type="hidden" name="view" value="">
    <input type="hidden" name="close" id="close" value="0">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="match_id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="component" value="com_sportsmanagement">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<div style="clear: both"></div>
