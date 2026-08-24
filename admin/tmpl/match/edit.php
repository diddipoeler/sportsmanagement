<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$matchId = (int) ($this->item->id ?? $this->match->id ?? 0);
$oldMatchId = (int) ($this->item->old_match_id ?? $this->match->old_match_id ?? 0);
$newMatchId = (int) ($this->item->new_match_id ?? $this->match->new_match_id ?? 0);
$action = 'index.php?option=com_sportsmanagement&view=match&layout=edit&id=' . $matchId;

if ($this->tmpl === 'component') {
    $action .= '&tmpl=component';
}

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

$relationSelect = static function (string $name, array $options, int $selected, string $placeholder) use ($escape): void {
    ?>
    <select class="form-select" name="jform[<?php echo $escape($name); ?>]">
        <option value="0"><?php echo Text::_($placeholder); ?></option>
        <?php foreach ($options as $option) : ?>
            <option value="<?php echo (int) ($option->value ?? 0); ?>"<?php echo (int) ($option->value ?? 0) === $selected ? ' selected' : ''; ?>>
                <?php echo $escape($option->text ?? ''); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
};

$modalTask = $this->tmpl === 'component' ? 'match.cancelmodal' : 'match.cancel';
?>
<form
    action="<?php echo Route::_($action); ?>"
    method="post"
    name="adminForm"
    id="match-form"
    class="form-validate"
>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h2 class="h4 mb-0">
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_TITLE',
                $escape($this->match->hometeam ?? ''),
                $escape($this->match->awayteam ?? '')
            ); ?>
        </h2>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-success" onclick="Joomla.submitform('match.apply', document.getElementById('match-form'));">
                <?php echo Text::_('JAPPLY'); ?>
            </button>
            <button type="button" class="btn btn-primary" onclick="Joomla.submitform('match.save', document.getElementById('match-form'));">
                <?php echo Text::_('JSAVE'); ?>
            </button>
            <button type="button" class="btn btn-secondary" onclick="Joomla.submitform('<?php echo $modalTask; ?>', document.getElementById('match-form'));">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="jsm-match-tabs" role="tablist">
        <?php
        $tabs = [
            'preview' => 'COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW',
            'details' => 'COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS',
            'score' => 'COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS',
            'report' => 'COM_SPORTSMANAGEMENT_TABS_MATCHREPORT',
            'relation' => 'COM_SPORTSMANAGEMENT_TABS_MATCHRELATION',
            'extended' => 'COM_SPORTSMANAGEMENT_TABS_EXTENDED',
        ];
        $first = true;
        foreach ($tabs as $id => $label) :
        ?>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link<?php echo $first ? ' active' : ''; ?>"
                    id="jsm-match-<?php echo $id; ?>-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#jsm-match-<?php echo $id; ?>"
                    type="button"
                    role="tab"
                    aria-controls="jsm-match-<?php echo $id; ?>"
                    aria-selected="<?php echo $first ? 'true' : 'false'; ?>"
                ><?php echo Text::_($label); ?></button>
            </li>
        <?php
            $first = false;
        endforeach;
        ?>
    </ul>

    <div class="tab-content" id="jsm-match-tab-content">
        <div class="tab-pane fade show active" id="jsm-match-preview" role="tabpanel" aria-labelledby="jsm-match-preview-tab" tabindex="0">
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MP'); ?></legend>
                <?php $renderFields($this->form->getFieldset('matchpreview')); ?>
            </fieldset>
        </div>

        <div class="tab-pane fade" id="jsm-match-details" role="tabpanel" aria-labelledby="jsm-match-details-tab" tabindex="0">
            <fieldset class="options-form mb-4">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD'); ?></legend>
                <?php $renderFields($this->form->getFieldset('matchdetails')); ?>
            </fieldset>
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD'); ?></legend>
                <?php $renderFields($this->form->getFieldset('matchalternativ')); ?>
            </fieldset>
        </div>

        <div class="tab-pane fade" id="jsm-match-score" role="tabpanel" aria-labelledby="jsm-match-score-tab" tabindex="0">
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_SD'); ?></legend>
                <?php if (($this->match->hometeam ?? '') !== '' || ($this->match->awayteam ?? '') !== '') : ?>
                    <p class="text-muted">
                        <?php echo $escape($this->match->hometeam ?? ''); ?>
                        &nbsp;&ndash;&nbsp;
                        <?php echo $escape($this->match->awayteam ?? ''); ?>
                    </p>
                <?php endif; ?>
                <?php $renderFields($this->form->getFieldset('scoredetails')); ?>
            </fieldset>
        </div>

        <div class="tab-pane fade" id="jsm-match-report" role="tabpanel" aria-labelledby="jsm-match-report-tab" tabindex="0">
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MR'); ?></legend>
                <?php $renderFields($this->form->getFieldset('matchreport')); ?>
            </fieldset>
        </div>

        <div class="tab-pane fade" id="jsm-match-relation" role="tabpanel" aria-labelledby="jsm-match-relation-tab" tabindex="0">
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_DETAILS'); ?></legend>

                <div class="control-group mb-3">
                    <div class="control-label">
                        <label for="jsm-old-match"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_OLD_ID'); ?></label>
                    </div>
                    <div class="controls" id="jsm-old-match">
                        <?php $relationSelect('old_match_id', $this->oldMatchOptions, $oldMatchId, 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_OLD_MATCH'); ?>
                        <?php if ($oldMatchId > 0) : ?>
                            <a class="btn btn-sm btn-outline-secondary mt-2" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=match&layout=edit&tmpl=component&id=' . $oldMatchId); ?>">
                                <?php echo Text::_('JEDIT'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="control-group mb-3">
                    <div class="control-label">
                        <label for="jsm-new-match"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_NEW_ID'); ?></label>
                    </div>
                    <div class="controls" id="jsm-new-match">
                        <?php $relationSelect('new_match_id', $this->newMatchOptions, $newMatchId, 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_NEW_MATCH'); ?>
                        <?php if ($newMatchId > 0) : ?>
                            <a class="btn btn-sm btn-outline-secondary mt-2" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=match&layout=edit&tmpl=component&id=' . $newMatchId); ?>">
                                <?php echo Text::_('JEDIT'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="tab-pane fade" id="jsm-match-extended" role="tabpanel" aria-labelledby="jsm-match-extended-tab" tabindex="0">
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'); ?></legend>
                <?php if ($this->extended) : ?>
                    <?php $hasExtended = false; ?>
                    <?php foreach ($this->extended->getFieldsets() as $fieldset) : ?>
                        <?php $fields = $this->extended->getFieldset($fieldset->name); ?>
                        <?php if (!$fields) { continue; } ?>
                        <?php $hasExtended = true; ?>
                        <h3 class="h6 mt-3"><?php echo Text::_((string) $fieldset->name); ?></h3>
                        <?php $renderFields($fields); ?>
                    <?php endforeach; ?>
                    <?php if (!$hasExtended) : ?>
                        <p class="text-muted"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="text-muted"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
                <?php endif; ?>
            </fieldset>
        </div>
    </div>

    <?php echo $this->form->getInput('id'); ?>
    <?php echo $this->form->getInput('checked_out'); ?>
    <?php echo $this->form->getInput('checked_out_time'); ?>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
