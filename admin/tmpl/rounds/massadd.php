<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$projectName = $this->project ? (string) $this->project->name : '';
$projectType = $this->project ? (string) $this->project->project_type : '';
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=rounds&layout=massadd&pid=' . $this->project_id); ?>"
    id="adminForm"
    name="adminForm"
    method="post"
>
    <div class="card">
        <div class="card-body">
            <h3 class="h5">
                <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_LEGEND', '<strong>' . $this->escape($projectName) . '</strong>'); ?>
            </h3>
            <div class="row g-3 align-items-end mt-1">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="add_round_count"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_COUNT'); ?></label>
                    <input
                        class="form-control"
                        type="number"
                        min="1"
                        name="add_round_count"
                        id="add_round_count"
                        value="1"
                        required
                    >
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="project_type" value="<?php echo $this->escape($projectType); ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
