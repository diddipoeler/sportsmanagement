<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.multiselect');

$search = (string) $this->state->get('filter.search');
$state = $this->state->get('filter.state');
$categoryId = (int) $this->state->get('filter.catid', 0);
$order = (string) $this->state->get('list.ordering', 'obj.quote');
$direction = (string) $this->state->get('list.direction', 'ASC');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=smquotes'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="filter_search"><?php echo Text::_('JSEARCH_FILTER'); ?></label>
                        <input class="form-control" type="search" name="filter_search" id="filter_search"
                               value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="filter_state"><?php echo Text::_('JSTATUS'); ?></label>
                        <select class="form-select" name="filter_state" id="filter_state">
                            <option value=""<?php echo $state === '' ? ' selected' : ''; ?>><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                            <option value="1"<?php echo (string) $state === '1' ? ' selected' : ''; ?>><?php echo Text::_('JPUBLISHED'); ?></option>
                            <option value="0"<?php echo (string) $state === '0' ? ' selected' : ''; ?>><?php echo Text::_('JUNPUBLISHED'); ?></option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="filter_catid"><?php echo Text::_('JCATEGORY'); ?></label>
                        <input class="form-control" type="number" min="0" name="filter_catid" id="filter_catid"
                               value="<?php echo $categoryId > 0 ? $categoryId : ''; ?>">
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                        <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=smquotes'); ?>"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                        <tr>
                            <th class="text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                            <th><?php echo Text::_('JAUTHOR'); ?></th>
                            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_DESCRIPTION'); ?></th>
                            <th><?php echo Text::_('JCATEGORY'); ?></th>
                            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'); ?></th>
                            <th><?php echo Text::_('JSTATUS'); ?></th>
                            <th><?php echo Text::_('JGLOBAL_FIELD_ID_LABEL'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$this->items) : ?>
                            <tr><td colspan="7" class="text-center py-4"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($this->items as $i => $item) : ?>
                                <tr>
                                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id); ?></td>
                                    <td>
                                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=smquote.edit&id=' . (int) $item->id); ?>">
                                            <?php echo htmlspecialchars((string) $item->author, ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars(trim(strip_tags((string) $item->quote)), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string) ($item->category_title ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php if (!empty($item->picture)) : ?>
                                            <img src="<?php echo htmlspecialchars(Uri::root() . ltrim((string) $item->picture, '/'), ENT_QUOTES, 'UTF-8'); ?>"
                                                 alt="" style="max-height:32px;max-width:64px">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo (int) $item->published === 1 ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo Text::_((int) $item->published === 1 ? 'JPUBLISHED' : 'JUNPUBLISHED'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo (int) $item->id; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($this->pagination) : ?>
            <div class="mt-3"><?php echo $this->pagination->getListFooter(); ?></div>
        <?php endif; ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars($order, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars($direction, ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
