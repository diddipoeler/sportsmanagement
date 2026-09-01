<?php
/** Joomla 5/6 SportsManagement installation helper: sports type selection. */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="alert alert-info" role="note">
    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE'); ?></strong>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_0'); ?>
</div>

<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=installhelper&step=1', false); ?>"
      method="post" id="adminForm" name="adminForm">
    <div class="card">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-8 col-lg-6">
                    <label for="filter_sports_type" class="form-label">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'); ?>
                    </label>
                    <select name="filter_sports_type" id="filter_sports_type" class="form-select" required>
                        <?php foreach ($this->sportstypeOptions as $option) : ?>
                            <option value="<?php echo $this->escape((string) $option->value); ?>"
                                <?php echo (string) $option->value === $this->selectedSportstype ? ' selected' : ''; ?>>
                                <?php echo $this->escape((string) $option->text); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-check" aria-hidden="true"></span>
                        <?php echo Text::_('JAPPLY'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="installhelper.savesportstype">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
