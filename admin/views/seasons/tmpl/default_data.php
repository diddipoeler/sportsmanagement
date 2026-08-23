<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage seasons
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

$this->saveOrder = $this->sortColumn === 's.ordering';
$saveOrderingUrl = '';

if ($this->saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_sportsmanagement&task=' . $this->view
        . '.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<div class="table-responsive" id="editcell">
    <table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th>&nbsp;</th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_SEASONS_NAME', 's.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="nowrap center"><?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 's.published', $this->sortDirection, $this->sortColumn); ?></th>
            <th>
                <?php
                echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 's.ordering', $this->sortDirection, $this->sortColumn);
                echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'seasons.saveorder');
                ?>
            </th>
            <th><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 's.id', $this->sortDirection, $this->sortColumn); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="100%" class="center">
                <?php echo $this->pagination->getListFooter(); ?>
                <?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody<?php if ($this->saveOrder && !empty($this->items)) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>"<?php endif; ?>>
        <?php foreach ($this->items as $this->count_i => $this->item) : ?>
            <?php
            $this->dragable_group = 'data-dragable-group="none"';
            $assignTeams = Route::_(
                'index.php?option=com_sportsmanagement&tmpl=component&view=teams&layout=assignteams&season_id=' . (int) $this->item->id
            );
            $assignPersons = Route::_(
                'index.php?option=com_sportsmanagement&tmpl=component&view=players&layout=assignpersons&season_id=' . (int) $this->item->id . '&whichview=seasons'
            );
            $userId = (int) $this->user->get('id');
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
            $canCheckin = $this->user->authorise('core.manage', 'com_checkin')
                || (int) $this->item->checked_out === $userId
                || (int) $this->item->checked_out === 0;
            $canChange = $this->user->authorise(
                'core.edit.state',
                'com_sportsmanagement.season.' . (int) $this->item->id
            ) && $canCheckin;
            ?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center"><?php echo $this->pagination->getRowOffset($this->count_i); ?></td>
                <td class="center"><?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?></td>
                <?php if ($this->table->isCheckedOut($userId, $this->item->checked_out)) : ?>
                    <td class="center">&nbsp;</td>
                <?php else : ?>
                    <td class="center" nowrap="nowrap">
                        <?php
                        echo sportsmanagementHelper::getBootstrapModalImage(
                            'assignteams' . (int) $this->item->id,
                            Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/teams.png',
                            Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_ASSIGN_TEAM'),
                            '20',
                            $assignTeams,
                            $this->modalwidth,
                            $this->modalheight
                        );
                        echo sportsmanagementHelper::getBootstrapModalImage(
                            'assignperson' . (int) $this->item->id,
                            Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/players.png',
                            Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_ASSIGN_PERSON'),
                            '20',
                            $assignPersons,
                            $this->modalwidth,
                            $this->modalheight
                        );
                        ?>
                    </td>
                <?php endif; ?>
                <td>
                    <?php if ($this->item->checked_out) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'seasons.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=season.edit&id=' . (int) $this->item->id); ?>">
                            <?php echo $this->escape($this->item->name); ?>
                        </a>
                    <?php else : ?>
                        <?php echo $this->escape($this->item->name); ?>
                    <?php endif; ?>
                    <p class="smallsub"><?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
                <td class="center">
                    <div class="btn-group">
                        <?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'seasons.', $canChange, 'cb'); ?>
                        <?php
                        if ($canChange) {
                            HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'seasons');
                            HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'seasons');
                            echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->item->name));
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
