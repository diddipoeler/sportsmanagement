<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = (string) $this->state->get('list.ordering', 'pl.lastname');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$context = [
    'layout' => $this->assignclub ? 'assignpersonsclub' : 'assignpersons',
    'tmpl' => 'component',
    'pid' => $this->project_id,
    'project_id' => $this->project_id,
    'project_team_id' => $this->project_team_id,
    'team_id' => $this->team_id,
    'season_id' => $this->season_id,
    'persontype' => $this->persontype,
    'whichview' => $this->whichview,
    'assignclub' => $this->assignclub ? 1 : 0,
];
$positionNames = [];
foreach ($this->positionOptions as $option) {
    $positionNames[(int) $option->value] = (string) $option->text;
}
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=players&' . http_build_query($context)); ?>" method="post" name="adminForm" id="adminForm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <strong><?php echo $this->escape($this->prj_name); ?></strong>
            <?php if ($this->team_name !== '') : ?> — <?php echo $this->escape($this->team_name); ?><?php endif; ?>
            <div class="small text-muted">
                <?php echo $this->persontype === 2 ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE') : Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE'); ?>
                <?php if ($this->assignclub) : ?> · <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN_CLUB'); ?><?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="Joomla.submitform('players.assign', this.form);">
                <?php echo Text::_('JAPPLY'); ?>
            </button>
            <button type="button" class="btn btn-secondary" onclick="Joomla.submitform('players.close', this.form);">
                <?php echo Text::_('JCANCEL'); ?>
            </button>
        </div>
    </div>

    <?php if ($this->filterForm) : ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
            <tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_L_NAME', 'pl.lastname', $listDirn, $listOrder); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_F_NAME', 'pl.firstname', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_N_NAME'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BIRTHDAY', 'pl.birthday', $listDirn, $listOrder); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY', 'pl.country', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_POSITION'); ?></th>
                <th class="text-center"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$this->items) : ?>
                <tr><td colspan="8" class="text-center text-muted"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
            <?php endif; ?>
            <?php foreach ($this->items as $i => $item) :
                if ((string) ($item->firstname ?? '') === '!Unknown' && (string) ($item->lastname ?? '') === '!Player') {
                    continue;
                }
                $id = (int) $item->id;
                ?>
                <tr>
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $id); ?></td>
                    <td><strong><?php echo $this->escape((string) ($item->lastname ?? '')); ?></strong></td>
                    <td><?php echo $this->escape((string) ($item->firstname ?? '')); ?></td>
                    <td><?php echo $this->escape((string) ($item->nickname ?? '')); ?></td>
                    <td><?php echo $this->escape(($item->birthday ?? '') === '0000-00-00' ? '' : (string) ($item->birthday ?? '')); ?></td>
                    <td><?php echo $this->escape((string) ($item->country ?? '')); ?></td>
                    <td><?php echo $this->escape($positionNames[(int) ($item->position_id ?? 0)] ?? ''); ?></td>
                    <td class="text-center"><?php echo $id; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($this->pagination) : ?>
        <?php echo $this->pagination->getListFooter(); ?>
    <?php endif; ?>

    <?php foreach ($context as $name => $value) : ?>
        <input type="hidden" name="<?php echo $this->escape($name); ?>" value="<?php echo $this->escape((string) $value); ?>">
    <?php endforeach; ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($listOrder); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($listDirn); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
