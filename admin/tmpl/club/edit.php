<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

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

$clubId = (int) ($this->item->id ?? 0);
$action = Route::_('index.php?option=com_sportsmanagement&view=club&layout=edit&id=' . $clubId);
$fieldsets = $this->form->getFieldsets();
$tabId = static function (string $name): string {
    return 'club-' . (preg_replace('/[^A-Za-z0-9_-]+/', '-', $name) ?: 'details');
};
$firstFieldset = reset($fieldsets);
$activeTab = $firstFieldset ? $tabId((string) $firstFieldset->name) : 'club-details';
?>
<form
    action="<?php echo $action; ?>"
    method="post"
    name="adminForm"
    id="club-form"
    class="form-validate"
>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'clubTabs', [
        'active' => $activeTab,
        'recall' => true,
        'breakpoint' => 768,
    ]); ?>

    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php
        $name = (string) $fieldset->name;
        $currentTab = $tabId($name);
        $label = Text::_((string) ($fieldset->label ?: $name));
        echo HTMLHelper::_('uitab.addTab', 'clubTabs', $currentTab, $label);
        ?>
        <div class="options-form mb-4" id="<?php echo $escape($currentTab); ?>-content">
            <?php if ($name === 'teamsofclub') : ?>
                <?php if ($this->teamsofclub) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME'); ?></th>
                                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_SHORT_NAME'); ?></th>
                                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_CLUB'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->teamsofclub as $team) : ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="team_id[]" value="<?php echo (int) $team->id; ?>">
                                            <input class="form-control" type="text" name="team_value_id[]" maxlength="100" value="<?php echo $escape($team->name ?? ''); ?>">
                                        </td>
                                        <td>
                                            <input class="form-control" type="text" name="team_short_name[]" maxlength="100" value="<?php echo $escape($team->short_name ?? ''); ?>">
                                        </td>
                                        <td>
                                            <input class="form-control" type="number" name="club_value_id[]" value="<?php echo (int) ($team->club_id ?? $clubId); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p class="text-muted mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
                <?php endif; ?>

            <?php elseif ($name === 'clublogohistory') : ?>
                <?php if ($this->logoHistoryForm) : ?>
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-lg-6">
                            <?php $renderFields($this->logoHistoryForm->getFieldset('picture')); ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <?php $renderFields($this->logoHistoryForm->getFieldset('seasons')); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($this->logohistory) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'); ?></th>
                                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_LOGO_LARGE'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->logohistory as $logo) : ?>
                                    <tr>
                                        <td><?php echo (int) ($logo->id ?? 0); ?></td>
                                        <td><?php echo $escape($logo->seasonname ?? ''); ?></td>
                                        <td>
                                            <?php if (!empty($logo->logo_big)) : ?>
                                                <img
                                                    src="<?php echo $escape(Uri::root() . ltrim((string) $logo->logo_big, '/')); ?>"
                                                    alt=""
                                                    style="max-height: 50px; width: auto;"
                                                >
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            <?php elseif ($name === 'extended') : ?>
                <?php if ($this->extended) : ?>
                    <?php $rendered = false; ?>
                    <?php foreach ($this->extended->getFieldsets() as $extendedFieldset) : ?>
                        <?php $fields = $this->extended->getFieldset($extendedFieldset->name); ?>
                        <?php if (!$fields) { continue; } ?>
                        <?php $rendered = true; ?>
                        <?php $renderFields($fields); ?>
                    <?php endforeach; ?>
                    <?php if (!$rendered) : ?>
                        <p class="text-muted mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="text-muted mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
                <?php endif; ?>

            <?php elseif ($name === 'extra_fields') : ?>
                <?php if ($this->extraFields) : ?>
                    <?php foreach ($this->extraFields as $extra) : ?>
                        <div class="control-group mb-3">
                            <div class="control-label">
                                <label><?php echo $escape($extra->name ?? ''); ?></label>
                            </div>
                            <div class="controls">
                                <textarea class="form-control" name="extraf[]" rows="4"><?php echo $escape($extra->fvalue ?? ''); ?></textarea>
                                <input type="hidden" name="extra_id[]" value="<?php echo (int) ($extra->id ?? 0); ?>">
                                <input type="hidden" name="extra_value_id[]" value="<?php echo (int) ($extra->value_id ?? 0); ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="text-muted mb-0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS'); ?></p>
                <?php endif; ?>

            <?php else : ?>
                <?php $renderFields($this->form->getFieldset($name)); ?>
            <?php endif; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <?php if ($clubId <= 0) : ?>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="createTeam" id="createTeam" value="1" checked>
            <label class="form-check-label" for="createTeam">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CREATE_TEAM'); ?>
            </label>
        </div>
    <?php endif; ?>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
