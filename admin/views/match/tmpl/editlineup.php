<?php
/**
 * SportsManagement administrator match lineup editor for Joomla 5/6.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage match
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2026 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Keep jQuery available for lineup subtemplates which still depend on it, but load it through Joomla's Web Asset Manager.
$this->getDocument()->getWebAssetManager()->useScript('jquery');

$close = Factory::getApplication()->input->getInt('close', 0);
?>
<script>
window.SportsManagementLineup = window.SportsManagementLineup || {};

window.SportsManagementLineup.selectAllAssigned = function () {
    document.querySelectorAll('select.position-starters option, select.position-staff option').forEach(function (option) {
        option.selected = true;
    });
};

window.SportsManagementLineup.submit = function (task, closeAfterSave) {
    const form = document.getElementById('adminForm');

    if (!form) {
        return;
    }

    window.SportsManagementLineup.selectAllAssigned();

    if (closeAfterSave) {
        const closeField = document.getElementById('close');

        if (closeField) {
            closeField.value = '1';
        }
    }

    Joomla.submitform(task, form);
};

window.SportsManagementLineup.closeParentDialog = function () {
    if (window.parent === window) {
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
        // Fall through to the Joomla dialog cross-window message.
    }

    window.parent.postMessage({messageType: 'joomla:cancel'}, window.location.origin);
};

<?php if ($close === 1) : ?>
document.addEventListener('DOMContentLoaded', function () {
    window.SportsManagementLineup.closeParentDialog();
});
<?php endif; ?>
</script>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" id="adminForm" method="post"
      style="display:inline" name="adminform">
    <fieldset>
        <div class="fltrt">
            <button type="button"
                    onclick="window.SportsManagementLineup.submit('matches.saveroster', false);">
                <?php echo Text::_('JAPPLY'); ?>
            </button>
            <button type="button"
                    onclick="window.SportsManagementLineup.submit('matches.saveroster', true);">
                <?php echo Text::_('JSAVE'); ?>
            </button>
        </div>
        <div class="configuration">
            <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELU_TITLE', $this->teamname); ?>
        </div>
    </fieldset>
    <div class="clear"></div>
    <div id="lineup">
        <?php
        $tabsOptions = ['active' => 'panel1'];

        echo HTMLHelper::_('bootstrap.startTabSet', 'match-lineup-tabs', $tabsOptions);
        echo HTMLHelper::_('bootstrap.addTab', 'match-lineup-tabs', 'panel1', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS'));
        echo $this->loadTemplate('players');
        echo HTMLHelper::_('bootstrap.endTab');

        if ($this->projectws->sport_type_name !== 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD') {
            echo HTMLHelper::_('bootstrap.addTab', 'match-lineup-tabs', 'panel2', Text::_('COM_SPORTSMANAGEMENT_TABS_SUBST'));
            echo $this->loadTemplate('substitutions');
            echo HTMLHelper::_('bootstrap.endTab');

            echo HTMLHelper::_('bootstrap.addTab', 'match-lineup-tabs', 'panel3', Text::_('COM_SPORTSMANAGEMENT_TABS_STAFF'));
            echo $this->loadTemplate('staff');
            echo HTMLHelper::_('bootstrap.endTab');
        }

        echo HTMLHelper::_('bootstrap.addTab', 'match-lineup-tabs', 'panel4', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS'));
        echo $this->loadTemplate('players_trikot_numbers');
        echo HTMLHelper::_('bootstrap.endTab');
        echo HTMLHelper::_('bootstrap.endTabSet');
        ?>
        <input type="hidden" name="task" value="">
        <input type="hidden" name="view" value="">
        <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
        <input type="hidden" name="close" id="close" value="0">
        <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
        <input type="hidden" name="changes_check" value="0" id="changes_check">
        <input type="hidden" name="team" value="<?php echo (int) $this->tid; ?>" id="team">
        <input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount">
        <?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </div>
</form>
