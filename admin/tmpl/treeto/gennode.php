<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=treeto&layout=gennode&id=' . (int) $this->treeto->id . '&pid=' . (int) $this->project_id); ?>"
      method="post" name="adminForm" id="treeto-generate-form" class="form-validate">
    <?php echo $this->loadTemplate('data'); ?>
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="id" value="<?php echo (int) $this->treeto->id; ?>">
    <input type="hidden" name="task" value="treeto.generatenode">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
