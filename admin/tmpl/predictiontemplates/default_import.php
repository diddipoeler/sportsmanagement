<?php
/** Native Joomla 5/6 inherited prediction-template override controls. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$options = [
    HTMLHelper::_('select.option', '', '- ' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TMPL_FILE') . ' -'),
];

foreach ($this->masterTemplates as $template) {
    $label = Text::_((string) ($template->text ?? $template->template ?? ''));
    $file = (string) ($template->template ?? '');

    if ($file !== '') {
        $label .= ' (' . $file . ')';
    }

    $options[] = HTMLHelper::_('select.option', (int) $template->value, $label);
}
?>
<div class="card mb-4" id="predictiontemplate-inheritance">
    <div class="card-body">
        <h2 class="h5 mb-2">
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_INHERITS_SETTINGS',
                $this->escape((string) ($this->masterPredictionGame->name ?? ''))
            ); ?>
        </h2>
        <p class="text-muted">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_OVERRIDES_SETTINGS'); ?>
        </p>

        <?php if (count($options) > 1) : ?>
            <form
                action="<?php echo Route::_('index.php?option=com_sportsmanagement&task=predictiontemplates.createOverride'); ?>"
                method="post"
                class="row g-2 align-items-end"
                id="predictiontemplate-override-form"
            >
                <div class="col-12 col-lg-8">
                    <label class="form-label" for="predictiontemplate-master-template">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TMPL_FILE'); ?>
                    </label>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $options,
                        'templateid',
                        'id="predictiontemplate-master-template" class="form-select" required',
                        'value',
                        'text'
                    ); ?>
                </div>
                <div class="col-12 col-lg-auto">
                    <button class="btn btn-primary" type="submit">
                        <span class="icon-plus" aria-hidden="true"></span>
                        <?php echo Text::_('JTOOLBAR_NEW'); ?>
                    </button>
                </div>
                <input type="hidden" name="prediction_id" value="<?php echo (int) $this->prediction_id; ?>">
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        <?php else : ?>
            <div class="alert alert-info mb-0">
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
