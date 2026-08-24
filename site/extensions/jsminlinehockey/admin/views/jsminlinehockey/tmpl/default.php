<?php
/** SportsManagement Inline Hockey administrator template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div id="editcell">
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif; ?>

        <form enctype="multipart/form-data"
              action="<?php echo $escape($this->request_url); ?>"
              method="post"
              id="adminForm"
              name="adminForm">
            <fieldset class="text-center">
                <div class="mb-3">
                    <label for="matchlink" class="form-label">Match-Link</label>
                    <input type="text"
                           class="form-control"
                           id="matchlink"
                           name="matchlink"
                           value="<?php echo $escape($this->matchlink); ?>"
                           maxlength="500">
                </div>

                <div class="mb-3">
                    <label class="me-3"><input type="radio" name="check" value="clubs" checked> Vereine</label>
                    <label class="me-3"><input type="radio" name="check" value="teams"> Mannschaften</label>
                    <label><input type="radio" name="check" value="players"> Spieler</label>
                </div>

                <div class="mb-3">
                    <input class="form-control" id="import_package" name="import_package" type="file">
                </div>

                <button class="btn btn-primary" type="submit">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_UPLOAD_BUTTON'); ?>
                </button>
            </fieldset>

            <input type="hidden" name="sent" value="1">
            <input type="hidden" name="projectid" value="<?php echo (int) $this->projectid; ?>">
            <input type="hidden" name="task" value="jsminlinehockey.save">
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
