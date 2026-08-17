<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 'obj.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=extrafields'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if ($this->filterForm) : ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped" id="extrafieldsList">
            <thead>
                <tr>
                    <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_NAME', 'obj.name', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_EXT_FIELD_BACKEND', 'obj.template_backend', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_EXT_FIELD_FRONTEND', 'obj.template_frontend', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_EXT_FIELD_VIEW_BACKEND', 'obj.views_backend', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_EXT_FIELD_FIELDTYP', 'obj.field_type', $listDirn, $listOrder); ?></th>
                    <th class="w-10 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'obj.published', $listDirn, $listOrder); ?></th>
                    <th class="w-5 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'obj.id', $listDirn, $listOrder); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) :
                $canEdit = $user->authorise('core.edit', 'com_sportsmanagement');
                $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement');
            ?>
                <tr>
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                    <td>
                        <?php if ($canEdit) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=extrafield.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->name); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($item->name); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $this->escape($item->template_backend); ?></td>
                    <td><?php echo $this->escape($item->template_frontend); ?></td>
                    <td><?php echo $this->escape($item->views_backend); ?></td>
                    <td><?php echo $this->escape($item->field_type ?: (string) $item->fieldtyp); ?></td>
                    <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'extrafields.', $canChange, 'cb'); ?></td>
                    <td class="text-center"><?php echo (int) $item->id; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php echo $this->pagination->getListFooter(); ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
