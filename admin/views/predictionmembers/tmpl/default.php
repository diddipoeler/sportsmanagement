<?php
/** Prediction members list wrapper for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

sportsmanagementHelper::addTemplatePaths(['footer'], $this);
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
    <?php if ($this->filterForm) : ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('data'); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="prediction_id" value="<?php echo (int) $this->prediction_id; ?>">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php echo $this->loadTemplate('footer'); ?>
