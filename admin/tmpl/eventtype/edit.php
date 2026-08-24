<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$fieldsets = $this->form->getFieldsets();
$tabId = static function (string $name): string {
    return 'eventtype-' . (preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'details');
};
$firstFieldset = reset($fieldsets);
$activeTab = $firstFieldset ? $tabId((string) $firstFieldset->name) : 'eventtype-details';
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'eventtypeTabs', [
        'active' => $activeTab,
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php
        $name = (string) $fieldset->name;
        $currentTab = $tabId($name);
        $label = Text::_((string) ($fieldset->label ?: $name));
        echo HTMLHelper::_('uitab.addTab', 'eventtypeTabs', $currentTab, $label);
        ?>
        <div class="options-form mb-4">
            <?php if (!empty($fieldset->description)) : ?>
                <div class="alert alert-info"><?php echo Text::_($fieldset->description); ?></div>
            <?php endif; ?>
            <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                <?php echo $field->renderField(); ?>
            <?php endforeach; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
