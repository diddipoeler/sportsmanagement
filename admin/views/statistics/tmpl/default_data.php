<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statistics
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;


?>

<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20">
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
            </th>

            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_NAME', 'obj.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="20">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_ABBREV', 'obj.short', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_ICON', 'obj.icon', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_SPORTSTYPE', 'obj.sports_type_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_NOTE'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_TYPE'); ?></th>
            <th width="1%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'obj.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn); ?>
				<?php echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'statistics.saveorder'); ?>
            </th>

            <th width="5%">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="12"><?php echo $this->pagination->getListFooter(); ?></td>
        </tr>
        </tfoot>
        <tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$row        =& $this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=statistic.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'statistics.', $canCheckin);
			$published  = HTMLHelper::_('grid.published', $row, $i, 'tick.png', 'publish_x.png', 'statistics.');
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

                <td class="center">
					<?php if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'statistics.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=statistic.edit&id=' . (int) $row->id); ?>">
							<?php echo $this->escape($row->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($row->name); ?>
					<?php endif; ?>



					<?php //echo $checked;
					?>

					<?php //echo $row->name;
					?>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->name)); ?></p>
                </td>

                <td><?php echo $row->short; ?></td>
                <td class="center">
					<?php
					$picture = JPATH_SITE . DIRECTORY_SEPARATOR . $row->icon;
					$desc    = Text::_($row->name);
					echo sportsmanagementHelper::getPictureThumb($picture, $desc, 0, 21, 4);
					?>
                </td>
                <td class="center">
					<?php
					echo Text::_(sportsmanagementHelper::getSportsTypeName($row->sports_type_id));
					?>
                </td>
                <td><?php echo $row->note; ?></td>
                <td><?php echo Text::_($row->class); ?></td>
                <td class="center"><?php echo $published; ?>
                </td>
                <td class="order">
                    <span><?php echo $this->pagination->orderUpIcon($i, $i > 0, 'statistics.orderup', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER_UP', true); ?></span>
                    <span><?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'statistics.orderdown', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER_DOWN', true); ?>
						<?php $disabled = true ? '' : 'disabled="disabled"'; ?></span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                           class="form-control form-control-inline" style="text-align: center"/>
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
    