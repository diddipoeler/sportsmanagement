<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage seasons
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;




$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
            <tr>
                <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
                <th width="20">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                </th>
                <th width="">&nbsp;</th>
                <th>
<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_SEASONS_NAME', 's.name', $this->sortDirection, $this->sortColumn); ?>
                </th>
                <th width="" class="nowrap center">
<?php
echo HTMLHelper::_('grid.sort', 'JSTATUS', 's.published', $this->sortDirection, $this->sortColumn);
?>
                </th>

                <th width="">
<?php
echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 's.ordering', $this->sortDirection, $this->sortColumn);
echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'seasons.saveorder');
?>
                </th>
                <th width="">
<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 's.id', $this->sortDirection, $this->sortColumn); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="100%" class="center">
<?php echo $this->pagination->getListFooter(); ?>
<?php echo $this->pagination->getResultsCounter(); ?>
                </td>
            </tr></tfoot>
        <tbody>
<?php
$k = 0;
for ($i = 0, $n = count($this->items); $i < $n; $i++) {
    $row = & $this->items[$i];
    $link = Route::_('index.php?option=com_sportsmanagement&task=season.edit&id=' . $row->id);

    $assignteams = Route::_('index.php?option=com_sportsmanagement&tmpl=component&view=teams&layout=assignteams&season_id=' . $row->id);
    $assignpersons = Route::_('index.php?option=com_sportsmanagement&tmpl=component&view=players&layout=assignpersons&season_id=' . $row->id);
    $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
    $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
    $checked = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'seasons.', $canCheckin);
    $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement.season.' . $row->id) && $canCheckin;
    ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td class="center">
                <?php
                echo $this->pagination->getRowOffset($i);
                ?>
                    </td>
                    <td class="center">
                        <?php
                        echo HTMLHelper::_('grid.id', $i, $row->id);
                        ?>
                    </td>
                        <?php
                        if ($this->table->isCheckedOut($this->user->get('id'), $row->checked_out)) {
                            $inputappend = ' disabled="disabled"';
                            ?><td class="center">&nbsp;</td><?php
                        } else {
                            $inputappend = '';
                            ?>
                            <td class="center" nowrap="nowrap">
                            <?php
                            $image = 'teams.png';
                            $title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_ASSIGN_TEAM');
                            echo sportsmanagementHelper::getBootstrapModalImage('assignteams' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/' . $image, $title, '20',  $assignteams, $this->modalwidth, $this->modalheight);
                            $image = 'players.png';
                            $title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_ASSIGN_PERSON');
                            echo sportsmanagementHelper::getBootstrapModalImage('assignperson' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/' . $image, $title, '20', $assignpersons, $this->modalwidth, $this->modalheight);
                            ?>                          
                            </td>
                            <?php
                        }
                        ?>
                    <td>
                        <?php if ($row->checked_out) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'seasons.', $canCheckin); ?>
                        <?php endif; ?>
                    <?php if ($canEdit) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=season.edit&id=' . (int) $row->id); ?>">
                            <?php echo $this->escape($row->name); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($row->name); ?>
                        <?php endif; ?>


                            <?php //echo $checked; ?>

                        <?php //echo $row->name; ?>
                        <p class="smallsub">
    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?></p>
                    </td>
                    <td class="center">
                        <div class="btn-group">
                        <?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'seasons.', $canChange, 'cb'); ?>
                            <?php
                            // Create dropdown items and render the dropdown list.
                            if ($canChange) {
                                HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'seasons');
                                HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'seasons');
                                echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
                            }
                            ?>
                        </div>
                    </td>  
                    <td class="order">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'seasons.orderup', 'JLIB_HTML_MOVE_UP', 's.ordering'); ?>
                        </span>
                        <span>
    <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'seasons.orderdown', 'JLIB_HTML_MOVE_DOWN', 's.ordering'); ?>
    <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                        </span>
                        <input    type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled ?>
                               class="form-control form-control-inline" style="text-align: center" />
                    </td>
                    <td class="center"><?php echo $row->id; ?></td>
                </tr>
                            <?php
                            $k = 1 - $k;
}
                        ?>
        </tbody>
    </table>
</div>
