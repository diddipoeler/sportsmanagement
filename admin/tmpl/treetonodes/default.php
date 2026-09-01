<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=treetonodes&tid=' . (int) $this->tree_id . '&pid=' . (int) $this->project_id); ?>"
      method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>

    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="tid" value="<?php echo (int) $this->tree_id; ?>">
    <input type="hidden" name="tree_i" value="<?php echo (int) $this->treetows->tree_i; ?>">
    <input type="hidden" name="treeto_id" value="<?php echo (int) $this->treetows->id; ?>">
    <input type="hidden" name="global_fake" value="<?php echo (int) $this->treetows->global_fake; ?>">
    <input type="hidden" name="global_known" value="<?php echo (int) $this->treetows->global_known; ?>">
    <input type="hidden" name="global_matchday" value="<?php echo (int) $this->treetows->global_matchday; ?>">
    <input type="hidden" name="global_bestof" value="<?php echo (int) $this->treetows->global_bestof; ?>">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
