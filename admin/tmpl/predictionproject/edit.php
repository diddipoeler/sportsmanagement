<?php
/** Native Joomla 5/6 prediction project edit layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$fieldsets = $this->form->getFieldsets();
$projectId = (int) ($this->item->project_id ?? 0);
$itemId = (int) ($this->item->id ?? 0);
$action = Route::_(
    'index.php?option=com_sportsmanagement&view=predictionproject&layout=edit&id=' . $itemId
    . '&project_id=' . $projectId
);
$tabId = static function (string $name): string {
    return 'predictionproject-' . (preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'details');
};
$firstFieldset = reset($fieldsets);
$activeTab = $firstFieldset ? $tabId((string) $firstFieldset->name) : 'predictionproject-details';
?>
<form
    action="<?php echo $action; ?>"
    method="post"
    name="adminForm"
    id="predictionproject-form"
    class="form-validate"
>
    <div class="d-flex justify-content-end mb-3">
        <button
            type="button"
            class="btn btn-success"
            onclick="Joomla.submitform('predictionproject.store', document.getElementById('predictionproject-form'))"
        >
            <span class="icon-save" aria-hidden="true"></span>
            <?php echo Text::_('JSAVE'); ?>
        </button>
    </div>

    <?php echo HTMLHelper::_('uitab.startTabSet', 'predictionProjectTabs', [
        'active' => $activeTab,
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php
        $name = (string) $fieldset->name;
        $label = Text::_((string) ($fieldset->label ?: $name));
        echo HTMLHelper::_('uitab.addTab', 'predictionProjectTabs', $tabId($name), $label);
        ?>
        <div class="options-form mb-4">
            <?php echo $this->form->renderFieldset($name); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="psapply" value="1">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
