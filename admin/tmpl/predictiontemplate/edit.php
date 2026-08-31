<?php
/** Native Joomla 5/6 prediction template settings editor. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$fieldsets = $this->form->getFieldsets();
$templateName = strtoupper($this->form->getName());
$itemId = (int) ($this->item->id ?? 0);
$action = Route::_('index.php?option=com_sportsmanagement&view=predictiontemplate&layout=edit&id=' . $itemId);
$tabId = static function (string $name): string {
    return 'predictiontemplate-' . (preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'options');
};
$firstFieldset = reset($fieldsets);
$activeTab = $firstFieldset ? $tabId((string) $firstFieldset->name) : 'predictiontemplate-options';
?>
<form
    action="<?php echo $action; ?>"
    method="post"
    name="adminForm"
    id="predictiontemplate-form"
    class="form-validate"
>
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-2">
                <?php echo Text::sprintf(
                    'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_LEGEND',
                    '<strong>' . $this->escape(Text::_('COM_SPORTSMANAGEMENT_FES_' . $templateName . '_NAME')) . '</strong>',
                    '<strong>' . $this->escape((string) ($this->predictionGame->name ?? '')) . '</strong>'
                ); ?>
            </h2>
            <p class="text-muted mb-0">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_FES_' . $templateName . '_DESCR'); ?>
            </p>
        </div>
    </div>

    <?php echo HTMLHelper::_('uitab.startTabSet', 'predictionTemplateTabs', [
        'active' => $activeTab,
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php
        $name = (string) $fieldset->name;
        $label = Text::_((string) ($fieldset->label ?: $name));
        echo HTMLHelper::_('uitab.addTab', 'predictionTemplateTabs', $tabId($name), $label);
        ?>
        <div class="options-form mb-4">
            <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                <?php echo $field->renderField(); ?>
            <?php endforeach; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="jform[id]" value="<?php echo $itemId; ?>">
    <input type="hidden" name="id" value="<?php echo $itemId; ?>">
    <input type="hidden" name="predid" value="<?php echo (int) $this->prediction_id; ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
