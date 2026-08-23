<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 'r.roundcode');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$projectName = $this->project ? (string) $this->project->name : '';

$displayDate = static function ($value): string {
    $value = trim((string) $value);

    if ($value === '' || str_starts_with($value, '0000-00-00')) {
        return '';
    }

    $timestamp = strtotime($value);

    return $timestamp === false ? '' : date('d-m-Y', $timestamp);
};
?>
<div class="table-responsive">
    <?php if ($projectName !== '') : ?>
        <div class="alert alert-info py-2">
            <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_LEGEND', '<strong>' . $this->escape($projectName) . '</strong>'); ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped align-middle" id="roundsList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_NR', 'r.roundcode', $listDirn, $listOrder); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_TITLE', 'r.name', $listDirn, $listOrder); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_STARTDATE', 'r.round_date_first', $listDirn, $listOrder); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ENDDATE', 'r.round_date_last', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_MATCHES'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_PUBLISHED_CHECK'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_RESULT_CHECK'); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_TOURNEMENT', 'r.tournement', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'r.published', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ORDERING', 'r.ordering', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'r.id', $listDirn, $listOrder); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$this->items) : ?>
            <tr>
                <td colspan="12">
                    <div class="alert alert-info mb-0"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
                </td>
            </tr>
        <?php endif; ?>

        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $roundId = (int) $item->id;
            $checkedOut = (int) ($item->checked_out ?? 0);
            $canEdit = $user->authorise('core.edit', 'com_sportsmanagement');
            $canCheckin = $user->authorise('core.manage', 'com_checkin') || $checkedOut === 0 || $checkedOut === (int) $user->id;
            $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement.round.' . $roundId) && $canCheckin;
            $editLink = Route::_('index.php?option=com_sportsmanagement&task=round.edit&id=' . $roundId . '&pid=' . $this->project_id);
            $matchesLink = Route::_('index.php?option=com_sportsmanagement&view=matches&rid=' . $roundId . '&pid=' . $this->project_id);
            $firstDate = $displayDate($item->round_date_first ?? '');
            $lastDate = $displayDate($item->round_date_last ?? '');
            $markChecked = "document.getElementById('cb{$i}').checked=true;";
            ?>
            <tr>
                <td class="text-center">
                    <?php echo HTMLHelper::_('grid.id', $i, $roundId); ?>
                    <?php if ($checkedOut) : ?>
                        <div class="mt-1">
                            <?php echo HTMLHelper::_('jgrid.checkedout', $i, (int) $user->id, (string) ($item->checked_out_time ?? ''), 'rounds.', $canCheckin); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <input
                        type="number"
                        class="form-control form-control-sm text-center"
                        name="roundcode<?php echo $roundId; ?>"
                        value="<?php echo (int) $item->roundcode; ?>"
                        onchange="<?php echo $markChecked; ?>"
                    >
                </td>
                <td>
                    <?php if ($canEdit && !$checkedOut) : ?>
                        <a href="<?php echo $editLink; ?>" class="fw-semibold"><?php echo $this->escape((string) $item->name); ?></a>
                    <?php else : ?>
                        <span class="fw-semibold"><?php echo $this->escape((string) $item->name); ?></span>
                    <?php endif; ?>
                    <input
                        type="text"
                        class="form-control form-control-sm mt-1"
                        name="name<?php echo $roundId; ?>"
                        value="<?php echo $this->escape((string) $item->name); ?>"
                        onchange="<?php echo $markChecked; ?>"
                    >
                    <?php if (!empty($item->alias)) : ?>
                        <div class="small text-muted"><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape((string) $item->alias)); ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo HTMLHelper::calendar(
                        $firstDate,
                        'round_date_first' . $roundId,
                        'round_date_first' . $roundId,
                        '%d-%m-%Y',
                        [
                            'class' => 'form-control form-control-sm',
                            'onchange' => $markChecked,
                            'todayBtn' => true,
                            'weekNumbers' => false,
                        ]
                    ); ?>
                </td>
                <td>
                    <?php echo HTMLHelper::calendar(
                        $lastDate,
                        'round_date_last' . $roundId,
                        'round_date_last' . $roundId,
                        '%d-%m-%Y',
                        [
                            'class' => 'form-control form-control-sm',
                            'onchange' => $markChecked,
                            'todayBtn' => true,
                            'weekNumbers' => false,
                        ]
                    ); ?>
                </td>
                <td class="text-center">
                    <a href="<?php echo $matchesLink; ?>">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_MATCHES_LINK'); ?>
                        <span class="badge bg-secondary ms-1"><?php echo (int) ($item->countMatches ?? 0); ?></span>
                    </a>
                </td>
                <td class="text-center">
                    <?php if ((int) ($item->countMatches ?? 0) > 0 && (int) ($item->countUnPublished ?? 0) === 0) : ?>
                        <span class="badge bg-success"><?php echo Text::_('JYES'); ?></span>
                    <?php else : ?>
                        <span class="badge bg-warning text-dark"><?php echo (int) ($item->countUnPublished ?? 0); ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if ((int) ($item->countMatches ?? 0) > 0 && (int) ($item->countNoResults ?? 0) === 0) : ?>
                        <span class="badge bg-success"><?php echo Text::_('JYES'); ?></span>
                    <?php else : ?>
                        <span class="badge bg-warning text-dark"><?php echo (int) ($item->countNoResults ?? 0); ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <select
                        class="form-select form-select-sm"
                        name="tournementround<?php echo $roundId; ?>"
                        onchange="<?php echo $markChecked; ?>"
                    >
                        <option value="0"<?php echo (int) $item->tournement === 0 ? ' selected' : ''; ?>><?php echo Text::_('JNO'); ?></option>
                        <option value="1"<?php echo (int) $item->tournement === 1 ? ' selected' : ''; ?>><?php echo Text::_('JYES'); ?></option>
                    </select>
                </td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('jgrid.published', (int) $item->published, $i, 'rounds.', $canChange, 'cb'); ?>
                </td>
                <td class="text-center"><?php echo (int) ($item->ordering ?? 0); ?></td>
                <td class="text-center"><?php echo $roundId; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
