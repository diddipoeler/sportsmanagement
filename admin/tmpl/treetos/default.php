<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('multiselect');

$listOrder = (string) $this->state->get('list.ordering', 'tt.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$action = 'index.php?option=com_sportsmanagement&view=treetos&pid=' . (int) $this->project_id;
?>
<form
    action="<?php echo Route::_($action); ?>"
    method="post"
    name="adminForm"
    id="adminForm"
>
    <?php echo $this->loadTemplate('data'); ?>

    <?php if ($this->pagination) : ?>
        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
