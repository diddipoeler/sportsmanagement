<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

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

$renderExtended = static function ($form) use ($renderFields): bool {
    if (!$form) {
        return false;
    }

    $rendered = false;

    foreach ($form->getFieldsets() as $fieldset) {
        $fields = $form->getFieldset($fieldset->name);

        if (!$fields) {
            continue;
        }

        $rendered = true;
        $label = trim((string) ($fieldset->label ?? $fieldset->name));
        ?>
        <fieldset class="options-form mb-4">
            <?php if ($label !== '') : ?>
                <legend><?php echo Text::_($label); ?></legend>
            <?php endif; ?>
            <?php $renderFields($fields); ?>
        </fieldset>
        <?php
    }

    return $rendered;
};

$leagueId = (int) ($this->item->id ?? 0);

$extendedUserHtml = '';
if ($this->extendeduser) {
    ob_start();
    $hasExtendedUser = $renderExtended($this->extendeduser);
    $extendedUserHtml = (string) ob_get_clean();
} else {
    $hasExtendedUser = false;
}
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=league&layout=edit&id=' . $leagueId); ?>"
    method="post"
    name="adminForm"
    id="league-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'leagueTabs', [
        'active' => 'league-details',
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'leagueTabs', 'league-details', Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('details')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'leagueTabs', 'league-request', Text::_('COM_SPORTSMANAGEMENT_TABS_REQUEST')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('request')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'leagueTabs', 'league-description', Text::_('COM_SPORTSMANAGEMENT_TABS_DESCRIPTION')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('description')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'leagueTabs', 'league-picture', Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('picture')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php if ($this->logoHistoryForm) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'leagueTabs', 'league-logo-history', Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_LOGO_HISTORY')); ?>
        <div class="options-form mb-4">
            <?php if ($this->logohistory) : ?>
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASON_TITLE'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_EDIT_PICTURE'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->logohistory as $history) : ?>
                            <?php
                            $logo = trim((string) ($history->logo_big ?? ''));
                            $logoUrl = '';

                            if ($logo !== '') {
                                if (preg_match('#^https://#i', $logo)) {
                                    $logoUrl = $logo;
                                } elseif (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $logo)) {
                                    $logoUrl = Uri::root() . ltrim($logo, '/');
                                }
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($history->seasonname ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($logoUrl !== '') : ?>
                                        <img
                                            src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                            alt=""
                                            class="img-thumbnail"
                                            style="max-width:96px;max-height:96px"
                                            loading="lazy"
                                        >
                                    <?php else : ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php $renderFields($this->logoHistoryForm->getFieldset('history')); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'leagueTabs', 'league-extended', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED')); ?>
    <div class="options-form mb-4">
        <?php if (!$renderExtended($this->extended)) : ?>
            <p class="text-muted mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
        <?php endif; ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php if ($hasExtendedUser) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'leagueTabs', 'league-extended-user', Text::_('COM_SPORTSMANAGEMENT_EXT_EXTENDED_USER_PREFERENCES')); ?>
        <div class="options-form mb-4">
            <?php echo $extendedUserHtml; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php if ($this->extraFields) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'leagueTabs', 'league-extra-fields', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS')); ?>
        <div class="options-form mb-4">
            <?php foreach ($this->extraFields as $extraField) : ?>
                <div class="control-group mb-3">
                    <label class="control-label">
                        <?php echo htmlspecialchars((string) ($extraField->name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                    <div class="controls">
                        <textarea name="extraf[]" rows="3" class="form-control"><?php
                            echo htmlspecialchars((string) ($extraField->fvalue ?? ''), ENT_QUOTES, 'UTF-8');
                        ?></textarea>
                        <input type="hidden" name="extra_id[]" value="<?php echo (int) ($extraField->id ?? 0); ?>">
                        <input type="hidden" name="extra_value_id[]" value="<?php echo (int) ($extraField->value_id ?? 0); ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
