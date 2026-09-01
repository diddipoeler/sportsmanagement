<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=treetomatchs&layout=default&nid=' . $this->node_id . '&tid=' . $this->tree_id . '&pid=' . $this->project_id); ?>" method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="nid" value="<?php echo $this->node_id; ?>">
    <input type="hidden" name="tid" value="<?php echo $this->tree_id; ?>">
    <input type="hidden" name="pid" value="<?php echo $this->project_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
