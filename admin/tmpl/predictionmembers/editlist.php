<?php
/** Native Joomla 5/6 prediction member assignment layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$input = Factory::getApplication()->getInput();
$refresh = $input->getBool('refresh', false);
$close = $input->getInt('close', 0) === 1;
$available = HTMLHelper::_(
    'select.genericlist',
    $this->availableMembers,
    'members[]',
    'id="members" class="form-select" multiple size="15"',
    'value',
    'text'
);
$assigned = HTMLHelper::_(
    'select.genericlist',
    $this->assignedMembers,
    'prediction_members[]',
    'id="prediction_members" class="form-select" multiple size="15"',
    'value',
    'text'
);
?>
<script>
function movePredictionMembers(fromId, toId) {
    const from = document.getElementById(fromId);
    const to = document.getElementById(toId);

    if (!from || !to) {
        return;
    }

    Array.from(from.selectedOptions).forEach((option) => to.appendChild(option));
}

function submitPredictionMembers(form) {
    form.querySelectorAll('#prediction_members option').forEach((option) => { option.selected = true; });
    Joomla.submitform('predictionmembers.save_memberlist', form);
}

function closePredictionMemberModal() {
    if (<?php echo $refresh ? 'true' : 'false'; ?> && window.parent) {
        window.parent.location.reload();
    }

    if (window.parent && window.parent.Joomla && window.parent.Joomla.Modal) {
        const modal = window.parent.Joomla.Modal.getCurrent();
        if (modal) {
            modal.close();
            return;
        }
    }

    if (window.parent && window.parent !== window) {
        window.parent.postMessage({type: 'joomla:close-modal'}, '*');
    }
}

<?php if ($close) : ?>
document.addEventListener('DOMContentLoaded', closePredictionMemberModal);
<?php endif; ?>
</script>

<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>"
      method="post"
      name="adminForm"
      id="adminForm">
    <div class="d-flex justify-content-end gap-2 mb-3">
        <button type="button" class="btn btn-success" onclick="submitPredictionMembers(this.form)">
            <?php echo Text::_('JSAVE'); ?>
        </button>
        <button type="button" class="btn btn-secondary" onclick="closePredictionMemberModal()">
            <?php echo Text::_('JCANCEL'); ?>
        </button>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <fieldset class="border rounded p-3 h-100">
                <legend class="float-none w-auto px-2 fs-6">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_AVAIL_MEMBERS'); ?>
                </legend>
                <?php echo $available; ?>
            </fieldset>
        </div>

        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="movePredictionMembers('members', 'prediction_members')">&gt;&gt;</button>
            <button type="button" class="btn btn-outline-secondary" onclick="movePredictionMembers('prediction_members', 'members')">&lt;&lt;</button>
        </div>

        <div class="col-md-5">
            <fieldset class="border rounded p-3 h-100">
                <legend class="float-none w-auto px-2 fs-6">
                    <?php echo Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_TITLE',
                        '<i>' . htmlspecialchars($this->prediction_name, ENT_QUOTES, 'UTF-8') . '</i>'
                    ); ?>
                </legend>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_PROJ_MEMBERS'); ?></strong>
                <?php echo $assigned; ?>
            </fieldset>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="cid" value="<?php echo (int) $this->prediction_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
