<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$listOrder = (string) $this->state->get('list.ordering', 'obj.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$search = (string) $this->state->get('filter.search', '');
$published = (string) $this->state->get('filter.state', '');
$publishedOptions = [
    HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_PUBLISHED')),
    HTMLHelper::_('select.option', '1', Text::_('JPUBLISHED')),
    HTMLHelper::_('select.option', '0', Text::_('JUNPUBLISHED')),
    HTMLHelper::_('select.option', '2', Text::_('JARCHIVED')),
    HTMLHelper::_('select.option', '-2', Text::_('JTRASHED')),
];
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=statistics'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label" for="filter_search"><?php echo Text::_('JSEARCH_FILTER'); ?></label>
            <input type="text" name="filter_search" id="filter_search" class="form-control"
                   value="<?php echo $this->escape($search); ?>"
                   placeholder="<?php echo $this->escape(Text::_('JSEARCH_FILTER')); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="filter_sports_type"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_SPORTSTYPE'); ?></label>
            <?php echo $this->lists['sportstypes']; ?>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="filter_published"><?php echo Text::_('JSTATUS'); ?></label>
            <?php echo HTMLHelper::_('select.genericList', $publishedOptions, 'filter_published', 'class="form-select" onchange="this.form.submit()"', 'value', 'text', $published); ?>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_search').value=''; document.getElementById('filter_sports_type').value='0'; document.getElementById('filter_published').value=''; this.form.submit();">
                <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
    </div>

    <?php echo $this->loadTemplate('data'); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
