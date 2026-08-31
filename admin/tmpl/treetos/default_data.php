<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 'tt.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$hasDivisions = (string) ($this->project->project_type ?? '') === 'DIVISIONS_LEAGUE';
$columns = $hasDivisions ? 8 : 7;
?>
<div class="table-responsive">
    <?php if (!empty($this->project->name)) : ?>
        <div class="alert alert-info py-2">
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE',
                '<span>',
                '<strong>' . $this->escape((string) $this->project->name) . '</strong>'
            ); ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped align-middle" id="treetosList">
        <thead>
            <tr>
                <th class="w-1 text-center">#</th>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TREETOS_NAME', 'tt.name', $listDirn, $listOrder); ?></th>
                <?php if ($hasDivisions) : ?>
                    <th>
                        <label class="visually-hidden" for="treetos-division-filter">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_DIVISION'); ?>
                        </label>
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            $this->divisions,
                            'division',
                            'id="treetos-division-filter" class="form-select form-select-sm" onchange="this.form.submit();"',
                            'value',
                            'text',
                            $this->division
                        ); ?>
                    </th>
                <?php endif; ?>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TREETOS_DEPTH', 'tt.tree_i', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TREETOS_HIDE', 'tt.hide', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'tt.published', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'tt.id', $listDirn, $listOrder); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$this->items) : ?>
            <tr>
                <td colspan="<?php echo $columns; ?>">
                    <div class="alert alert-info mb-0"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
                </td>
            </tr>
        <?php endif; ?>

        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $id = (int) $item->id;
            $checkedOut = (int) ($item->checked_out ?? 0);
            $checkedOutByOther = $checkedOut > 0 && $checkedOut !== (int) $user->id;
            $canEdit = $user->authorise('core.edit', 'com_sportsmanagement') && !$checkedOutByOther;
            $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement') && !$checkedOutByOther;
            $editLink = Route::_(
                'index.php?option=com_sportsmanagement&task=treeto.edit&id=' . $id . '&pid=' . (int) $this->project_id
            );
            $generateLink = Route::_(
                'index.php?option=com_sportsmanagement&task=treetos.genNode&id=' . $id . '&pid=' . (int) $this->project_id
            );
            $nodesLink = Route::_(
                'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display&tid=' . $id
                . '&pid=' . (int) $this->project_id
            );
            $markChecked = "document.getElementById('cb{$i}').checked=true; Joomla.isChecked(true);";
            ?>
            <tr>
                <td class="text-center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('grid.id', $i, $id, $checkedOutByOther); ?>
                </td>
                <td>
                    <?php if ($canEdit) : ?>
                        <a href="<?php echo $editLink; ?>" class="fw-semibold">
                            <?php echo $this->escape((string) $item->name); ?>
                        </a>
                    <?php else : ?>
                        <span class="fw-semibold"><?php echo $this->escape((string) $item->name); ?></span>
                    <?php endif; ?>

                    <div class="small mt-1">
                        <?php if ((int) ($item->leafed ?? 0) === 0) : ?>
                            <a href="<?php echo $generateLink; ?>">
                                <span class="icon-refresh" aria-hidden="true"></span>
                                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_GENERATE'); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php echo $nodesLink; ?>">
                                <span class="icon-tree-2" aria-hidden="true"></span>
                                <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_TREE'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
                <?php if ($hasDivisions) : ?>
                    <td>
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            $this->divisions,
                            'division_id' . $id,
                            'class="form-select form-select-sm" onchange="' . $markChecked . '"',
                            'value',
                            'text',
                            (int) ($item->division_id ?? 0)
                        ); ?>
                    </td>
                <?php endif; ?>
                <td class="text-center"><?php echo (int) ($item->tree_i ?? 0); ?></td>
                <td class="text-center"><?php echo $this->escape((string) ($item->hide ?? '')); ?></td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('jgrid.published', (int) ($item->published ?? 0), $i, 'treetos.', $canChange, 'cb'); ?>
                </td>
                <td class="text-center"><?php echo $id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
