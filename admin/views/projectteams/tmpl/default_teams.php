<?php
/** Native Joomla 5/6 project-team table rows. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
$allowProjectChange = (bool) ComponentHelper::getParams('com_sportsmanagement')
    ->get('show_option_projectteam_change', false);
$divisionMode = (string) $this->project->project_type === 'DIVISIONS_LEAGUE';
$statFields = [
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
<div class="table-responsive" id="editcell_projectteams">
    <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_LEGEND', '<i>' . $this->project->name . '</i>'); ?></legend>
    <table class="table table-striped align-middle">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TEAMNAME'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CLUBNAME'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_COUNTRY'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_MANAGE_PERSONNEL'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_ADMIN'); ?></th>
            <?php if ($divisionMode) : ?>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DIVISION'); ?></th>
            <?php endif; ?>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PICTURE'); ?></th>
            <?php foreach ($statFields as $label) : ?>
                <th><?php echo Text::_($label); ?></th>
            <?php endforeach; ?>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_IS_IN_SCORE'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_USE_FINALLY'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHAMPION'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_FINALTABLERANK'); ?></th>
            <th>STID</th>
            <th>TID / CID</th>
            <th><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->projectteam as $index => $item) :
            $id = (int) $item->id;
            $teamId = (int) ($item->team_id ?? 0);
            $clubId = (int) ($item->club_id ?? 0);
            $checkedOut = (int) ($item->checked_out ?? 0);
            $isCheckedOut = $checkedOut > 0 && $checkedOut !== (int) $this->user->id;
            $disabled = $isCheckedOut ? ' disabled' : '';
            $editUrl = Route::_(
                'index.php?option=com_sportsmanagement&task=projectteam.edit&id=' . $id
                . '&pid=' . (int) $this->project_id . '&team_id=' . $teamId
            );
            $playersUrl = Route::_(
                'index.php?option=com_sportsmanagement&view=teamplayers&persontype=1&project_team_id=' . $id
                . '&team_id=' . $teamId . '&pid=' . (int) $this->project_id
                . '&season_team_id=' . (int) ($item->season_team_id ?? 0)
            );
            $staffUrl = Route::_(
                'index.php?option=com_sportsmanagement&view=teamplayers&persontype=2&project_team_id=' . $id
                . '&team_id=' . $teamId . '&pid=' . (int) $this->project_id
                . '&season_team_id=' . (int) ($item->season_team_id ?? 0)
            );
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
                        <a href="<?php echo $editUrl; ?>"><?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php else : ?>
                        <?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                    <input type="hidden" name="team_id<?php echo $id; ?>" value="<?php echo $teamId; ?>" />
                    <input<?php echo $disabled; ?> class="form-control form-control-sm mt-1" type="text"
                           name="teamname<?php echo $id; ?>" value="<?php echo htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8'); ?>"
                           onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                    <?php if ($allowProjectChange) : ?>
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            $this->projectsbyleagueseason,
                            'new_project_id' . $id,
                            $disabled . ' class="form-select form-select-sm mt-1" onchange="document.getElementById(\'cb' . $index . '\').checked=true"',
                            'value',
                            'text',
                            (int) $this->project_id
                        ); ?>
                    <?php else : ?>
                        <input type="hidden" name="new_project_id<?php echo $id; ?>" value="<?php echo (int) $this->project_id; ?>" />
                    <?php endif; ?>
                </td>
                <td>
                    <input type="hidden" name="club_id<?php echo $id; ?>" value="<?php echo $clubId; ?>" />
                    <input<?php echo $disabled; ?> class="form-control form-control-sm" type="text"
                           name="clubname<?php echo $id; ?>" value="<?php echo htmlspecialchars((string) ($item->clubname ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                    <input<?php echo $disabled; ?> class="form-control form-control-sm mt-1" type="text"
                           name="location<?php echo $id; ?>" value="<?php echo htmlspecialchars((string) ($item->location ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CITY'); ?>"
                           onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                    <input<?php echo $disabled; ?> class="form-control form-control-sm mt-1" type="text"
                           name="zipcode<?php echo $id; ?>" value="<?php echo htmlspecialchars((string) ($item->zipcode ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_POSTAL_CODE'); ?>"
                           onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                    <input<?php echo $disabled; ?> class="form-control form-control-sm mt-1" type="text"
                           name="address<?php echo $id; ?>" value="<?php echo htmlspecialchars((string) ($item->address ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_ADDRESS'); ?>"
                           onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                    <div class="row g-1 mt-1">
                        <div class="col">
                            <input<?php echo $disabled; ?> class="form-control form-control-sm" type="text"
                                   name="founded_year<?php echo $id; ?>" value="<?php echo htmlspecialchars((string) ($item->founded_year ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_FOUNDED_YEAR'); ?>"
                                   onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                        </div>
                        <div class="col">
                            <input<?php echo $disabled; ?> class="form-control form-control-sm" type="text"
                                   name="unique_id<?php echo $id; ?>" value="<?php echo htmlspecialchars((string) ($item->unique_id ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_UNIQUE_ID'); ?>"
                                   onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                        </div>
                    </div>
                </td>
                <td>
                    <?php echo htmlspecialchars((string) ($item->country ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <td class="text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo $playersUrl; ?>">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_PLAYERS'); ?> (<?php echo (int) ($item->playercount ?? 0); ?>)
                    </a>
                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo $staffUrl; ?>">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_STAFF'); ?> (<?php echo (int) ($item->staffcount ?? 0); ?>)
                    </a>
                </td>
                <td>
                    <?php echo htmlspecialchars((string) ($item->editor ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    <?php if (!empty($item->email)) : ?>
                        <br /><a href="mailto:<?php echo htmlspecialchars((string) $item->email, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars((string) $item->email, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endif; ?>
                </td>
                <?php if ($divisionMode) : ?>
                    <td>
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            $this->lists['divisions'],
                            'division_id' . $id,
                            $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"',
                            'value',
                            'text',
                            (int) ($item->division_id ?? 0)
                        ); ?>
                        <details class="mt-2">
                            <summary><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_DIVISION'); ?></summary>
                            <?php foreach ($this->divisions as $division) :
                                $divisionId = (int) ($division->value ?? 0);
                                if ($divisionId <= 0) {
                                    continue;
                                } ?>
                                <div class="border rounded p-2 mt-1">
                                    <strong><?php echo htmlspecialchars((string) $division->text, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <?php foreach ($statFields as $field => $label) : ?>
                                        <label class="form-label small d-block mb-0 mt-1">
                                            <?php echo Text::_($label); ?>
                                            <input<?php echo $disabled; ?> class="form-control form-control-sm" type="number"
                                                   name="division_points[<?php echo $id; ?>][<?php echo $divisionId; ?>][<?php echo $field; ?>]"
                                                   value="<?php echo htmlspecialchars((string) $this->model->getProjectTeamDivisionPoints($this->project_id, $id, $divisionId, $field), ENT_QUOTES, 'UTF-8'); ?>"
                                                   onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </details>
                    </td>
                <?php endif; ?>
                <td class="text-center">
                    <?php
                    $picture = (string) ($item->picture ?? '');
                    $absolute = $picture !== '' ? JPATH_SITE . '/' . ltrim($picture, '/') : '';
                    if ($picture !== '' && is_file($absolute)) : ?>
                        <img src="<?php echo Uri::root() . ltrim($picture, '/'); ?>" alt="" style="max-height:40px;max-width:60px" />
                    <?php else : ?>
                        <span class="badge bg-secondary"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_NO_IMAGE'); ?></span>
                    <?php endif; ?>
                    <br />
                    <?php echo htmlspecialchars((string) ($item->playground_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                </td>
                <?php foreach ($statFields as $field => $label) : ?>
                    <td>
                        <input<?php echo $disabled; ?> class="form-control form-control-sm" type="number"
                               name="<?php echo $field . $id; ?>"
                               value="<?php echo htmlspecialchars((string) ($item->{$field} ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                               onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                        <?php if ($field === 'matches_finally') : ?>
                            <small><?php echo $this->model->getMatchesCount($this->project_id, $id); ?> <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_GAMES'); ?></small>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <td>
                    <?php echo HTMLHelper::_('select.genericlist', $this->lists['is_in_score'], 'is_in_score' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->is_in_score ?? 0)); ?>
                </td>
                <td>
                    <?php echo HTMLHelper::_('select.genericlist', $this->lists['use_finally'], 'use_finally' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->use_finally ?? 0)); ?>
                </td>
                <td>
                    <?php echo HTMLHelper::_('select.genericlist', $this->lists['use_finally'], 'champion' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->champion ?? 0)); ?>
                </td>
                <td>
                    <?php echo HTMLHelper::_('select.genericlist', $this->lists['finaltablerank'], 'finaltablerank' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->finaltablerank ?? 0)); ?>
                </td>
                <td><?php echo (int) ($item->season_team_id ?? 0); ?></td>
                <td><?php echo $teamId; ?> / <?php echo $clubId; ?></td>
                <td><?php echo $id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="<?php echo 20 + count($statFields) + ($divisionMode ? 1 : 0); ?>">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
