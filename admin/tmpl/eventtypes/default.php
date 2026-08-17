<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 'obj.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=eventtypes'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if ($this->filterForm) : ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped" id="eventtypesList">
            <thead>
                <tr>
                    <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_EVENTS_STANDARD_NAME_OF_EVENT', 'obj.name', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_EVENTS_SPORTSTYPE', 'st.name', $listDirn, $listOrder); ?></th>
                    <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_ICON'); ?></th>
                    <th class="w-10 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'obj.published', $listDirn, $listOrder); ?></th>
                    <th class="w-10 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $listDirn, $listOrder); ?></th>
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
                            <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=eventtype.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->name); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($item->name); ?>
                        <?php endif; ?>
                        <?php if (!empty($item->alias)) : ?><div class="small text-muted"><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></div><?php endif; ?>
                    </td>
                    <td><?php echo Text::_((string) $item->sportstype); ?></td>
                    <td class="text-center">
                        <?php if (!empty($item->icon)) : ?><img src="<?php echo Uri::root() . ltrim($this->escape($item->icon), '/'); ?>" alt="" style="max-height:32px;max-width:48px"><?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'eventtypes.', $canChange, 'cb'); ?></td>
                    <td class="text-center"><input type="number" name="order[]" value="<?php echo (int) $item->ordering; ?>" class="form-control form-control-sm text-center" style="width:6rem"></td>
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
