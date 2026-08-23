<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = (string) $this->state->get('list.ordering', $this->individualProject ? 'ppl.lastname' : 't.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$pid = (int) $this->project_id;
$divisionMode = (string) $this->project->project_type === 'DIVISIONS_LEAGUE';
$allowProjectChange = (bool) ComponentHelper::getParams('com_sportsmanagement')
    ->get('show_option_projectteam_change', false);

$number = static function (string $field, object $item, int $row): string {
    return '<input class="form-control form-control-sm text-center" type="number" name="'
        . $field . (int) $item->id . '" value="'
        . htmlspecialchars((string) ($item->{$field} ?? 0), ENT_QUOTES, 'UTF-8')
        . '" onchange="document.getElementById(\'cb' . $row . '\').checked=true">';
};

$yesNo = [
    HTMLHelper::_('select.option', 0, Text::_('JNO')),
    HTMLHelper::_('select.option', 1, Text::_('JYES')),
];
$divisionStatFields = [
    'start_points' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_INITIAL_POINTS',
    'matches_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_MA',
    'points_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PLUS_P',
    'neg_points_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_MINUS_P',
    'penalty_points' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PENALTY_P',
    'won_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_W',
    'draws_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_D',
    'lost_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_L',
    'homegoals_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_HG',
    'guestgoals_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_GG',
    'diffgoals_finally' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DG',
];
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $pid); ?>" method="post" name="adminForm" id="adminForm">
    <?php if ($this->filterForm) : ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <?php endif; ?>

    <div class="alert alert-info py-2">
        <?php echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_LEGEND',
            '<strong>' . $this->escape((string) $this->project->name) . '</strong>'
        ); ?>
        <?php if (!empty($this->project->season_name)) : ?>
            <span class="ms-2 text-muted"><?php echo $this->escape((string) $this->project->season_name); ?></span>
        <?php endif; ?>
    </div>

    <?php if ($this->quickAddTeams || ($allowProjectChange && $this->projectsbyleagueseason)) : ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <?php if ($this->quickAddTeams) : ?>
                        <div class="col-md-6">
                            <label class="form-label" for="team_id"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_QUICKADD'); ?></label>
                            <?php echo HTMLHelper::_(
                                'select.genericlist',
                                $this->quickAddTeams,
                                'team_id',
                                'class="form-select" id="team_id"',
                                'value',
                                'text',
                                0
                            ); ?>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('projectteams.addteam');">
                                <?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_ADD'); ?>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($allowProjectChange && $this->projectsbyleagueseason) : ?>
                        <div class="col-md-5">
                            <label class="form-label" for="all_project_id"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGE'); ?></label>
                            <?php echo HTMLHelper::_(
                                'select.genericlist',
                                $this->projectsbyleagueseason,
                                'all_project_id',
                                'class="form-select" id="all_project_id"',
                                'value',
                                'text',
                                $pid
                            ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped align-middle" id="projectteamsList">
            <thead>
                <tr>
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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $id = (int) $item->id; ?>
                    <tr>
                        <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $id); ?></td>
                        <td>
                            <strong><?php echo $this->escape((string) $item->name); ?></strong>
                            <?php if (!$this->individualProject && !empty($item->clubname)) : ?>
                                <div class="small text-muted">
                                    <?php echo $this->escape((string) $item->clubname); ?>
                                    <?php if (!empty($item->country)) : ?> · <?php echo $this->escape((string) $item->country); ?><?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($item->editor)) : ?>
                                <div class="small text-muted"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADMIN'); ?>: <?php echo $this->escape((string) $item->editor); ?></div>
                            <?php endif; ?>
                        </td>

                        <?php if (!$this->individualProject) : ?>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary mb-1" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=teamplayers&pid=' . $pid . '&project_team_id=' . $id . '&team_id=' . (int) $item->team_id . '&season_team_id=' . (int) $item->season_team_id . '&persontype=1'); ?>">
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE'); ?> (<?php echo (int) $item->playercount; ?>)
                                </a>
                                <a class="btn btn-sm btn-outline-secondary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=teamplayers&pid=' . $pid . '&project_team_id=' . $id . '&team_id=' . (int) $item->team_id . '&season_team_id=' . (int) $item->season_team_id . '&persontype=2'); ?>">
                                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE'); ?> (<?php echo (int) $item->staffcount; ?>)
                                </a>
                            </td>
                            <td>
                                <?php echo HTMLHelper::_(
                                    'select.genericlist',
                                    array_merge([HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'))], $this->divisionOptions),
                                    'division_id' . $id,
                                    'class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $i . '\').checked=true"',
                                    'value',
                                    'text',
                                    (int) ($item->division_id ?? 0)
                                ); ?>

                                <?php if ($divisionMode && $this->divisionOptions) : ?>
                                    <details class="mt-2">
                                        <summary><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DIVISION'); ?></summary>
                                        <?php foreach ($this->divisionOptions as $division) :
                                            $divisionId = (int) ($division->value ?? 0);
                                            if ($divisionId <= 0) { continue; } ?>
                                            <div class="border rounded p-2 mt-2">
                                                <strong><?php echo $this->escape((string) $division->text); ?></strong>
                                                <?php foreach ($divisionStatFields as $field => $label) : ?>
                                                    <label class="form-label small d-block mt-1 mb-0">
                                                        <?php echo Text::_($label); ?>
                                                        <input
                                                            class="form-control form-control-sm"
                                                            type="number"
                                                            name="division_points[<?php echo $id; ?>][<?php echo $divisionId; ?>][<?php echo $field; ?>]"
                                                            value="<?php echo $this->escape((string) $this->model->getProjectTeamDivisionPoints($pid, $id, $divisionId, $field)); ?>"
                                                            onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"
                                                        >
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </details>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $this->escape((string) ($item->playground_name ?? '')); ?>
                                <?php if (empty($item->playground_name)) : ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>

                        <td><?php echo $number('start_points', $item, $i); ?></td>
                        <td><?php echo $number('penalty_points', $item, $i); ?></td>
                        <td>
                            <?php echo $number('matches_finally', $item, $i); ?>
                            <small class="text-muted"><?php echo (int) $this->model->getMatchesCount($pid, $id); ?> <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_GAMES'); ?></small>
                        </td>
                        <td><?php echo $number('points_finally', $item, $i); ?></td>
                        <td><?php echo $number('neg_points_finally', $item, $i); ?></td>
                        <td><?php echo $number('won_finally', $item, $i); ?></td>
                        <td><?php echo $number('draws_finally', $item, $i); ?></td>
                        <td><?php echo $number('lost_finally', $item, $i); ?></td>
                        <td><?php echo $number('homegoals_finally', $item, $i); ?></td>
                        <td><?php echo $number('guestgoals_finally', $item, $i); ?></td>
                        <td><?php echo $number('diffgoals_finally', $item, $i); ?></td>

                        <?php foreach (['is_in_score', 'use_finally', 'champion'] as $field) : ?>
                            <td>
                                <?php echo HTMLHelper::_(
                                    'select.genericlist',
                                    $yesNo,
                                    $field . $id,
                                    'class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $i . '\').checked=true"',
                                    'value',
                                    'text',
                                    (int) ($item->{$field} ?? 0)
                                ); ?>
                            </td>
                        <?php endforeach; ?>

                        <td>
                            <input class="form-control form-control-sm text-center" type="number" min="0" max="40"
                                   name="finaltablerank<?php echo $id; ?>" value="<?php echo (int) ($item->finaltablerank ?? 0); ?>"
                                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true">
                        </td>
                        <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', (int) $item->published, $i, 'projectteams.', true, 'cb'); ?></td>
                        <td class="text-center"><?php echo $id; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($this->pagination) : ?>
        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>

    <input type="hidden" name="pid" value="<?php echo $pid; ?>">
    <input type="hidden" name="season_id" value="<?php echo (int) $this->season_id; ?>">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php echo $this->assignModal; ?>
<?php echo $this->changeTeamsModal; ?>
