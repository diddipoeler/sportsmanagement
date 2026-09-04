<?php
/**
 * Native Joomla 5/6 projects list rows.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$statusLabels = [
    1 => Text::_('JPUBLISHED'),
    0 => Text::_('JUNPUBLISHED'),
    2 => Text::_('JARCHIVED'),
    -2 => Text::_('JTRASHED'),
];
?>
<div class="table-responsive" id="editcell">
    <table class="table table-striped align-middle" id="projectslist">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_NAME_OF_PROJECT', 'p.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUE', 'l.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_COUNTRY', 'l.country', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON', 's.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE', 'st.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP', 'ag.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTTYPE', 'p.project_type', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_GAMES'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_ROUND'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_DIVISION'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_USER_FIELD'); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'p.published', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'p.ordering', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'p.id', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?></th>
            <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$this->items) : ?>
            <tr><td colspan="20" class="text-center py-4"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php else : ?>
        <?php foreach ($this->items as $index => $item) :
            $id = (int) $item->id;
            $checkedOut = (int) ($item->checked_out ?? 0);
            $isCheckedOut = $checkedOut > 0 && $checkedOut !== (int) $this->user->id;
            $disabled = $isCheckedOut ? ' disabled' : '';
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
            $panelUrl = Route::_(
                'index.php?option=com_sportsmanagement&task=project.edit&layout=panel&pid=' . $id
                . '&stid=' . (int) $item->sports_type_id . '&id=' . $id
            );
            $editUrl = Route::_('index.php?option=com_sportsmanagement&task=project.edit&id=' . $id);
            $teamsUrl = Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $id . '&id=' . $id);
            $roundsUrl = Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $id);
            $divisionsUrl = Route::_('index.php?option=com_sportsmanagement&view=divisions&pid=' . $id);
            $matchesUrl = Route::_(
                'index.php?option=com_sportsmanagement&view=matches&pid=' . $id
                . '&rid=' . (int) ($item->current_round ?? 0)
            );
            $roundCount = $this->projectData->getRoundsCount($id);
            $matchCount = $this->projectData->getMatchesCount($id);
            $divisionCount = $this->projectData->getDivisionsCount($id);
            ?>
            <tr>
                <td><?php echo $this->pagination->getRowOffset($index); ?></td>
                <td>
                    <?php echo HTMLHelper::_('grid.id', $index, $id, $isCheckedOut); ?>
                    <?php if ($checkedOut > 0) : ?>
                        <span class="badge bg-warning text-dark" title="<?php echo htmlspecialchars((string) ($item->checked_out_time ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars((string) ($item->editor ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($canEdit && !$isCheckedOut) : ?>
                        <a href="<?php echo $panelUrl; ?>"><?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php else : ?>
                        <?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                    <input<?php echo $disabled; ?> class="form-control form-control-sm mt-1" type="text"
                           name="new_project_name<?php echo $id; ?>"
                           value="<?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?>"
                           onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                    <small><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', htmlspecialchars((string) $item->alias, ENT_QUOTES, 'UTF-8')); ?></small>
                    <?php if ($canEdit && !$isCheckedOut) : ?>
                        <div><a class="small" href="<?php echo $editUrl; ?>"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_EDIT_DETAILS'); ?></a></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['league'],
                        'league' . $id,
                        $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"',
                        'id',
                        'name',
                        (int) $item->league_id
                    ); ?>
                </td>
                <td><?php echo htmlspecialchars((string) ($item->country ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <?php echo htmlspecialchars((string) ($item->season ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    <br />
                    <?php if ($this->model->existcurrentseason($this->season_ids, (int) $item->league_id)) : ?>
                        <span class="badge bg-success"><?php echo Text::_('JYES'); ?></span>
                    <?php else : ?>
                        <span class="badge bg-secondary"><?php echo Text::_('JNO'); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo Text::_((string) ($item->sportstype ?? '')); ?>
                    <br />
                    <label class="small">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_LIVE_UPDATE'); ?>
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            $this->lists['yesno'],
                            'project_live_update' . $id,
                            $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"',
                            'value',
                            'text',
                            (int) ($item->project_live_update ?? 0)
                        ); ?>
                    </label>
                </td>
                <td>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['agegroup'],
                        'agegroup' . $id,
                        $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"',
                        'value',
                        'text',
                        (int) ($item->agegroup_id ?? 0)
                    ); ?>
                    <label class="small mt-1 d-block">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_SETTINGS_PROJECTTEAMS_QUICKADD'); ?>
                        <?php echo HTMLHelper::_('select.genericlist', $this->lists['yesno'], 'fast_projektteam' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->fast_projektteam ?? 0)); ?>
                    </label>
                    <label class="small mt-1 d-block">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_USE_LEAGUECHAMPION'); ?>
                        <?php echo HTMLHelper::_('select.genericlist', $this->lists['yesno'], 'use_leaguechampion' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->use_leaguechampion ?? 0)); ?>
                    </label>
                </td>
                <td>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['project_type'],
                        'project_type' . $id,
                        $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"',
                        'value',
                        'text',
                        (string) $item->project_type
                    ); ?>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['mastertemplates'],
                        'master_template' . $id,
                        $disabled . ' class="form-select form-select-sm mt-1" onchange="document.getElementById(\'cb' . $index . '\').checked=true"',
                        'value',
                        'text',
                        (int) ($item->master_template ?? 0)
                    ); ?>
                    <input<?php echo $disabled; ?> class="form-control form-control-sm mt-1" type="text"
                           name="cr_project<?php echo $id; ?>"
                           value="<?php echo htmlspecialchars((string) ($item->cr_project ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                </td>
                <td class="text-center">
                    <?php
                    $picture = (string) ($item->picture ?? '');
                    $absolute = $picture !== '' ? JPATH_SITE . '/' . ltrim($picture, '/') : '';
                    if ($picture !== '' && is_file($absolute)) : ?>
                        <img src="<?php echo Uri::root() . ltrim($picture, '/'); ?>" alt="" style="max-height:45px;max-width:70px" />
                    <?php else : ?>
                        <span class="badge bg-secondary"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE'); ?></span>
                    <?php endif; ?>
                    <div class="mt-1">
                        <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
                           href="index.php?option=com_media&path=local-images:/com_sportsmanagement/database/projectimages/<?php echo $id; ?>">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTSPICTURE'); ?>
                        </a>
                    </div>
                </td>
                <td>
                    <?php if ((int) ($item->current_round ?? 0) > 0) : ?>
                        <a href="<?php echo $matchesUrl; ?>"><?php echo $matchCount; ?></a>
                    <?php else : ?>
                        <?php echo $matchCount; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?php echo $teamsUrl; ?>"><?php echo (int) ($item->proteams ?? 0); ?></a>
                    <?php if ((int) ($item->notassign ?? 0) > 0) : ?>
                        <br /><span class="badge bg-warning text-dark"><?php echo (int) $item->notassign; ?></span>
                    <?php endif; ?>
                </td>
                <td><a href="<?php echo $roundsUrl; ?>"><?php echo $roundCount; ?></a></td>
                <td><a href="<?php echo $divisionsUrl; ?>"><?php echo $divisionCount; ?></a></td>
                <td>
                    <?php if (!empty($item->user_field)) : ?>
                        <?php echo $item->user_field; ?>
                    <?php endif; ?>
                    <?php if ((int) $this->state->get('filter.userfields', 0) > 0 && !empty($item->user_field_id)) : ?>
                        <input class="form-control form-control-sm mt-1" type="text"
                               name="user_field<?php echo $id; ?>"
                               value="<?php echo htmlspecialchars((string) ($item->user_fieldvalue ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                               onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                        <input type="hidden" name="user_field_id<?php echo $id; ?>" value="<?php echo (int) $item->user_field_id; ?>" />
                    <?php endif; ?>
                </td>
                <td><span class="badge bg-secondary"><?php echo $statusLabels[(int) $item->published] ?? (int) $item->published; ?></span></td>
                <td><?php echo (int) ($item->ordering ?? 0); ?></td>
                <td><?php echo $id; ?></td>
                <td><?php echo htmlspecialchars((string) ($item->modified ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars((string) ($item->username ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="20"><?php echo $this->pagination->getListFooter(); ?></td>
        </tr>
        </tfoot>
    </table>
</div>
