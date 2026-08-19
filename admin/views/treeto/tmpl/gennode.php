<?php
/** SportsManagement tournament-tree node generation form. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<form method="post" name="adminForm" id="adminForm">
    <?php echo $this->loadTemplate('data'); ?>
    <input type="hidden" name="pid" value="<?php echo (int) $this->projectws->id; ?>">
    <input type="hidden" name="id" value="<?php echo (int) $this->treeto->id; ?>">
    <input type="hidden" name="task" value="treeto.generatenode">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php echo $this->loadTemplate('footer'); ?>
