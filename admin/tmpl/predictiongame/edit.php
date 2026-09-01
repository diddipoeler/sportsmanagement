<?php
/** Native Joomla 5/6 prediction game edit layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$itemId = (int) ($this->item->id ?? 0);
$fieldsets = $this->form->getFieldsets();
$action = Route::_('index.php?option=com_sportsmanagement&view=predictiongame&layout=edit&id=' . $itemId);
$tabId = static function (string $name): string {
    return 'predictiongame-' . (preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'details');
};
$firstFieldset = reset($fieldsets);
$activeTab = $firstFieldset ? $tabId((string) $firstFieldset->name) : 'predictiongame-details';
?>
<form
    action="<?php echo $action; ?>"
    method="post"
    name="adminForm"
    id="predictiongame-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'predictionGameTabs', [
        'active' => $activeTab,
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php
        $name = (string) $fieldset->name;
        $label = Text::_((string) ($fieldset->label ?: $name));
        echo HTMLHelper::_('uitab.addTab', 'predictionGameTabs', $tabId($name), $label);
        ?>
        <div class="options-form mb-4">
            <?php if (!empty($fieldset->description)) : ?>
                <p class="text-muted"><?php echo Text::_((string) $fieldset->description); ?></p>
            <?php endif; ?>
            <?php echo $this->form->renderFieldset($name); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
