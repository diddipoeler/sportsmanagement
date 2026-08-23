<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = (string) $this->state->get('list.ordering', 'pl.lastname');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=players'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if ($this->filterForm) : ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('data'); ?>

    <?php if ($this->pagination) : ?>
        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
