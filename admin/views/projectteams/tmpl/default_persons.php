<?php
/** Native Joomla 5/6 project-person table rows. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
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
<div class="table-responsive" id="editcell_projectpersons">
    <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_LEGEND', '<i>' . $this->project->name . '</i>'); ?></legend>
    <table class="table table-striped align-middle">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_TEAMNAME'); ?></th>
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
            <th>PID</th>
            <th><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->projectteam as $index => $item) :
            $id = (int) $item->id;
            $personId = (int) ($item->team_id ?? 0);
            $checkedOut = (int) ($item->checked_out ?? 0);
            $isCheckedOut = $checkedOut > 0 && $checkedOut !== (int) $this->user->id;
            $disabled = $isCheckedOut ? ' disabled' : '';
            $editUrl = Route::_(
                'index.php?option=com_sportsmanagement&task=projectteam.edit&id=' . $id
                . '&pid=' . (int) $this->project_id . '&team_id=' . $personId
            );
            ?>
            <tr>
                <td><?php echo $this->pagination->getRowOffset($index); ?></td>
                <td>
                    <?php echo HTMLHelper::_('grid.id', $index, $id, $isCheckedOut); ?>
                    <?php if ($checkedOut > 0) : ?>
                        <span class="badge bg-warning text-dark">
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
                    <?php if (!empty($item->seasonname)) : ?>
                        <br /><small><?php echo htmlspecialchars((string) $item->seasonname, ENT_QUOTES, 'UTF-8'); ?></small>
                    <?php endif; ?>
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
                </td>
                <?php foreach ($statFields as $field => $label) : ?>
                    <td>
                        <input<?php echo $disabled; ?> class="form-control form-control-sm" type="number"
                               name="<?php echo $field . $id; ?>"
                               value="<?php echo htmlspecialchars((string) ($item->{$field} ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                               onchange="document.getElementById('cb<?php echo $index; ?>').checked=true" />
                    </td>
                <?php endforeach; ?>
                <td><?php echo HTMLHelper::_('select.genericlist', $this->lists['is_in_score'], 'is_in_score' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->is_in_score ?? 0)); ?></td>
                <td><?php echo HTMLHelper::_('select.genericlist', $this->lists['use_finally'], 'use_finally' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->use_finally ?? 0)); ?></td>
                <td><?php echo HTMLHelper::_('select.genericlist', $this->lists['use_finally'], 'champion' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->champion ?? 0)); ?></td>
                <td><?php echo HTMLHelper::_('select.genericlist', $this->lists['finaltablerank'], 'finaltablerank' . $id, $disabled . ' class="form-select form-select-sm" onchange="document.getElementById(\'cb' . $index . '\').checked=true"', 'value', 'text', (int) ($item->finaltablerank ?? 0)); ?></td>
                <td><?php echo $personId; ?></td>
                <td><?php echo $id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="<?php echo 10 + count($statFields) + ($divisionMode ? 1 : 0); ?>">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
