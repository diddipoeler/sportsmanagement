<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = (string) $this->state->get('list.ordering', 'ppl.lastname');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$queryContext = http_build_query(array_filter($this->contextParams, static fn($value) => (int) $value !== 0));
$renderOptions = static function (array $options, int $selected): string {
    $html = '<option value="0">' . htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_FUNCTION'), ENT_QUOTES, 'UTF-8') . '</option>';
    foreach ($options as $option) {
        $value = (int) $option->value;
        $html .= '<option value="' . $value . '"' . ($selected === $value ? ' selected' : '') . '>' . htmlspecialchars((string) $option->text, ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
};
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=teamplayers&' . $queryContext); ?>" method="post" name="adminForm" id="adminForm">
    <?php if ($this->filterForm) echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <div class="alert alert-info py-2">
        <?php echo $this->escape((string) $this->project->name); ?> — <?php echo $this->escape((string) $this->teamContext->team_name); ?>
        <span class="ms-2 text-muted"><?php echo $this->personType === 2 ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE') : Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE'); ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-striped align-middle" id="teamplayersList">
            <thead><tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_NAME', 'ppl.lastname', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_S_NAME'); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_JERSEYNUMBER', 'tp.jerseynumber', $listDirn, $listOrder); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_MARKET_VALUE', 'tp.market_value', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_MARKET_VALUE_TEXT'); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_TT_STARTPOINTS', 'tp.tt_startpoints', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'tp.published', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'tp.id', $listDirn, $listOrder); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr>
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id); ?></td>
                    <td>
                        <strong><?php echo $this->escape(trim((string) $item->lastname . ', ' . (string) $item->firstname, ', ')); ?></strong>
                        <?php if (!empty($item->nickname)) : ?><div class="small text-muted"><?php echo $this->escape((string) $item->nickname); ?></div><?php endif; ?>
                        <?php if (!empty($item->country)) : ?><div class="small text-muted"><?php echo $this->escape((string) $item->country); ?></div><?php endif; ?>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" name="project_position_id[<?php echo (int) $item->id; ?>]" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true">
                            <?php echo $renderOptions($this->positionOptions, (int) ($item->project_position_id ?? 0)); ?>
                        </select>
                        <select class="form-select form-select-sm mt-1" name="project_published[<?php echo (int) $item->id; ?>]" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true">
                            <option value="1"<?php echo (int) $item->project_published === 1 ? ' selected' : ''; ?>><?php echo Text::_('JPUBLISHED'); ?></option>
                            <option value="0"<?php echo (int) $item->project_published === 0 ? ' selected' : ''; ?>><?php echo Text::_('JUNPUBLISHED'); ?></option>
                        </select>
                    </td>
                    <td><input class="form-control form-control-sm text-center" type="number" min="0" name="jerseynumber[<?php echo (int) $item->id; ?>]" value="<?php echo (int) $item->jerseynumber; ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"></td>
                    <td><input class="form-control form-control-sm" type="number" min="0" name="market_value[<?php echo (int) $item->id; ?>]" value="<?php echo (int) $item->market_value; ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"></td>
                    <td><input class="form-control form-control-sm" type="text" maxlength="50" name="market_text[<?php echo (int) $item->id; ?>]" value="<?php echo $this->escape((string) $item->market_text); ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"></td>
                    <td><input class="form-control form-control-sm text-center" type="number" name="tt_startpoints[<?php echo (int) $item->id; ?>]" value="<?php echo (int) $item->tt_startpoints; ?>" onchange="document.getElementById('cb<?php echo $i; ?>').checked=true"></td>
                    <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', (int) $item->published, $i, 'teamplayers.', true, 'cb'); ?></td>
                    <td class="text-center"><?php echo (int) $item->id; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php echo $this->pagination->getListFooter(); ?>
    <?php foreach ($this->contextParams as $name => $value) : ?><input type="hidden" name="<?php echo $this->escape($name); ?>" value="<?php echo (int) $value; ?>"><?php endforeach; ?>
    <input type="hidden" name="task" value=""><input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>"><input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
