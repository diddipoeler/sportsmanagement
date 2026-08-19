<?php
/** SportsManagement tournament tree list rows. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = $this->app->getIdentity();
$hasDivisions = (string) ($this->projectws->project_type ?? '') === 'DIVISIONS_LEAGUE';
$columns = $hasDivisions ? 8 : 7;
?>
<div id="table-responsive">
    <legend>
        <?php echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE',
            '<i>',
            '<i>' . $this->escape((string) ($this->projectws->name ?? '')) . '</i>'
        ); ?>
    </legend>

    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5"><?php echo count($this->items) . '/' . (int) $this->pagination->total; ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_NAME'); ?></th>
            <?php if ($hasDivisions) : ?>
                <th>
                    <?php
                    echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_DIVISION') . '<br>';
                    echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['divisions'],
                        'division',
                        'class="form-select" onchange="window.location.href=window.location.href.split(\'&division=\')[0]+\'&division=\'+this.value"',
                        'value',
                        'text',
                        $this->division
                    );
                    ?>
                </th>
            <?php endif; ?>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_DEPTH'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_HIDE'); ?></th>
            <th><?php echo Text::_('JSTATUS'); ?></th>
            <th><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $row) :
            $checkedOut = (int) ($row->checked_out ?? 0) > 0
                && (int) $row->checked_out !== (int) $user->id;
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="text-center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('grid.id', $i, (int) $row->id, $checkedOut); ?>
                    <?php if (!$checkedOut) : ?>
                        <a href="<?php echo Route::_(
                            'index.php?option=com_sportsmanagement&task=treeto.edit&id=' . (int) $row->id
                            . '&pid=' . (int) $this->project_id
                        ); ?>">
                            <?php echo HTMLHelper::_(
                                'image',
                                'administrator/components/com_sportsmanagement/assets/images/edit.png',
                                Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_DETAILS'),
                                ['title' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_DETAILS')]
                            ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ((int) ($row->leafed ?? 0) === 0) : ?>
                        <a href="<?php echo Route::_(
                            'index.php?option=com_sportsmanagement&task=treetos.genNode&id=' . (int) $row->id
                            . '&pid=' . (int) $this->project_id
                        ); ?>">
                            <?php echo HTMLHelper::_(
                                'image',
                                'administrator/components/com_sportsmanagement/assets/images/update.png',
                                Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_GENERATE'),
                                ['title' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_GENERATE')]
                            ); ?>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo Route::_(
                            'index.php?option=com_sportsmanagement&view=treetonodes&task=treetonode.display'
                            . '&tid=' . (int) $row->id . '&pid=' . (int) $this->project_id
                        ); ?>">
                            <?php echo HTMLHelper::_(
                                'image',
                                'administrator/components/com_sportsmanagement/assets/images/icon-16-Tree.png',
                                Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_TREE'),
                                ['title' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_EDIT_TREE')]
                            ); ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td><?php echo $this->escape((string) $row->name); ?></td>
                <?php if ($hasDivisions) : ?>
                    <td>
                        <?php
                        $attributes = 'class="form-select" onchange="document.getElementById(\'cb' . $i . '\').checked=true; Joomla.isChecked(true);"';
                        echo HTMLHelper::_(
                            'select.genericlist',
                            $this->lists['divisions'],
                            'division_id' . (int) $row->id,
                            $attributes,
                            'value',
                            'text',
                            (int) $row->division_id
                        );
                        ?>
                    </td>
                <?php endif; ?>
                <td class="text-center"><?php echo (int) $row->tree_i; ?></td>
                <td class="text-center"><?php echo $this->escape((string) $row->hide); ?></td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('grid.published', (int) $row->published, $i, 'tick.png', 'publish_x.png', 'treetos.'); ?>
                </td>
                <td class="text-center"><?php echo (int) $row->id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="<?php echo $columns; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
        </tr>
        </tfoot>
    </table>
</div>
