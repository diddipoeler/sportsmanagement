<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 't.name');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$renderOptions = static function (array $options, $selected): string {
    $html = '';
    foreach ($options as $option) {
        $value = (string) $option->value;
        $html .= '<option value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . ((string) $selected === $value ? ' selected' : '') . '>' . htmlspecialchars((string) $option->text, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
};
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=teams' . ($this->clubId > 0 ? '&club_id=' . $this->clubId : '')); ?>" method="post" name="adminForm" id="adminForm">
  <?php if ($this->filterForm) echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
  <div class="table-responsive"><table class="table table-striped" id="teamsList">
    <thead><tr>
      <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
      <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_NAME', 't.name', $listDirn, $listOrder); ?></th>
      <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CLUBNAME', 'c.name', $listDirn, $listOrder); ?></th>
      <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_COUNTRY', 'c.country', $listDirn, $listOrder); ?></th>
      <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP', 'ag.name', $listDirn, $listOrder); ?></th>
      <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE', 'st.name', $listDirn, $listOrder); ?></th>
      <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_WEBSITE'); ?></th>
      <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_EMAIL'); ?></th>
      <th class="w-10 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 't.published', $listDirn, $listOrder); ?></th>
      <th class="w-5 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 't.id', $listDirn, $listOrder); ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($this->items as $i => $item) : $canEdit = $user->authorise('core.edit', 'com_sportsmanagement'); $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement'); ?>
      <tr>
        <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
        <td><?php if ($canEdit) : ?><a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=team.edit&id=' . (int) $item->id . ($this->clubId > 0 ? '&club_id=' . $this->clubId : '')); ?>"><?php echo $this->escape($item->name); ?></a><?php else : echo $this->escape($item->name); endif; ?><div class="small text-muted"><?php echo $this->escape($item->short_name); ?><?php if ($item->info !== '') : ?> · <?php echo $this->escape($item->info); ?><?php endif; ?></div></td>
        <td><?php echo $item->clubname !== null && $item->clubname !== '' ? $this->escape($item->clubname) : '<span class="text-danger">' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_CLUB') . '</span>'; ?></td>
        <td><?php echo $this->escape($item->country); ?></td>
        <td><select class="form-select form-select-sm" name="agegroup<?php echo (int) $item->id; ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"><option value="0"></option><?php echo $renderOptions($this->inlineOptions['agegroups'], $item->agegroup_id); ?></select></td>
        <td><select class="form-select form-select-sm" name="sportstype<?php echo (int) $item->id; ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"><option value="0"></option><?php echo $renderOptions($this->inlineOptions['sportstypes'], $item->sports_type_id); ?></select></td>
        <td class="text-center"><?php if ($item->website) : ?><a href="<?php echo htmlspecialchars((string) $item->website, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><?php echo Text::_('JYES'); ?></a><?php else : echo Text::_('JNO'); endif; ?></td>
        <td class="text-center"><?php echo $item->email ? '<a href="mailto:' . htmlspecialchars((string) $item->email, ENT_QUOTES, 'UTF-8') . '">' . Text::_('JYES') . '</a>' : Text::_('JNO'); ?></td>
        <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'teams.', $canChange, 'cb'); ?></td>
        <td class="text-center"><?php echo (int) $item->id; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php echo $this->pagination->getListFooter(); ?>
  <input type="hidden" name="task" value=""><input type="hidden" name="boxchecked" value="0"><input type="hidden" name="club_id" value="<?php echo (int) $this->clubId; ?>">
  <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>"><input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
  <?php echo HTMLHelper::_('form.token'); ?>
</form>
