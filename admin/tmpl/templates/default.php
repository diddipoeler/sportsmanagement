<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$listOrder = (string) $this->state->get('list.ordering', 'tmpl.template');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=templates&pid=' . (int) $this->project_id); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="filter_search" id="filter_search" class="form-control" value="<?php echo $this->escape((string) $this->state->get('filter.search', '')); ?>" placeholder="<?php echo $this->escape(Text::_('JSEARCH_FILTER')); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
        </div>
        <?php if (!empty($this->projectws->master_template) && !empty($this->lists['mastertemplates'])) : ?>
            <div class="col-md-5"><?php echo $this->lists['mastertemplates']; ?></div>
        <?php endif; ?>
    </div>
    <?php echo $this->loadTemplate('data'); ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
