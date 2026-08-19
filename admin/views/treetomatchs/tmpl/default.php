<?php
/** Tournament-tree assigned matches list for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

sportsmanagementHelper::addTemplatePaths(['footer'], $this);
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>

    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="nid" value="<?php echo (int) $this->node_id; ?>">
    <input type="hidden" name="tid" value="<?php echo (int) $this->tree_id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php echo $this->loadTemplate('footer'); ?>
