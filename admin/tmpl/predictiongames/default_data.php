<?php
/**
 * Native Joomla 5/6 prediction games list rows.
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

$listOrder = (string) $this->state->get('list.ordering', 'pre.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>

<?php if ($this->prediction_id > 0 && $this->pred_project) : ?>
    <div class="alert alert-info py-2">
        <?php echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE2',
            '<strong>' . $this->escape((string) $this->pred_project->name) . '</strong>'
        ); ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle" id="predictiongamesList">
        <thead>
            <tr>
                <th class="w-1 text-center">#</th>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NAME', 'pre.name', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PROJ_COUNT'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_ADMIN_COUNT'); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'pre.published', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USERS'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_GROUPS'); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TEMPLATES'); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'pre.id', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?></th>
                <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$this->items) : ?>
            <tr>
                <td colspan="12" class="text-center py-4">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GAMES'); ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $id = (int) $item->id;
            $checkedOut = (int) ($item->checked_out ?? 0);
            $checkedOutByOther = $checkedOut > 0 && $checkedOut !== (int) $this->user->id;
            $canCheckin = $this->user->authorise('core.manage', 'com_checkin')
                || !$checkedOutByOther;
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement') && !$checkedOutByOther;
            $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement') && !$checkedOutByOther;
            $editLink = Route::_('index.php?option=com_sportsmanagement&task=predictiongame.edit&id=' . $id);
            $selectLink = Route::_('index.php?option=com_sportsmanagement&view=predictiongames&prediction_id=' . $id);
            $membersLink = Route::_('index.php?option=com_sportsmanagement&view=predictionmembers&prediction_id=' . $id);
            $groupsLink = Route::_('index.php?option=com_sportsmanagement&view=predictiongroups&prediction_id=' . $id);
            $templatesLink = Route::_('index.php?option=com_sportsmanagement&view=predictiontemplates&prediction_id=' . $id);
            ?>
            <tr>
                <td class="text-center">
                    <?php echo $this->pagination ? $this->pagination->getRowOffset($i) : $i + 1; ?>
                </td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('grid.id', $i, $id, !$canCheckin); ?>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($checkedOutByOther) : ?>
                            <span class="icon-lock" aria-hidden="true"></span>
                        <?php endif; ?>
                        <a href="<?php echo $selectLink; ?>" class="fw-semibold">
                            <?php echo $this->escape((string) $item->name); ?>
                        </a>
                        <?php if ($canEdit) : ?>
                            <a
                                href="<?php echo $editLink; ?>"
                                class="btn btn-sm btn-outline-secondary"
                                title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_EDIT_DETAILS')); ?>"
                                aria-label="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_EDIT_DETAILS')); ?>"
                            >
                                <span class="icon-edit" aria-hidden="true"></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="text-center">
                    <a href="<?php echo $selectLink; ?>">
                        <?php echo (int) ($this->projectCounts[$id] ?? 0); ?>
                    </a>
                </td>
                <td class="text-center"><?php echo (int) ($this->adminCounts[$id] ?? 0); ?></td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('jgrid.published', (int) ($item->published ?? 0), $i, 'predictiongames.', $canChange, 'cb'); ?>
                </td>
                <td class="text-center">
                    <a
                        href="<?php echo $membersLink; ?>"
                        title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USERS')); ?>"
                        aria-label="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USERS')); ?>"
                    ><span class="icon-users" aria-hidden="true"></span></a>
                </td>
                <td class="text-center">
                    <a
                        href="<?php echo $groupsLink; ?>"
                        title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_GROUPS')); ?>"
                        aria-label="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_GROUPS')); ?>"
                    ><span class="icon-list" aria-hidden="true"></span></a>
                </td>
                <td class="text-center">
                    <a
                        href="<?php echo $templatesLink; ?>"
                        title="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TEMPLATES')); ?>"
                        aria-label="<?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TEMPLATES')); ?>"
                    ><span class="icon-options" aria-hidden="true"></span></a>
                </td>
                <td class="text-center"><?php echo $id; ?></td>
                <td><?php echo $this->escape((string) ($item->modified ?? '')); ?></td>
                <td><?php echo $this->escape((string) ($item->username ?? '')); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($this->pagination) : ?>
    <div class="mt-3"><?php echo $this->pagination->getListFooter(); ?></div>
<?php endif; ?>
