<?php
/** SportsManagement project templates list wrapper. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<form action="<?php echo $this->escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
    <?php
    echo $this->loadTemplate('joomla_version');
    echo $this->loadTemplate('data');
    ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->projectws->id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php echo $this->loadTemplate('footer'); ?>
