<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$renderFields = static function (array $fields): void {
    foreach ($fields as $field) {
        if (strtolower((string) $field->type) === 'hidden') {
            echo $field->input;
            continue;
        }
        ?>
        <div class="control-group mb-3">
            <div class="control-label"><?php echo $field->label; ?></div>
            <div class="controls"><?php echo $field->input; ?></div>
        </div>
        <?php
    }
};

$sportstypeId = (int) ($this->item->id ?? 0);
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=sportstype&layout=edit&id=' . $sportstypeId); ?>"
    method="post"
    name="adminForm"
    id="sportstype-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'sportstypeTabs', [
        'active' => 'sportstype-details',
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sportstypeTabs', 'sportstype-details', Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('details')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'sportstypeTabs', 'sportstype-picture', Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('picture')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
