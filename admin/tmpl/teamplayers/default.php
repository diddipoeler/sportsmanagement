<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$listOrder = (string) $this->state->get('list.ordering', 'ppl.lastname');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$queryContext = http_build_query(array_filter(
    $this->contextParams,
    static fn ($value): bool => (int) $value !== 0
));
$isPlayer = $this->personType === 1;
$user = $this->getApplication()->getIdentity();

$renderOptions = static function (array $options, int $selected): string {
    $html = '<option value="0">'
        . htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_FUNCTION'), ENT_QUOTES, 'UTF-8')
        . '</option>';

    foreach ($options as $option) {
        $value = (int) $option->value;
        $html .= '<option value="' . $value . '"'
            . ($selected === $value ? ' selected' : '') . '>'
            . htmlspecialchars((string) $option->text, ENT_QUOTES, 'UTF-8')
            . '</option>';
    }

    return $html;
};
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=teamplayers&' . $queryContext); ?>"
      method="post" name="adminForm" id="adminForm">
    <?php if ($this->filterForm) : ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <?php endif; ?>

    <div class="alert alert-info py-2">
        <?php echo $this->escape((string) $this->project->name); ?> —
        <?php echo $this->escape((string) $this->teamContext->team_name); ?>
        <span class="ms-2 text-muted">
            <?php echo $isPlayer
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE')
                : Text::_('COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE'); ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-striped align-middle" id="teamplayersList">
            <thead>
            <tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_NAME', 'ppl.lastname', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_IMAGE'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_S_NAME'); ?></th>
                <?php if ($isPlayer) : ?>
                    <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_JERSEYNUMBER', 'tp.jerseynumber', $listDirn, $listOrder); ?></th>
                    <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_MARKET_VALUE', 'tp.market_value', $listDirn, $listOrder); ?></th>
                    <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_MARKET_VALUE_TEXT'); ?></th>
                    <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_TT_STARTPOINTS', 'tp.tt_startpoints', $listDirn, $listOrder); ?></th>
                <?php endif; ?>
                <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_STATUS_PROJECT'); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'tp.published', $listDirn, $listOrder); ?></th>
                <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'tp.id', $listDirn, $listOrder); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) :
                $relationId = (int) ($item->tpid ?? $item->id ?? 0);
                $personId = (int) ($item->person_id ?? 0);
                $checkedOut = (int) ($item->checked_out ?? 0);
                $canEdit = $user->authorise('core.edit', 'com_sportsmanagement')
                    && ($checkedOut === 0 || $checkedOut === (int) $user->id);
                $name = trim((string) ($item->firstname ?? '') . ' ' . (string) ($item->lastname ?? ''));
                $editUrl = Route::_(
                    'index.php?option=com_sportsmanagement&task=teamplayer.edit&id=' . $relationId
                    . '&person_id=' . $personId
                    . '&project_team_id=' . (int) ($this->contextParams['project_team_id'] ?? 0)
                    . '&pid=' . (int) ($this->contextParams['pid'] ?? 0)
                    . '&team_id=' . (int) ($this->contextParams['team_id'] ?? 0)
                    . '&season_team_id=' . (int) ($this->contextParams['season_team_id'] ?? 0)
                    . '&season_id=' . (int) ($this->contextParams['season_id'] ?? 0)
                    . '&persontype=' . $this->personType
                );
                $picture = trim((string) ($item->season_picture ?? $item->picture ?? ''));
                ?>
                <tr>
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $relationId); ?></td>
                    <td>
                        <?php if ($canEdit) : ?>
                            <a href="<?php echo $editUrl; ?>"><strong><?php echo $this->escape($name); ?></strong></a>
                        <?php else : ?>
                            <strong><?php echo $this->escape($name); ?></strong>
                        <?php endif; ?>
                        <?php if (!empty($item->nickname)) : ?>
                            <div class="small text-muted"><?php echo $this->escape((string) $item->nickname); ?></div>
                        <?php endif; ?>
                        <?php if ($checkedOut > 0 && !empty($item->editor)) : ?>
                            <div class="small text-muted"><?php echo $this->escape((string) $item->editor); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $this->escape((string) ($item->country ?? '')); ?></td>
                    <td>
                        <?php if ($picture !== '') : ?>
                            <img src="<?php echo $this->escape(Uri::root() . ltrim($picture, '/')); ?>"
                                 alt="" loading="lazy" style="max-width:48px;max-height:48px">
                        <?php endif; ?>
                    </td>
                    <td>
                        <select class="form-select form-select-sm"
                                name="project_position_id[<?php echo $relationId; ?>]"
                                onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;Joomla.isChecked(true);">
                            <?php echo $renderOptions($this->positionOptions, (int) ($item->project_position_id ?? 0)); ?>
                        </select>
                    </td>
                    <?php if ($isPlayer) : ?>
                        <td><input class="form-control form-control-sm text-center" type="number" min="0"
                                   name="jerseynumber[<?php echo $relationId; ?>]"
                                   value="<?php echo (int) ($item->jerseynumber ?? 0); ?>"
                                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;Joomla.isChecked(true);"></td>
                        <td><input class="form-control form-control-sm" type="number" min="0"
                                   name="market_value[<?php echo $relationId; ?>]"
                                   value="<?php echo (int) ($item->market_value ?? 0); ?>"
                                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;Joomla.isChecked(true);"></td>
                        <td><input class="form-control form-control-sm" type="text" maxlength="50"
                                   name="market_text[<?php echo $relationId; ?>]"
                                   value="<?php echo $this->escape((string) ($item->market_text ?? '')); ?>"
                                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;Joomla.isChecked(true);"></td>
                        <td><input class="form-control form-control-sm text-center" type="number"
                                   name="tt_startpoints[<?php echo $relationId; ?>]"
                                   value="<?php echo (int) ($item->tt_startpoints ?? 0); ?>"
                                   onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;Joomla.isChecked(true);"></td>
                    <?php endif; ?>
                    <td>
                        <select class="form-select form-select-sm"
                                name="project_published[<?php echo $relationId; ?>]"
                                onchange="document.getElementById('cb<?php echo $i; ?>').checked=true;Joomla.isChecked(true);">
                            <option value="1"<?php echo (int) ($item->project_published ?? 1) === 1 ? ' selected' : ''; ?>><?php echo Text::_('JPUBLISHED'); ?></option>
                            <option value="0"<?php echo (int) ($item->project_published ?? 1) === 0 ? ' selected' : ''; ?>><?php echo Text::_('JUNPUBLISHED'); ?></option>
                        </select>
                    </td>
                    <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', (int) ($item->published ?? 0), $i, 'teamplayers.', true, 'cb'); ?></td>
                    <td class="text-center"><?php echo $relationId; ?><div class="small text-muted">P: <?php echo $personId; ?></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($this->pagination) : ?>
        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>

    <?php foreach ($this->contextParams as $name => $value) : ?>
        <input type="hidden" name="<?php echo $this->escape($name); ?>" value="<?php echo (int) $value; ?>">
    <?php endforeach; ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php echo $this->assignModal; ?>
<?php echo $this->assignClubModal; ?>
