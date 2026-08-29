<?php
/** Native Joomla 5/6 image-package import list. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
?>
<form action="<?php echo htmlspecialchars((string) $this->request_url, ENT_QUOTES, 'UTF-8'); ?>" method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars((string) $this->sortColumn, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars((string) $this->sortDirection, ENT_QUOTES, 'UTF-8'); ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php echo $this->loadTemplate('footer'); ?>
