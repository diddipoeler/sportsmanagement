<?php
/**
 * SportsManagement Inline Hockey administrator template.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Escape legacy Inline Hockey form values without scalar conversion warnings.
 *
 * Malformed module options, remote match links and invalid UTF-8 must never
 * be emitted as raw attributes in the administrator import form.
 *
 * @param mixed $value User or project-supplied form value.
 * @return string HTML-safe scalar text or empty text for invalid input.
 */
$escape = static fn (mixed $value): string => htmlspecialchars(
    is_string($value) || is_numeric($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

// Always post the import to the component itself; do not trust a legacy
// request_url to redirect an import form or leak its CSRF token elsewhere.
$formAction = 'index.php?option=com_sportsmanagement&view=jsminlinehockey';
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
              action="<?php echo $escape($formAction); ?>"
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
                           value="<?php echo $escape($this->matchlink ?? ''); ?>"
                           maxlength="500">
                </div>

                <div class="mb-3">
                    <label class="me-3"><input type="radio" name="check" value="clubs" checked> Vereine</label>
                    <label class="me-3"><input type="radio" name="check" value="teams"> Mannschaften</label>
                    <label><input type="radio" name="check" value="players"> Spieler</label>
                </div>

                <div class="mb-3">
                    <input class="form-control" id="import_package" name="import_package" type="file" accept=".xls,.ics,.zip">
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
