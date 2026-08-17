<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 'po.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=positions'); ?>" method="post" name="adminForm" id="adminForm">
  <?php if ($this->filterForm) echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
  <div class="table-responsive">
    <table class="table table-striped" id="positionsList">
      <thead>
        <tr>
          <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
          <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_NAME', 'po.name', $listDirn, $listOrder); ?></th>
          <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'); ?></th>
          <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SPORT_TYPE'); ?></th>
          <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_PERSON_TYPE'); ?></th>
          <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_EVENTS'); ?></th>
          <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_STATISTICS'); ?></th>
          <th class="w-10 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'po.published', $listDirn, $listOrder); ?></th>
          <th class="w-5 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'po.id', $listDirn, $listOrder); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
          <?php $canEdit = $user->authorise('core.edit', 'com_sportsmanagement'); $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement'); ?>
          <tr>
            <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
            <td>
              <?php if ($canEdit) : ?>
                <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=position.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape(Text::_($item->name)); ?></a>
              <?php else : ?>
                <?php echo $this->escape(Text::_($item->name)); ?>
              <?php endif; ?>
            </td>
            <td>
              <select class="form-select form-select-sm" name="parent_id<?php echo (int) $item->id; ?>">
                <option value="0"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'); ?></option>
                <?php foreach ($this->parents as $parent) : if ((int) $parent->id === (int) $item->id) continue; ?>
                  <option value="<?php echo (int) $parent->id; ?>"<?php echo (int) $item->parent_id === (int) $parent->id ? ' selected' : ''; ?>><?php echo $this->escape(Text::_($parent->name)); ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><?php echo $this->escape(Text::_($item->sportstype)); ?></td>
            <td><?php echo $this->escape(Text::_('COM_SPORTSMANAGEMENT_F_' . match ((int) $item->persontype) { 2 => 'TEAM_STAFF', 3 => 'REFEREES', 4 => 'CLUB_STAFF', default => 'PLAYERS' })); ?></td>
            <td class="text-center"><?php echo (int) $item->countEvents; ?></td>
            <td class="text-center"><?php echo (int) $item->countStats; ?></td>
            <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'positions.', $canChange, 'cb'); ?></td>
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
