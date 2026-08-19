<?php
/** SportsManagement tournament trees list wrapper. */
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
    <input type="hidden" name="project_id" value="<?php echo (int) $this->projectws->id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="view" value="treetos">
    <input type="hidden" name="task" value="treeto.display">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php echo $this->loadTemplate('footer'); ?>
