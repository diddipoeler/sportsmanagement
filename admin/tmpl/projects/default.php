<?php
/**
 * Native Joomla 5/6 administrator projects list layout.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=projects'); ?>" method="post" id="adminForm" name="adminForm">
    <?php if ($this->filterForm) : ?>
        <?php echo HTMLHelper::_('searchtools.default', ['view' => $this]); ?>
    <?php endif; ?>

    <?php echo $this->loadTemplate('data'); ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars($this->sortColumn, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars($this->sortDirection, ENT_QUOTES, 'UTF-8'); ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
