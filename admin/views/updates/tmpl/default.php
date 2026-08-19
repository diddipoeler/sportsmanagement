<?php
/** Joomla 5/6 administrator updates layout. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = ['footer'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<form action="<?php echo htmlspecialchars($this->request_url, ENT_QUOTES, 'UTF-8'); ?>"
      method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>
    <input type="hidden" name="view" value="updates">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="filter_order" value="<?php echo htmlspecialchars((string) $this->sortColumn, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo htmlspecialchars((string) $this->sortDirection, ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php echo $this->loadTemplate('footer'); ?>
