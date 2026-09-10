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

// The shared substitution AJAX script still uses jQuery. Load it only through Joomla's Web Asset Manager.
$this->getDocument()->getWebAssetManager()->useScript('jquery');

$close = Factory::getApplication()->input->getInt('close', 0);
?>
<script>
window.SportsManagementLineup = window.SportsManagementLineup || {};

window.SportsManagementLineup.markChanged = function () {
    const changesField = document.getElementById('changes_check');

    if (changesField) {
        changesField.value = '1';
    }
};

window.SportsManagementLineup.moveSelected = function (sourceId, destinationId) {
    const source = document.getElementById(sourceId);
    const destination = document.getElementById(destinationId);

    if (!source || !destination) {
        return;
    }

    Array.from(source.selectedOptions).forEach(function (option) {
        destination.appendChild(option);
    });

    window.SportsManagementLineup.markChanged();
};

window.SportsManagementLineup.moveUp = function (selectId) {
    const select = document.getElementById(selectId);

    if (!select) {
        return;
    }

    Array.from(select.selectedOptions).forEach(function (option) {
        const previous = option.previousElementSibling;

        if (previous && !previous.selected) {
            select.insertBefore(option, previous);
        }
    });

    window.SportsManagementLineup.markChanged();
};

window.SportsManagementLineup.moveDown = function (selectId) {
    const select = document.getElementById(selectId);

    if (!select) {
        return;
    }

    Array.from(select.selectedOptions).reverse().forEach(function (option) {
        const next = option.nextElementSibling;

        if (next && !next.selected) {
            select.insertBefore(next, option);
        }
    });

    window.SportsManagementLineup.markChanged();
};

window.SportsManagementLineup.bindControls = function () {
    const form = document.getElementById('adminForm');

    if (!form) {
        return;
    }

    form.addEventListener('click', function (event) {
        const button = event.target.closest('[data-lineup-action], [data-lineup-submit]');

        if (!button || !form.contains(button)) {
            return;
        }

        if (button.dataset.lineupSubmit) {
            window.SportsManagementLineup.submit(
                button.dataset.lineupSubmit,
                button.dataset.lineupClose === '1'
            );
            return;
        }

        const action = button.dataset.lineupAction;

        if (action === 'move-selected') {
            window.SportsManagementLineup.moveSelected(
                button.dataset.sourceSelect || '',
                button.dataset.destinationSelect || ''
            );
        } else if (action === 'move-up') {
            window.SportsManagementLineup.moveUp(button.dataset.targetSelect || '');
        } else if (action === 'move-down') {
            window.SportsManagementLineup.moveDown(button.dataset.targetSelect || '');
        }
    });
};

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

document.addEventListener('DOMContentLoaded', function () {
    window.SportsManagementLineup.bindControls();

<?php if ($close === 1) : ?>
    window.SportsManagementLineup.closeParentDialog();
<?php endif; ?>
});
</script>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" id="adminForm" method="post"
      style="display:inline" name="adminform">
    <fieldset>
        <div class="fltrt">
            <button type="button" data-lineup-submit="matches.saveroster" data-lineup-close="0">
                <?php echo Text::_('JAPPLY'); ?>
            </button>
            <button type="button" data-lineup-submit="matches.saveroster" data-lineup-close="1">
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
