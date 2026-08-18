<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$radio = static function (string $name, int $value): string {
    $no = $value === 0 ? ' checked' : '';
    $yes = $value === 1 ? ' checked' : '';
    return '<div class="form-check form-check-inline">'
        . '<input class="form-check-input" type="radio" name="' . $name . '" id="' . $name . '_0" value="0"' . $no . '>'
        . '<label class="form-check-label" for="' . $name . '_0">' . Text::_('JNO') . '</label></div>'
        . '<div class="form-check form-check-inline">'
        . '<input class="form-check-input" type="radio" name="' . $name . '" id="' . $name . '_1" value="1"' . $yes . '>'
        . '<label class="form-check-label" for="' . $name . '_1">' . Text::_('JYES') . '</label></div>';
};
$selectedFinal4 = static function (array $values, int $index): int {
    return (int) ($values[$index] ?? 0);
};
?>
<div class="<?php echo $escape($this->divclasscontainer); ?>" id="predictionuser-edit">
    <h2><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_TITLE', $escape($this->predictionGame->name ?? '')); ?></h2>

    <form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" method="post" id="prediction-user-edit-form">
        <div class="table-responsive">
            <table class="table align-middle">
                <tbody>
                <tr>
                    <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_NAME'); ?></th>
                    <td><?php echo $escape($this->displayName()); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_REGDATE'); ?></th>
                    <td>
                        <?php if ($this->isPredictionAdmin) : ?>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <input class="form-control" type="date" name="registerDate" value="<?php echo $escape($this->registerDate()); ?>">
                                </div>
                                <div class="col-sm-6">
                                    <input class="form-control" type="time" step="1" name="registerTime" value="<?php echo $escape($this->registerTime()); ?>">
                                </div>
                            </div>
                        <?php else : ?>
                            <?php echo $this->registerDate() !== '' ? $escape(($this->predictionMember->pmRegisterDate ?? '')) : Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_UNKNOWN'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_APPROVED'); ?></th>
                    <td><?php echo !empty($this->predictionMember->approved) ? Text::_('JYES') : Text::_('JNO'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_GROUP'); ?></th>
                    <td>
                        <select class="form-select" name="group_id"<?php echo $this->canChangeGroup ? '' : ' disabled'; ?>>
                            <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PREDICTION_MEMBER_GROUP'); ?></option>
                            <?php foreach ($this->groupOptions as $group) : ?>
                                <?php $value = (int) ($group->value ?? 0); ?>
                                <option value="<?php echo $value; ?>"<?php echo $value === (int) ($this->predictionMember->group_id ?? 0) ? ' selected' : ''; ?>><?php echo $escape($group->text ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$this->canChangeGroup) : ?>
                            <div class="form-text text-danger"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_NO_GROUP_CHANGE'); ?></div>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php if (!empty($this->config['allow_alias'])) : ?>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_ALIAS'); ?></th>
                        <td><input class="form-control" type="text" name="aliasName" maxlength="255" value="<?php echo $escape($this->predictionMember->aliasName ?? ''); ?>"></td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($this->config['edit_slogan'])) : ?>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_SLOGAN'); ?></th>
                        <td><input class="form-control" type="text" name="slogan" maxlength="255" value="<?php echo $escape($this->predictionMember->slogan ?? ''); ?>"></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_SHOW_PROFILE'); ?></th>
                    <td><?php echo $radio('show_profile', !empty($this->predictionMember->show_profile) ? 1 : 0); ?></td>
                </tr>

                <?php if (!empty($this->config['edit_reminder'])) : ?>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_REMINDER'); ?></th>
                        <td><?php echo $radio('reminder', !empty($this->predictionMember->reminder) ? 1 : 0); ?></td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($this->config['edit_receipt'])) : ?>
                    <tr>
                        <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_RECEIPT'); ?></th>
                        <td><?php echo $radio('receipt', !empty($this->predictionMember->receipt) ? 1 : 0); ?></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <th scope="row"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_ALLOW_ADMIN'); ?></th>
                    <td><?php echo $radio('admintipp', !empty($this->predictionMember->admintipp) ? 1 : 0); ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <?php if (!empty($this->config['edit_favteam']) && $this->editProjects) : ?>
            <fieldset class="mb-4">
                <legend class="h4"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_FAVTEAM'); ?></legend>
                <?php foreach ($this->editProjects as $row) : ?>
                    <div class="mb-3">
                        <label class="form-label" for="fav_team_<?php echo (int) $row['project_id']; ?>"><?php echo $escape($row['project_name']); ?></label>
                        <select class="form-select" id="fav_team_<?php echo (int) $row['project_id']; ?>" name="fav_team[<?php echo (int) $row['project_id']; ?>]">
                            <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_TEAM'); ?></option>
                            <?php foreach ($row['teams'] as $team) : ?>
                                <?php $value = (int) ($team->value ?? 0); ?>
                                <option value="<?php echo $value; ?>"<?php echo $value === (int) $row['fav_team'] ? ' selected' : ''; ?>><?php echo $escape($team->text ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        <?php endif; ?>

        <?php if ($this->editProjects) : ?>
            <fieldset class="mb-4">
                <legend class="h4"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_CHAMPION'); ?></legend>
                <?php foreach ($this->editProjects as $row) : ?>
                    <div class="mb-3">
                        <label class="form-label" for="champ_tipp_<?php echo (int) $row['project_id']; ?>"><?php echo $escape($row['project_name']); ?></label>
                        <select class="form-select" id="champ_tipp_<?php echo (int) $row['project_id']; ?>" name="champ_tipp[<?php echo (int) $row['project_id']; ?>]"<?php echo ($row['competitive_open'] && $row['champ_enabled']) ? '' : ' disabled'; ?>>
                            <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_TEAM'); ?></option>
                            <?php foreach ($row['teams'] as $team) : ?>
                                <?php $value = (int) ($team->value ?? 0); ?>
                                <option value="<?php echo $value; ?>"<?php echo $value === (int) $row['champ_tipp'] ? ' selected' : ''; ?>><?php echo $escape($team->text ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$row['competitive_open']) : ?>
                            <div class="form-text text-danger"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_NO_GROUP_CHANGE'); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        <?php endif; ?>

        <?php if (!empty($this->config['show_final4_tip']) && $this->editProjects) : ?>
            <fieldset class="mb-4">
                <legend class="h4"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_FINAL4'); ?></legend>
                <?php foreach ($this->editProjects as $row) : ?>
                    <div class="mb-4">
                        <div class="form-label"><?php echo $escape($row['project_name']); ?></div>
                        <div class="row g-2">
                            <?php for ($index = 0; $index < 4; $index++) : ?>
                                <div class="col-md-3">
                                    <select class="form-select" name="final4_tipp<?php echo $index + 1; ?>[<?php echo (int) $row['project_id']; ?>]"<?php echo $row['competitive_open'] ? '' : ' disabled'; ?>>
                                        <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_TEAM'); ?></option>
                                        <?php foreach ($row['teams'] as $team) : ?>
                                            <?php $value = (int) ($team->value ?? 0); ?>
                                            <option value="<?php echo $value; ?>"<?php echo $value === $selectedFinal4($row['final4_tipp'], $index) ? ' selected' : ''; ?>><?php echo $escape($team->text ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <?php if (!$row['competitive_open']) : ?>
                            <div class="form-text text-danger"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_NO_GROUP_CHANGE'); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        <?php endif; ?>

        <?php if (!empty($this->config['edit_avatar_upload'])) : ?>
            <div class="mb-4">
                <label class="form-label" for="prediction-member-picture"><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_AVATAR'); ?></label>
                <input class="form-control" id="prediction-member-picture" type="text" name="picture" maxlength="255" value="<?php echo $escape($this->predictionMember->picture ?? ''); ?>">
            </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" name="task" value="predictionuser.save"><?php echo Text::_('JSAVE'); ?></button>
            <button type="submit" class="btn btn-secondary" name="task" value="predictionuser.cancel" formnovalidate><?php echo Text::_('JCANCEL'); ?></button>
        </div>

        <input type="hidden" name="option" value="com_sportsmanagement">
        <input type="hidden" name="view" value="predictionuser">
        <input type="hidden" name="layout" value="edit">
        <input type="hidden" name="prediction_id" value="<?php echo (int) $this->predictionGameID; ?>">
        <input type="hidden" name="uid" value="<?php echo (int) $this->memberID; ?>">
        <input type="hidden" name="member_id" value="<?php echo (int) $this->memberID; ?>">
        <input type="hidden" name="pj" value="<?php echo (int) $this->projectID; ?>">
        <input type="hidden" name="r" value="<?php echo (int) $this->roundID; ?>">
        <input type="hidden" name="pggroup" value="<?php echo (int) $this->predictionGroupID; ?>">
        <input type="hidden" name="cfg_which_database" value="<?php echo (int) $this->databaseSelector; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
