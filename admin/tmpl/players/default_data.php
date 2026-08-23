<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$params = ComponentHelper::getParams('com_sportsmanagement');
$showRegistration = (bool) $params->get('backend_show_players_knvbnr');
$showAgegroup = (bool) $params->get('backend_show_players_agegroup');
$listOrder = (string) $this->state->get('list.ordering', 'pl.lastname');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
$columnCount = 11 + ($showRegistration ? 1 : 0) + ($showAgegroup ? 1 : 0);
?>
<div class="table-responsive">
    <table class="table table-striped align-middle" id="playersList">
        <thead>
        <tr>
            <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_F_NAME', 'pl.firstname', $listDirn, $listOrder); ?></th>
            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_N_NAME', 'pl.nickname', $listDirn, $listOrder); ?></th>
            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_L_NAME', 'pl.lastname', $listDirn, $listOrder); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?></th>
            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BIRTHDAY', 'pl.birthday', $listDirn, $listOrder); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_DEATHDAY'); ?></th>
            <?php if ($showRegistration) : ?>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSON_REGISTRATION_NUMBER', 'pl.knvbnr', $listDirn, $listOrder); ?></th>
            <?php endif; ?>
            <?php if ($showAgegroup) : ?>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP', 'ag.name', $listDirn, $listOrder); ?></th>
            <?php endif; ?>
            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY', 'pl.country', $listDirn, $listOrder); ?></th>
            <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_POSITION', 'pl.position_id', $listDirn, $listOrder); ?></th>
            <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'pl.published', $listDirn, $listOrder); ?></th>
            <th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'pl.id', $listDirn, $listOrder); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$this->items) : ?>
            <tr><td colspan="<?php echo $columnCount; ?>" class="text-center text-muted"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php endif; ?>
        <?php foreach ($this->items as $i => $item) :
            if ((string) ($item->firstname ?? '') === '!Unknown' && (string) ($item->lastname ?? '') === '!Player') {
                continue;
            }
            $id = (int) $item->id;
            $checkedOut = (int) ($item->checked_out ?? 0);
            $locked = $checkedOut > 0 && $checkedOut !== (int) $this->user->id;
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement') && !$locked;
            $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement') && !$locked;
            $mark = "document.getElementById('cb{$i}').checked=true;Joomla.isChecked(true)";
            $disabled = $locked ? ' disabled' : '';
            $picture = trim((string) ($item->picture ?? ''));
            $picturePath = $picture !== '' ? JPATH_SITE . '/' . ltrim($picture, '/') : '';
            $pictureUrl = $picture !== '' && is_file($picturePath) ? Uri::root() . ltrim($picture, '/') : '';
            ?>
            <tr>
                <td class="text-center">
                    <?php echo HTMLHelper::_('grid.id', $i, $id, $locked); ?>
                    <?php if ($locked) : ?><div class="small text-muted"><?php echo $this->escape((string) ($item->editor ?? '')); ?></div><?php endif; ?>
                </td>
                <td>
                    <?php if ($canEdit) : ?>
                        <a class="d-block mb-1" href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=player.edit&id=' . $id); ?>">
                            <?php echo $this->escape(trim((string) ($item->firstname ?? '') . ' ' . (string) ($item->lastname ?? ''))); ?>
                        </a>
                    <?php endif; ?>
                    <input<?php echo $disabled; ?> class="form-control form-control-sm" type="text" name="firstname<?php echo $id; ?>" value="<?php echo $this->escape((string) ($item->firstname ?? '')); ?>" onchange="<?php echo $mark; ?>">
                </td>
                <td><input<?php echo $disabled; ?> class="form-control form-control-sm" type="text" name="nickname<?php echo $id; ?>" value="<?php echo $this->escape((string) ($item->nickname ?? '')); ?>" onchange="<?php echo $mark; ?>"></td>
                <td><input<?php echo $disabled; ?> class="form-control form-control-sm" type="text" name="lastname<?php echo $id; ?>" value="<?php echo $this->escape((string) ($item->lastname ?? '')); ?>" onchange="<?php echo $mark; ?>"></td>
                <td class="text-center">
                    <?php if ($pictureUrl !== '') : ?>
                        <img src="<?php echo $this->escape($pictureUrl); ?>" alt="" loading="lazy" style="max-width:48px;max-height:48px">
                    <?php endif; ?>
                </td>
                <td><input<?php echo $disabled; ?> class="form-control form-control-sm" type="date" name="birthday<?php echo $id; ?>" value="<?php echo $this->escape(($item->birthday ?? '') === '0000-00-00' ? '' : (string) ($item->birthday ?? '')); ?>" onchange="<?php echo $mark; ?>"></td>
                <td><input<?php echo $disabled; ?> class="form-control form-control-sm" type="date" name="deathday<?php echo $id; ?>" value="<?php echo $this->escape(($item->deathday ?? '') === '0000-00-00' ? '' : (string) ($item->deathday ?? '')); ?>" onchange="<?php echo $mark; ?>"></td>
                <?php if ($showRegistration) : ?>
                    <td><input<?php echo $disabled; ?> class="form-control form-control-sm" type="text" name="knvbnr<?php echo $id; ?>" value="<?php echo $this->escape((string) ($item->knvbnr ?? '')); ?>" onchange="<?php echo $mark; ?>"></td>
                <?php endif; ?>
                <?php if ($showAgegroup) : ?>
                    <td><?php echo HTMLHelper::_('select.genericlist', $this->agegroupOptions, 'agegroup' . $id, $disabled . ' class="form-select form-select-sm" onchange="' . $mark . '"', 'value', 'text', (int) ($item->agegroup_id ?? 0)); ?></td>
                <?php endif; ?>
                <td>
                    <?php if (!$showAgegroup) : ?><input type="hidden" name="agegroup<?php echo $id; ?>" value="<?php echo (int) ($item->agegroup_id ?? 0); ?>"><?php endif; ?>
                    <?php echo HTMLHelper::_('select.genericlist', $this->countryOptions, 'country' . $id, $disabled . ' class="form-select form-select-sm" onchange="' . $mark . '"', 'value', 'text', (string) ($item->country ?? '')); ?>
                </td>
                <td><?php echo HTMLHelper::_('select.genericlist', $this->positionOptions, 'position' . $id, $disabled . ' class="form-select form-select-sm" onchange="' . $mark . '"', 'value', 'text', (int) ($item->position_id ?? 0)); ?></td>
                <td class="text-center"><?php echo HTMLHelper::_('jgrid.published', (int) ($item->published ?? 0), $i, 'players.', $canChange, 'cb'); ?></td>
                <td class="text-center"><?php echo $id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
