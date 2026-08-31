<?php
/** Native Joomla 5/6 administrator extended XML/PHP source editor layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$editorField = $this->form->getField('source');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=smextxmleditor&layout=default'); ?>"
      method="post"
      name="adminForm"
      id="source-form"
      class="form-validate">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <?php echo $this->form->getLabel('source'); ?>
                    <div class="editor-border">
                        <?php echo $this->form->getInput('source'); ?>
                    </div>
                </div>
                <?php echo $this->form->getInput('filename'); ?>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
Joomla.submitbutton = function (task) {
    const form = document.getElementById('source-form');

    if (task === 'smextxmleditor.cancel' || document.formvalidator.isValid(form)) {
        <?php if ($editorField && method_exists($editorField, 'save')) : ?>
        <?php echo $editorField->save(); ?>
        <?php endif; ?>
        Joomla.submitform(task, form);
        return;
    }

    alert(<?php echo json_encode(Text::_('JGLOBAL_VALIDATION_FORM_FAILED'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
};
</script>
