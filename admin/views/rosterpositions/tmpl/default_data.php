<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage rosterpositions
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;



?>
<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
            <tr>
                <th width="1%" class="center  hidden-phone"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
                <th width="1%" class="center">
                    <input  type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                </th>

                <th width="10%" class="nowrap">
                    <?php
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_NAME', 'obj.name', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="5%" class="nowrap">
                    <?php
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_SHORT_NAME', 'obj.alias', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>					
                <th width="1%" class="nowrap center hidden-phone">
                    <?php
                    echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_COUNTRY', 'obj.country', $this->sortDirection, $this->sortColumn);
                    ?>
                </th>
                <th width="5%" class="nowrap center hidden-phone">
                    <?php
                    echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn);
                    echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'rosterpositions.saveorder');
                    ?>
                </th>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn); ?>
                </th>
            </tr>
        </thead>
        <tfoot><tr><td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
        <tbody>
            <?php
            $k = 0;
            for ($i = 0, $n = count($this->items); $i < $n; $i++) {
                $row = & $this->items[$i];
                $link = Route::_('index.php?option=com_sportsmanagement&task=rosterposition.edit&id=' . $row->id);
                $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
                $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
                $checked = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'rosterpositions.', $canCheckin);
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td class="center hidden-phone">
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
                    $inputappend = '';
                    ?>
                    <td >
                        <?php if ($row->checked_out) : ?>
                            <?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'rosterpositions.', $canCheckin); ?>
                        <?php endif; ?>
                        <?php if ($canEdit) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=rosterposition.edit&id=' . (int) $row->id); ?>">
                                <?php echo $this->escape($row->name); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($row->name); ?>
                        <?php endif; ?>
                        <?php //echo $checked;  ?>
                        <?php //echo $row->name; ?>
                        <div class="small">
                            <?php //echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?></div>
                    </td>
                    <td><?php echo $row->short_name; ?></td>
                    <td class="center hidden-phone"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
                    <td class="order center hidden-phone">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'rosterpositions.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?>
                        </span>
                        <span>
                            <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'rosterpositions.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?>
                            <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                        </span>
                        <input	type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                               class="form-control form-control-inline" style="text-align: center" />
                    </td>
                    <td class="center hidden-phone"><?php echo $row->id; ?></td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
        </tbody>
    </table>
</div>

