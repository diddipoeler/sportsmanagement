<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 'obj.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>
<div class="table-responsive">
    <table class="table table-striped" id="statisticsList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_NAME', 'obj.name', $listDirn, $listOrder); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_ABBREV', 'obj.short', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_ICON'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_SPORTSTYPE', 'obj.sports_type_id', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_NOTE'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_TYPE'); ?></th>
                <th class="w-10 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'obj.published', $listDirn, $listOrder); ?></th>
                <th class="w-10 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $listDirn, $listOrder); ?></th>
                <th class="w-5 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'obj.id', $listDirn, $listOrder); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $canEdit = $user->authorise('core.edit', 'com_sportsmanagement');
            $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement');
            $icon = trim((string) ($item->icon ?? ''));
            $iconUrl = $icon !== '' ? Uri::root() . ltrim($icon, '/') : '';
        ?>
            <tr>
                <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id); ?></td>
                <td>
                    <?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=statistic.edit&id=' . (int) $item->id); ?>">
                            <?php echo $this->escape((string) $item->name); ?>
                        </a>
                    <?php else : ?>
                        <?php echo $this->escape((string) $item->name); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo $this->escape((string) ($item->short ?? '')); ?></td>
                <td class="text-center">
                    <?php if ($iconUrl !== '') : ?>
                        <img src="<?php echo $this->escape($iconUrl); ?>" alt="" style="max-height:32px;max-width:48px">
                    <?php endif; ?>
                </td>
                <td><?php echo Text::_((string) ($item->sportstype ?? '')); ?></td>
                <td><?php echo $this->escape((string) ($item->note ?? '')); ?></td>
                <td><?php echo Text::_((string) ($item->class ?? '')); ?></td>
                <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', (int) $item->published, $i, 'statistics.', $canChange, 'cb'); ?></td>
                <td class="text-center">
                    <input type="number" name="order[]" value="<?php echo (int) $item->ordering; ?>" class="form-control form-control-sm text-center" style="width:6rem">
                </td>
                <td class="text-center"><?php echo (int) $item->id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php echo $this->pagination->getListFooter(); ?>
