<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
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
        ?>
        <fieldset class="options-form mb-3">
            <?php if (!empty($fieldset->label)) : ?>
                <legend><?php echo Text::_((string) $fieldset->label); ?></legend>
            <?php endif; ?>
            <?php $renderFields($fields); ?>
        </fieldset>
        <?php
    }

    return $rendered;
};

$playgroundId = (int) ($this->item->id ?? 0);
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=playground&layout=edit&id=' . $playgroundId); ?>"
    method="post"
    name="adminForm"
    id="playground-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'playgroundTabs', [
        'active' => 'playground-details',
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'playgroundTabs', 'playground-details', Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('details')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'playgroundTabs', 'playground-description', Text::_('COM_SPORTSMANAGEMENT_TABS_DESCRIPTION')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('description')); ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'playgroundTabs', 'playground-picture', Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE')); ?>
    <div class="options-form mb-4">
        <?php $renderFields($this->form->getFieldset('picture')); ?>
        <?php if (!$this->map && $playgroundId > 0) : ?>
            <div class="alert alert-warning mt-3">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'playgroundTabs', 'playground-extended', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED')); ?>
    <div class="options-form mb-4">
        <?php if (!$renderExtended($this->extended)) : ?>
            <p class="text-muted mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
        <?php endif; ?>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php if ($this->extendeduser) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'playgroundTabs', 'playground-extended-user', Text::_('COM_SPORTSMANAGEMENT_EXT_EXTENDED_USER_PREFERENCES')); ?>
        <div class="options-form mb-4">
            <?php $renderExtended($this->extendeduser); ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php if ($this->extraFields) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'playgroundTabs', 'playground-extra-fields', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS')); ?>
        <div class="options-form mb-4">
            <?php foreach ($this->extraFields as $extraField) : ?>
                <div class="control-group mb-3">
                    <label class="control-label">
                        <?php echo htmlspecialchars((string) ($extraField->name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                    <div class="controls">
                        <textarea name="extraf[]" rows="4" class="form-control"><?php
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

    <?php if ($this->logoHistoryForm) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'playgroundTabs', 'playground-logo-history', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUND_LOGO_HISTORY')); ?>
        <div class="options-form mb-4">
            <div class="row g-4 mb-3">
                <div class="col-12 col-lg-6">
                    <?php $renderFields($this->logoHistoryForm->getFieldset('picture')); ?>
                </div>
                <div class="col-12 col-lg-6">
                    <?php $renderFields($this->logoHistoryForm->getFieldset('seasons')); ?>
                </div>
            </div>

            <?php if ($this->logohistory) : ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'); ?></th>
                            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUND_PICTURE'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->logohistory as $history) : ?>
                            <?php
                            $logo = trim((string) ($history->logo_big ?? ''));
                            $logoUrl = '';
                            if ($logo !== '') {
                                if (preg_match('#^https?://#i', $logo)) {
                                    $logoUrl = $logo;
                                } elseif (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $logo)) {
                                    $logoUrl = Uri::root() . ltrim($logo, '/');
                                }
                            }
                            ?>
                            <tr>
                                <td><?php echo (int) ($history->id ?? 0); ?></td>
                                <td><?php echo htmlspecialchars((string) ($history->seasonname ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($logoUrl !== '') : ?>
                                        <a href="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                            <img
                                                src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                                alt=""
                                                class="img-thumbnail"
                                                style="max-width:96px;max-height:96px"
                                                loading="lazy"
                                            >
                                        </a>
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
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php if ($playgroundId > 0) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'playgroundTabs', 'playground-notes', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUND_NOTIZ')); ?>
        <div class="options-form mb-4">
            <p class="text-muted">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUND_NOTIZ_DESC'); ?>
            </p>

            <?php if ($this->playgroundnotic) : ?>
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">date_von</th>
                            <th scope="col">date_bis</th>
                            <th scope="col">name_visitors</th>
                            <th scope="col">notes</th>
                            <th scope="col">max_visitors</th>
                            <th scope="col">max_visitors_int</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->playgroundnotic as $detail) : ?>
                            <tr>
                                <td>
                                    <?php echo (int) ($detail->id ?? 0); ?>
                                    <input type="hidden" name="change_id[]" value="<?php echo (int) ($detail->id ?? 0); ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="change_date_von[]" value="<?php
                                        echo htmlspecialchars(SportsManagementDateHelper::convertDate((string) ($detail->date_von ?? ''), 1), ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="change_date_bis[]" value="<?php
                                        echo htmlspecialchars(SportsManagementDateHelper::convertDate((string) ($detail->date_bis ?? ''), 1), ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" name="change_name_visitors[]">
                                        <?php foreach (['NAME', 'VISITORS'] as $type) : ?>
                                            <option value="<?php echo $type; ?>"<?php echo (string) ($detail->name_visitors ?? '') === $type ? ' selected' : ''; ?>>
                                                <?php echo Text::_($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="change_notes[]" value="<?php
                                        echo htmlspecialchars((string) ($detail->notes ?? ''), ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </td>
                                <td><input type="number" min="0" class="form-control form-control-sm" name="change_max_visitors[]" value="<?php echo max(0, (int) ($detail->max_visitors ?? 0)); ?>"></td>
                                <td><input type="number" min="0" class="form-control form-control-sm" name="change_max_visitors_int[]" value="<?php echo max(0, (int) ($detail->max_visitors_int ?? 0)); ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong><?php echo Text::_('JNEW'); ?></strong>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-jsm-add-playground-detail>
                    <?php echo Text::_('JTOOLBAR_NEW'); ?>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle" id="playground-detail-new">
                    <thead>
                    <tr>
                        <th scope="col">date_von</th>
                        <th scope="col">date_bis</th>
                        <th scope="col">name_visitors</th>
                        <th scope="col">notes</th>
                        <th scope="col">max_visitors</th>
                        <th scope="col">max_visitors_int</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <template id="playground-detail-row-template">
                <tr>
                    <td><input type="text" class="form-control form-control-sm" name="date_von[]" value=""></td>
                    <td><input type="text" class="form-control form-control-sm" name="date_bis[]" value=""></td>
                    <td>
                        <select class="form-select form-select-sm" name="name_visitors[]">
                            <option value="NAME"><?php echo Text::_('NAME'); ?></option>
                            <option value="VISITORS"><?php echo Text::_('VISITORS'); ?></option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm" name="notes[]" value=""></td>
                    <td><input type="number" min="0" class="form-control form-control-sm" name="max_visitors[]" value="0"></td>
                    <td><input type="number" min="0" class="form-control form-control-sm" name="max_visitors_int[]" value="0"></td>
                </tr>
            </template>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
