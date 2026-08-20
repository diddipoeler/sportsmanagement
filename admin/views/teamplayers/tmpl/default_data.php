<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$user = $this->app->getIdentity();
$isPlayer = $this->_persontype === 1;
?>
<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead>
    <tr>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
        <th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_NAME', 'ppl.lastname', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY'); ?></th>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_IMAGE'); ?></th>
        <?php if ($isPlayer) : ?>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_SHIRTNR', 'tp.jerseynumber', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_MARKET_VALUE', 'tp.market_value', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_MARKET_TEXT', 'tp.market_text', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_STARTPOINTS'); ?></th>
        <?php endif; ?>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_POS'); ?></th>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_STATUS_PROJECT'); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'tp.published', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $i => $item) : ?>
        <?php
        $relationId = (int) ($item->tpid ?? $item->id ?? 0);
        $personId = (int) ($item->person_id ?? 0);
        $checkedOut = (int) ($item->checked_out ?? 0);
        $canEdit = $user->authorise('core.edit', 'com_sportsmanagement')
            && ($checkedOut === 0 || $checkedOut === (int) $user->id);
        $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement')
            && ($checkedOut === 0 || $checkedOut === (int) $user->id);
        $mark = "document.getElementById('cb{$i}').checked=true;Joomla.isChecked(true)";
        $editUrl = Route::_(
            'index.php?option=com_sportsmanagement&task=teamplayer.edit&id=' . $relationId
            . '&person_id=' . $personId
            . '&project_team_id=' . (int) $this->project_team_id
            . '&pid=' . (int) $this->project_id
            . '&team_id=' . (int) $this->team_id
            . '&season_team_id=' . (int) $this->season_team_id
            . '&season_id=' . (int) $this->season_id
            . '&persontype=' . (int) $this->_persontype
        );
        $name = trim((string) ($item->firstname ?? '') . ' ' . (string) ($item->lastname ?? ''));
        $picture = trim((string) ($item->season_picture ?? ''));
        $pictureUrl = $picture !== '' ? Uri::root() . ltrim($picture, '/') : '';
        ?>
        <tr>
            <td><?php echo $this->pagination->getRowOffset($i); ?></td>
            <td>
                <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $relationId; ?>"
                       onclick="Joomla.isChecked(this.checked);">
            </td>
            <td>
                <?php if ($canEdit) : ?>
                    <a href="<?php echo $editUrl; ?>"><?php echo $this->escape($name); ?></a>
                <?php else : ?>
                    <?php echo $this->escape($name); ?>
                <?php endif; ?>
                <?php if ($checkedOut > 0) : ?>
                    <div class="small text-muted"><?php echo $this->escape((string) ($item->editor ?? '')); ?></div>
                <?php endif; ?>
            </td>
            <td><?php echo $this->escape((string) ($item->country ?? '')); ?></td>
            <td>
                <?php if ($pictureUrl !== '') : ?>
                    <img src="<?php echo $this->escape($pictureUrl); ?>" alt="" loading="lazy" style="max-width:48px;max-height:48px">
                <?php endif; ?>
            </td>
            <?php if ($isPlayer) : ?>
                <td>
                    <input class="form-control form-control-sm" type="number" min="0"
                           name="jerseynumber[<?php echo $relationId; ?>]"
                           value="<?php echo (int) ($item->jerseynumber ?? 0); ?>" onchange="<?php echo $mark; ?>">
                </td>
                <td>
                    <input class="form-control form-control-sm" type="number" min="0"
                           name="market_value[<?php echo $relationId; ?>]"
                           value="<?php echo (int) ($item->market_value ?? 0); ?>" onchange="<?php echo $mark; ?>">
                </td>
                <td>
                    <input class="form-control form-control-sm" type="text" maxlength="50"
                           name="market_text[<?php echo $relationId; ?>]"
                           value="<?php echo $this->escape((string) ($item->market_text ?? '')); ?>" onchange="<?php echo $mark; ?>">
                </td>
                <td>
                    <input class="form-control form-control-sm" type="number"
                           name="tt_startpoints[<?php echo $relationId; ?>]"
                           value="<?php echo (int) ($item->tt_startpoints ?? 0); ?>" onchange="<?php echo $mark; ?>">
                </td>
            <?php else : ?>
                <input type="hidden" name="jerseynumber[<?php echo $relationId; ?>]" value="0">
                <input type="hidden" name="market_value[<?php echo $relationId; ?>]" value="0">
                <input type="hidden" name="market_text[<?php echo $relationId; ?>]" value="">
                <input type="hidden" name="tt_startpoints[<?php echo $relationId; ?>]" value="0">
            <?php endif; ?>
            <td>
                <?php echo HTMLHelper::_(
                    'select.genericlist',
                    $this->lists['project_position_id'],
                    'project_position_id[' . $relationId . ']',
                    'class="form-select form-select-sm" onchange="' . $mark . '"',
                    'value',
                    'text',
                    (int) ($item->project_position_id ?? 0)
                ); ?>
            </td>
            <td>
                <?php echo HTMLHelper::_(
                    'select.genericlist',
                    [
                        HTMLHelper::_('select.option', 1, Text::_('JPUBLISHED')),
                        HTMLHelper::_('select.option', 0, Text::_('JUNPUBLISHED')),
                    ],
                    'project_published[' . $relationId . ']',
                    'class="form-select form-select-sm" onchange="' . $mark . '"',
                    'value',
                    'text',
                    (int) ($item->project_published ?? 1)
                ); ?>
            </td>
            <td class="text-center">
                <?php echo HTMLHelper::_('jgrid.published', (int) ($item->published ?? 0), $i, 'teamplayers.', $canChange); ?>
            </td>
            <td><?php echo $relationId; ?><div class="small text-muted">P: <?php echo $personId; ?></div></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="13"><?php echo $this->pagination->getListFooter(); ?></td>
    </tr>
    </tfoot>
</table>
</div>
