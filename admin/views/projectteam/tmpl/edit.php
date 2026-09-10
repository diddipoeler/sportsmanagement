<?php
/** Joomla 5/6 administrator project-team editor compatibility template. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$templatesToLoad = ['footer', 'fieldsets'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$fieldsets = $this->form->getFieldsets();
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$tabId = static fn (string $name): string => 'projectteam-' . (preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'fieldset');

$assets = $this->getDocument()->getWebAssetManager();
$assets->useScript('form.validate');
$assets->registerAndUseScript(
    'com_sportsmanagement.admin.projectteam',
    'administrator/components/com_sportsmanagement/assets/js/projectteam.js',
    ['version' => 'auto'],
    ['defer' => true],
    ['core', 'form.validate']
);

$helpServer = defined('COM_SPORTSMANAGEMENT_HELP_SERVER') ? (string) COM_SPORTSMANAGEMENT_HELP_SERVER : '';
$viewName = isset($this->jinput) ? $this->jinput->getCmd('view', 'projectteam') : 'projectteam';
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=' . $this->view . '&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post"
    id="projectteam-form"
    name="adminForm"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'projectTeamTabs', [
        'active' => 'projectteam-details',
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'projectTeamTabs', 'projectteam-details', Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS')); ?>
    <div class="options-form mb-4">
        <?php foreach ($this->form->getFieldset('details') as $field) : ?>
            <?php if (strtolower((string) $field->type) === 'hidden') : ?>
                <?php echo $field->input; ?>
                <?php continue; ?>
            <?php endif; ?>

            <div class="mb-3">
                <div class="form-label"><?php echo $field->label; ?></div>
                <div>
                    <?php echo $field->input; ?>

                    <?php if (in_array($field->name, ['jform[country]', 'jform[address_country]'], true)) : ?>
                        <?php echo JSMCountries::getCountryFlag($field->value); ?>
                    <?php endif; ?>

                    <?php
                    $fieldKey = str_replace(['jform[', ']'], '', (string) $field->name);
                    $showHelp = $helpServer !== '' && !in_array($fieldKey, ['id', 'project_id', 'team_id'], true);
                    ?>
                    <?php if ($showHelp) : ?>
                        <?php
                        $helpUrl = $helpServer
                            . 'SM-Backend-Felder:'
                            . rawurlencode($viewName)
                            . '-'
                            . rawurlencode($this->form->getName())
                            . '-'
                            . rawurlencode($fieldKey);
                        ?>
                        <a
                            class="ms-2"
                            href="<?php echo $escape($helpUrl); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            title="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_HELP_LINK')); ?>"
                        >
                            <?php echo HTMLHelper::_(
                                'image',
                                'media/com_sportsmanagement/jl_images/help.png',
                                Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'),
                                ['class' => 'icon-16']
                            ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php if ($fieldset->name === 'details') : ?>
            <?php continue; ?>
        <?php endif; ?>

        <?php
        $label = Text::_((string) ($fieldset->label ?: $fieldset->name));
        echo HTMLHelper::_('uitab.addTab', 'projectTeamTabs', $tabId((string) $fieldset->name), $label);
        ?>
        <div class="options-form mb-4">
            <?php if (!empty($fieldset->description)) : ?>
                <p class="text-muted"><?php echo Text::_((string) $fieldset->description); ?></p>
            <?php endif; ?>
            <?php
            $this->fieldset = $fieldset->name;
            echo $this->loadTemplate('fieldsets');
            ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="pid" value="<?php echo (int) $this->item->project_id; ?>">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->item->project_id; ?>">
    <input type="hidden" name="season_id" value="<?php echo (int) $this->season_id; ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php echo $this->loadTemplate('footer'); ?>
