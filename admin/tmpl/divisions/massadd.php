<?php
/**
 * Native Joomla 5/6 mass-add layout for project divisions.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$projectId = (int) ($this->project->id ?? $this->projectId);
$projectName = (string) ($this->project->name ?? '');
?>
<?php if ($this->close === 1) : ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('cancel')?.click();
});
</script>
<?php endif; ?>

<div id="alt_massadd_enter" class="container-fluid">
    <div class="card">
        <div class="card-header">
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_MASSADD_LEGEND',
                '<i>' . htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8') . '</i>'
            ); ?>
        </div>
        <div class="card-body">
            <form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>"
                  id="component-form" method="post" name="adminform">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="add_division_count">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_MASSADD_COUNT'); ?>
                        </label>
                        <input type="number" min="0" name="add_division_count" id="add_division_count"
                               value="0" class="form-control">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button id="save" class="btn btn-success" type="button"
                                onclick="Joomla.submitform('divisions.massadd', this.form)">
                            <?php echo Text::_('JSAVE'); ?>
                        </button>
                        <button id="cancel" class="btn btn-secondary" type="button"
                                onclick="Joomla.submitform('divisions.cancel', this.form)">
                            <?php echo Text::_('JCANCEL'); ?>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
                <input type="hidden" name="pid" value="<?php echo $projectId; ?>">
                <input type="hidden" name="task" value="">
                <input type="hidden" name="component" value="com_sportsmanagement">
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
    </div>
</div>
