<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = (string) $this->state->get('list.ordering', $this->individualProject ? 'ppl.lastname' : 't.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$pid = (int) $this->project->id;
$renderOptions = static function (array $options, int $selected, string $placeholder = ''): string {
    $html = '<option value="0">' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '</option>';
    foreach ($options as $option) {
        $value = (int) $option->value;
        $html .= '<option value="' . $value . '"' . ($selected === $value ? ' selected' : '') . '>' . htmlspecialchars((string) $option->text, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
};
$number = static fn(string $name, object $item): string => '<input class="form-control form-control-sm text-center" type="number" name="' . $name . '[' . (int) $item->id . ']" value="' . (int) $item->{$name} . '" onchange="document.getElementById(\'cb' . $item->_rowIndex . '\').checked=true">';
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $pid); ?>" method="post" name="adminForm" id="adminForm">
    <?php if ($this->filterForm) echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <div class="alert alert-info py-2">
        <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_LEGEND', '<strong>' . $this->escape((string) $this->project->name) . '</strong>'); ?>
        <?php if (!empty($this->project->season_name)) : ?><span class="ms-2 text-muted"><?php echo $this->escape((string) $this->project->season_name); ?></span><?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-striped align-middle" id="projectteamsList">
            <thead><tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TEAMNAME', $this->individualProject ? 'ppl.lastname' : 't.name', $listDirn, $listOrder); ?></th>
                <?php if (!$this->individualProject) : ?>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_MANAGE_PERSONNEL'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DIVISION'); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_VENUE'); ?></th>
                <?php endif; ?>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_INITIAL_POINTS', 'pt.start_points', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PENALTY_P'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_MA', 'pt.matches_finally', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PLUS_P'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_MINUS_P'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_W'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_D'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_L'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_HG'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_GG'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DG'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_IS_IN_SCORE'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_USE_FINALLY'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHAMPION'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_FINALTABLERANK'); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'pt.published', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'pt.id', $listDirn, $listOrder); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : $item->_rowIndex = $i; ?>
                <tr>
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id); ?></td>
                    <td>
                        <strong><?php echo $this->escape((string) $item->name); ?></strong>
                        <?php if (!$this->individualProject && !empty($item->clubname)) : ?><div class="small text-muted"><?php echo $this->escape((string) $item->clubname); ?><?php if (!empty($item->country)) : ?> · <?php echo $this->escape((string) $item->country); ?><?php endif; ?></div><?php endif; ?>
                        <?php if (!empty($item->editor)) : ?><div class="small text-muted"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADMIN'); ?>: <?php echo $this->escape((string) $item->editor); ?></div><?php endif; ?>
                    </td>
                    <?php if (!$this->individualProject) : ?>
                        <td>
                            <a class="btn btn-sm btn-outline-secondary mb-1" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=teamplayers&pid=' . $pid . '&project_team_id=' . (int) $item->id . '&team_id=' . (int) $item->team_id . '&season_team_id=' . (int) $item->season_team_id . '&persontype=1'); ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE'); ?> (<?php echo (int) $item->playercount; ?>)</a>
                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=teamplayers&pid=' . $pid . '&project_team_id=' . (int) $item->id . '&team_id=' . (int) $item->team_id . '&season_team_id=' . (int) $item->season_team_id . '&persontype=2'); ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE'); ?> (<?php echo (int) $item->staffcount; ?>)</a>
                        </td>
                        <td><select class="form-select form-select-sm" name="division_id[<?php echo (int) $item->id; ?>]" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"><?php echo $renderOptions($this->divisionOptions, (int) ($item->division_id ?? 0), Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION')); ?></select></td>
                        <td><select class="form-select form-select-sm" name="standard_playground[<?php echo (int) $item->id; ?>]" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"><?php echo $renderOptions($this->playgroundOptions, (int) ($item->standard_playground ?? 0), Text::_('COM_SPORTSMANAGEMENT_SETTINGS_LABEL_PH_STADIUM')); ?></select></td>
                    <?php endif; ?>
                    <td><?php echo $number('start_points', $item); ?></td>
                    <td><?php echo $number('penalty_points', $item); ?></td>
                    <td><?php echo $number('matches_finally', $item); ?></td>
                    <td><?php echo $number('points_finally', $item); ?></td>
                    <td><?php echo $number('neg_points_finally', $item); ?></td>
                    <td><?php echo $number('won_finally', $item); ?></td>
                    <td><?php echo $number('draws_finally', $item); ?></td>
                    <td><?php echo $number('lost_finally', $item); ?></td>
                    <td><?php echo $number('homegoals_finally', $item); ?></td>
                    <td><?php echo $number('guestgoals_finally', $item); ?></td>
                    <td><?php echo $number('diffgoals_finally', $item); ?></td>
                    <?php foreach (['is_in_score', 'use_finally', 'champion'] as $field) : ?>
                        <td><select class="form-select form-select-sm" name="<?php echo $field; ?>[<?php echo (int) $item->id; ?>]" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"><option value="0"<?php echo (int) $item->{$field} === 0 ? ' selected' : ''; ?>><?php echo Text::_('JNO'); ?></option><option value="1"<?php echo (int) $item->{$field} === 1 ? ' selected' : ''; ?>><?php echo Text::_('JYES'); ?></option></select></td>
                    <?php endforeach; ?>
                    <td><input class="form-control form-control-sm text-center" type="number" min="0" max="40" name="finaltablerank[<?php echo (int) $item->id; ?>]" value="<?php echo (int) $item->finaltablerank; ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"></td>
                    <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', (int) $item->published, $i, 'projectteams.', true, 'cb'); ?></td>
                    <td class="text-center"><?php echo (int) $item->id; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php echo $this->pagination->getListFooter(); ?>
    <input type="hidden" name="pid" value="<?php echo $pid; ?>"><input type="hidden" name="task" value=""><input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>"><input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
