<?php
/** Native Joomla 5/6 prediction member edit layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>"
      method="post"
      name="adminForm"
      id="predictionmember-form"
      class="form-validate">
    <div class="card">
        <div class="card-body">
            <?php foreach ($this->form->getFieldset('details') as $field) : ?>
                <?php echo $field->renderField(); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
