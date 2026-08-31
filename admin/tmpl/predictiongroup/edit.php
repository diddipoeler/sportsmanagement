<?php
/** Native Joomla 5/6 prediction group edit layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>"
      method="post"
      name="adminForm"
      id="predictiongroup-form"
      class="form-validate">
    <div class="card">
        <div class="card-body">
            <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
                <?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
                    <?php echo $field->renderField(); ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
