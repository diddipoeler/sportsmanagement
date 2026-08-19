<?php
/** Prediction member assignment modal. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$input = $this->app->getInput();
$refresh = $input->getBool('refresh', false);
$close = $input->getInt('close', 0) === 1;
?>
<script>
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
      id="adminForm"
      class="form-validate">
    <fieldset>
        <div class="d-flex justify-content-end gap-2 mb-3">
            <button type="button" class="btn btn-success"
                    onclick="Joomla.submitform('predictionmembers.save_memberlist', this.form)">
                <?php echo Text::_('JSAVE'); ?>
            </button>
            <button id="cancel" type="button" class="btn btn-secondary" onclick="closePredictionMemberModal();">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </fieldset>

    <div class="row g-3">
        <div class="col-md-5">
            <fieldset class="border rounded p-3 h-100">
                <legend class="float-none w-auto px-2 fs-6">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_AVAIL_MEMBERS'); ?>
                </legend>
                <?php echo $this->lists['members']; ?>
            </fieldset>
        </div>

        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary"
                    onclick="move_list_items('members','prediction_members');document.querySelectorAll('#prediction_members option').forEach(option => option.selected = true);">
                &gt;&gt;
            </button>
            <button type="button" class="btn btn-outline-secondary"
                    onclick="move_list_items('prediction_members','members');document.querySelectorAll('#prediction_members option').forEach(option => option.selected = true);">
                &lt;&lt;
            </button>
        </div>

        <div class="col-md-5">
            <fieldset class="border rounded p-3 h-100">
                <legend class="float-none w-auto px-2 fs-6">
                    <?php echo Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_TITLE',
                        '<i>' . htmlspecialchars((string) $this->prediction_name) . '</i>'
                    ); ?>
                </legend>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONMEMBERS_ASSIGN_PROJ_MEMBERS'); ?></strong>
                <?php echo $this->lists['prediction_members']; ?>
            </fieldset>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="option" value="com_sportsmanagement">
    <input type="hidden" name="cid" value="<?php echo (int) $this->prediction_id; ?>">
    <input type="hidden" name="component" value="com_sportsmanagement">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
