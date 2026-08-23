<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=players&layout=players_upload'); ?>" method="post" enctype="multipart/form-data" id="adminForm" name="adminForm">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title h5"><?php echo Text::_('JTOOLBAR_UPLOAD'); ?></h3>
            <div class="mb-3">
                <label class="form-label" for="fileToUpload">CSV</label>
                <input class="form-control" type="file" name="fileToUpload" id="fileToUpload" accept=".csv,text/csv" required>
                <div class="form-text">CSV, maximal 5 MB.</div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?php echo Text::_('JTOOLBAR_UPLOAD'); ?></button>
                <button type="reset" class="btn btn-secondary"><?php echo Text::_('JCLEAR'); ?></button>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="players.importupload">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
