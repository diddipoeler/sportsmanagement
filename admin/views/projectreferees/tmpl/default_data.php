<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectreferees
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$this->saveOrder = $this->sortColumn === 'pref.ordering';
$saveOrderingUrl = '';

if ($this->saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_sportsmanagement&task=' . $this->view
        . '.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<legend>
    <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE2', '<i>' . $this->project->name . '</i>'); ?>
</legend>

<div class="table-responsive" id="editcell">
    <table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th width="20">&nbsp;</th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PREF_NAME', 'p.lastname', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="20"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_PID'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_IMAGE'); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_POS'), 'pref.project_position_id', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'pref.published', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="10%">
                <?php
                echo HTMLHelper::_('grid.sort', Text::_('JGRID_HEADING_ORDERING'), 'pref.ordering', $this->sortDirection, $this->sortColumn);
                echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'projectreferees.saveorder');
                ?>
            </th>
            <th width="5%"><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'p.id', $this->sortDirection, $this->sortColumn); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
            <td colspan="4"><?php echo $this->pagination->getResultsCounter(); ?></td>
        </tr>
        </tfoot>
        <tbody<?php if ($this->saveOrder && !empty($this->items)) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>"<?php endif; ?>>
        <?php foreach ($this->items as $this->count_i => $this->item) : ?>
            <?php
            $this->dragable_group = 'data-dragable-group="none"';
            $link = Route::_(
                'index.php?option=com_sportsmanagement&task=projectreferee.edit&id=' . (int) $this->item->id
                . '&pid=' . (int) $this->item->project_id
                . '&person_id=' . (int) $this->item->person_id
            );
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
            $userId = (int) $this->user->get('id');
            $canCheckin = $this->user->authorise('core.manage', 'com_checkin')
                || (int) $this->item->checked_out === $userId
                || (int) $this->item->checked_out === 0;
            $canChange = $this->user->authorise(
                'core.edit.state',
                'com_sportsmanagement.projectreferee.' . (int) $this->item->id
            ) && $canCheckin;
            $inputappend = '';
            ?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center"><?php echo $this->pagination->getRowOffset($this->count_i); ?></td>
                <td class="center"><?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?></td>
                <td class="center">
                    <?php if ($this->item->checked_out) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $userId, $this->item->checked_out_time, 'projectreferees.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit && !$this->item->checked_out) : ?>
                        <a href="<?php echo $link; ?>">
                            <?php
                            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_EDIT_DETAILS');
                            $imageAttributes = ['title' => $imageTitle];
                            echo HTMLHelper::_(
                                'image',
                                'administrator/components/com_sportsmanagement/assets/images/edit.png',
                                $imageTitle,
                                $imageAttributes
                            );
                            ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td><?php echo sportsmanagementHelper::formatName(null, $this->item->firstname, $this->item->nickname, $this->item->lastname, 1); ?></td>
                <td class="center"><?php echo (int) $this->item->person_id; ?></td>
                <td class="center">
                    <?php
                    if ($this->item->picture === '') {
                        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_NO_IMAGE');
                        echo HTMLHelper::_(
                            'image',
                            'administrator/components/com_sportsmanagement/assets/images/delete.png',
                            $imageTitle,
                            ['title' => $imageTitle]
                        );
                    } elseif ($this->item->picture === sportsmanagementHelper::getDefaultPlaceholder('player')) {
                        $imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_DEFAULT_IMAGE');
                        echo HTMLHelper::_(
                            'image',
                            'administrator/components/com_sportsmanagement/assets/images/information.png',
                            $imageTitle,
                            ['title' => $imageTitle]
                        );
                    } else {
                        $playerName = sportsmanagementHelper::formatName(
                            null,
                            $this->item->firstname,
                            $this->item->nickname,
                            $this->item->lastname,
                            0
                        );
                        $picture = Uri::root() . $this->item->picture;
                        echo sportsmanagementHelper::getBootstrapModalImage(
                            'collapseModalplayerpicture' . (int) $this->item->id,
                            $picture,
                            $playerName,
                            '20',
                            $picture
                        );
                    }
                    ?>
                </td>
                <td class="center">
                    <?php
                    $selectedValue = (int) $this->item->project_position_id;
                    $append = $selectedValue === 0 ? ' style="background-color:#FFCCCC"' : '';

                    if ($append !== '') :
                    ?>
                        <script>document.getElementById('cb<?php echo $this->count_i; ?>').checked = true;</script>
                    <?php endif; ?>
                    <?php
                    echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['project_position_id'],
                        'project_position_id' . (int) $this->item->id,
                        $inputappend . 'class="inputbox" size="1" onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"' . $append,
                        'value',
                        'text',
                        $selectedValue
                    );
                    ?>
                </td>
                <td class="center">
                    <div class="btn-group">
                        <?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'projectreferees.', $canChange, 'cb'); ?>
                        <?php
                        if ($canChange) {
                            HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'projectreferees');
                            HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'projectreferees');
                            echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->item->lastname));
                        }
                        ?>
                    </div>
                </td>
                <td class="order" id="defaultdataorder">
                    <?php echo $this->loadTemplate('data_order'); ?>
                </td>
                <td class="center"><?php echo (int) $this->item->id; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
