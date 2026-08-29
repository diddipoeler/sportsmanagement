<?php
/** Native Joomla 5/6 image-package import table. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<div class="table-responsive" id="editcell">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_IMAGES_IMPORT'); ?></legend>

    <div class="row g-2 mb-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label" for="filter_search"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
            <div class="input-group">
                <input type="search" name="filter_search" id="filter_search" class="form-control"
                       value="<?php echo htmlspecialchars((string) $this->state->get('filter.search'), ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" />
                <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <button class="btn btn-outline-secondary" type="button"
                        onclick="document.getElementById('filter_search').value='';this.form.submit();">
                    <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="filter_image_folder"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_FOLDER'); ?></label>
            <?php echo $this->lists['folders']; ?>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="filter_published"><?php echo Text::_('JSTATUS'); ?></label>
            <select name="filter_published" id="filter_published" class="form-select" onchange="this.form.submit();">
                <option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                <?php echo HTMLHelper::_('select.options', HTMLHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true); ?>
            </select>
        </div>
    </div>

    <table class="table table-striped align-middle">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_IMPORT_IMAGE', 'name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_IMPORT_PATH', 'folder', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_IMPORT_DIRECTORY', 'directory', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_IMPORT_FILE', 'file', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo Text::_('JSTATUS'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $index => $item) :
            $id = (int) $item->id;
            $installed = (int) ($item->published ?? 0) === 1;
            ?>
            <tr>
                <td><?php echo $this->pagination->getRowOffset($index); ?></td>
                <td><?php echo HTMLHelper::_('grid.id', $index, $id); ?></td>
                <td>
                    <?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?>
                    <input type="hidden" name="picture[<?php echo $id; ?>]" value="<?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?>" />
                </td>
                <td>
                    <?php echo htmlspecialchars((string) $item->folder, ENT_QUOTES, 'UTF-8'); ?>
                    <input type="hidden" name="folder[<?php echo $id; ?>]" value="<?php echo htmlspecialchars((string) $item->folder, ENT_QUOTES, 'UTF-8'); ?>" />
                </td>
                <td>
                    <?php echo htmlspecialchars((string) $item->directory, ENT_QUOTES, 'UTF-8'); ?>
                    <input type="hidden" name="directory[<?php echo $id; ?>]" value="<?php echo htmlspecialchars((string) $item->directory, ENT_QUOTES, 'UTF-8'); ?>" />
                </td>
                <td>
                    <?php echo htmlspecialchars((string) $item->file, ENT_QUOTES, 'UTF-8'); ?>
                    <input type="hidden" name="file[<?php echo $id; ?>]" value="<?php echo htmlspecialchars((string) $item->file, ENT_QUOTES, 'UTF-8'); ?>" />
                </td>
                <td>
                    <span class="badge <?php echo $installed ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $installed ? Text::_('JYES') : Text::_('JNO'); ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
        </tr>
        </tfoot>
    </table>
</div>
