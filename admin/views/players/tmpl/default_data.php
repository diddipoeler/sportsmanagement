<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$user = $this->app->getIdentity();
$params = ComponentHelper::getParams($this->option);
$showRegistration = (bool) $params->get('backend_show_players_knvbnr');
$showAgegroup = (bool) $params->get('backend_show_players_agegroup');
?>
<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead>
    <tr>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
        <th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_F_NAME', 'pl.firstname', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_N_NAME', 'pl.nickname', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_L_NAME', 'pl.lastname', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_BIRTHDAY', 'pl.birthday', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_DEATHDAY'); ?></th>
        <?php if ($showRegistration) : ?>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSON_REGISTRATION_NUMBER', 'pl.knvbnr', $this->sortDirection, $this->sortColumn); ?></th>
        <?php endif; ?>
        <?php if ($showAgegroup) : ?>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP', 'ag.name', $this->sortDirection, $this->sortColumn); ?></th>
        <?php endif; ?>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY', 'pl.country', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_POSITION', 'pl.position_id', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'pl.published', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'pl.id', $this->sortDirection, $this->sortColumn); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $i => $item) : ?>
        <?php
        if ($item->firstname === '!Unknown' && $item->lastname === '!Player') {
            continue;
        }
        $id = (int) $item->id;
        $checkedOut = (int) ($item->checked_out ?? 0);
        $canEdit = $user->authorise('core.edit', 'com_sportsmanagement');
        $canChange = $user->authorise('core.edit.state', 'com_sportsmanagement.player.' . $id)
            && ($checkedOut === 0 || $checkedOut === (int) $user->id);
        $disabled = $this->assign ? ' disabled' : '';
        $mark = "document.getElementById('cb{$i}').checked=true";
        $picture = trim((string) ($item->picture ?? ''));
        $imageUrl = $picture !== '' ? Uri::root() . ltrim($picture, '/') : '';
        ?>
        <tr>
            <td><?php echo $this->pagination->getRowOffset($i); ?></td>
            <td>
                <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $id; ?>"
                       onclick="Joomla.isChecked(this.checked);">
            </td>
            <td>
                <?php if ($canEdit && !$this->assign) : ?>
                    <a class="d-block mb-1" href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=player.edit&id=' . $id); ?>">
                        <?php echo $this->escape(trim($item->firstname . ' ' . $item->lastname)); ?>
                    </a>
                <?php endif; ?>
                <input class="form-control form-control-sm" type="text" name="firstname<?php echo $id; ?>"
                       value="<?php echo $this->escape((string) $item->firstname); ?>" onchange="<?php echo $mark; ?>"<?php echo $disabled; ?>>
            </td>
            <td>
                <input class="form-control form-control-sm" type="text" name="nickname<?php echo $id; ?>"
                       value="<?php echo $this->escape((string) $item->nickname); ?>" onchange="<?php echo $mark; ?>"<?php echo $disabled; ?>>
            </td>
            <td>
                <input class="form-control form-control-sm" type="text" name="lastname<?php echo $id; ?>"
                       value="<?php echo $this->escape((string) $item->lastname); ?>" onchange="<?php echo $mark; ?>"<?php echo $disabled; ?>>
            </td>
            <td class="text-center">
                <?php if ($imageUrl !== '') : ?>
                    <img src="<?php echo $this->escape($imageUrl); ?>" alt="" style="max-width:48px;max-height:48px" loading="lazy">
                <?php endif; ?>
                <?php if (!$this->assign) : ?>
                    <?php
                    $selectUrl = 'index.php?option=com_sportsmanagement&view=imagelist&player_id=' . $id
                        . '&imagelist=1&asset=com_sportsmanagement&folder=persons&tmpl=component&type=persons&fieldname=picture';
                    echo sportsmanagementHelper::getBootstrapModalImage(
                        'select' . $id,
                        '',
                        Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),
                        '20',
                        Uri::base() . $selectUrl,
                        $this->modalwidth,
                        $this->modalheight
                    );
                    ?>
                <?php endif; ?>
            </td>
            <td>
                <input class="form-control form-control-sm" type="date" name="birthday<?php echo $id; ?>"
                       value="<?php echo $this->escape(($item->birthday ?? '') === '0000-00-00' ? '' : (string) $item->birthday); ?>"
                       onchange="<?php echo $mark; ?>"<?php echo $disabled; ?>>
            </td>
            <td>
                <input class="form-control form-control-sm" type="date" name="deathday<?php echo $id; ?>"
                       value="<?php echo $this->escape(($item->deathday ?? '') === '0000-00-00' ? '' : (string) $item->deathday); ?>"
                       onchange="<?php echo $mark; ?>"<?php echo $disabled; ?>>
            </td>
            <?php if ($showRegistration) : ?>
                <td>
                    <input class="form-control form-control-sm" type="text" name="knvbnr<?php echo $id; ?>"
                           value="<?php echo $this->escape((string) ($item->knvbnr ?? '')); ?>" onchange="<?php echo $mark; ?>"<?php echo $disabled; ?>>
                </td>
            <?php endif; ?>
            <?php if ($showAgegroup) : ?>
                <td>
                    <?php echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['agegroup'],
                        'agegroup' . $id,
                        'class="form-select form-select-sm" onchange="' . $mark . '"' . $disabled,
                        'value',
                        'text',
                        (int) ($item->agegroup_id ?? 0)
                    ); ?>
                </td>
            <?php else : ?>
                <input type="hidden" name="agegroup<?php echo $id; ?>" value="<?php echo (int) ($item->agegroup_id ?? 0); ?>">
            <?php endif; ?>
            <td>
                <?php echo HTMLHelper::_(
                    'select.genericlist',
                    $this->lists['nation'],
                    'country' . $id,
                    'class="form-select form-select-sm" onchange="' . $mark . '"' . $disabled,
                    'value',
                    'text',
                    (string) ($item->country ?? '')
                ); ?>
            </td>
            <td>
                <?php echo HTMLHelper::_(
                    'select.genericlist',
                    $this->lists['positions'],
                    'position' . $id,
                    'class="form-select form-select-sm" onchange="' . $mark . '"' . $disabled,
                    'value',
                    'text',
                    (int) ($item->position_id ?? 0)
                ); ?>
            </td>
            <td class="text-center">
                <?php if ($this->assign) : ?>
                    <?php echo (int) $item->published ? Text::_('JPUBLISHED') : Text::_('JUNPUBLISHED'); ?>
                <?php else : ?>
                    <?php echo HTMLHelper::_('jgrid.published', (int) $item->published, $i, 'players.', $canChange); ?>
                <?php endif; ?>
            </td>
            <td><?php echo $id; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="14"><?php echo $this->pagination->getListFooter(); ?></td>
    </tr>
    </tfoot>
</table>
</div>
