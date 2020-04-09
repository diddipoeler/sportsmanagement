<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage eventtypes
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
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
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
            </th>

            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_EVENTS_STANDARD_NAME_OF_EVENT', 'obj.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width=""><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TRANSLATION'); ?></th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_EVENTS_ICON', 'obj.icon', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_EVENTS_SPORTSTYPE', 'obj.sports_type_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'obj.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'eventtypes.saveorder');
				?>
            </th>
            <th width="5%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
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
        <tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$row = &$this->items[$i];

			$link       = Route::_('index.php?option=com_sportsmanagement&task=eventtype.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'eventtypes.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.eventtype.' . $row->id) && $canCheckin;
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
				$inputappend = '';
				?>
                <td class="center">
					<?php if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'eventtypes.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=eventtype.edit&id=' . (int) $row->id); ?>">
							<?php echo $this->escape($row->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($row->name); ?>
					<?php endif; ?>



					<?php //echo $checked;  ?>

					<?php //echo $row->name;  ?>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?></p>
                </td>
				<?php ?>

                <td>
					<?php
					if ($row->name == Text::_($row->name))
					{
						echo '&nbsp;';
					}
					else
					{
						echo Text::_($row->name);
					}
					?>
                </td>
                <td class="center">
					<?php
					$desc = Text::_($row->name);
					echo sportsmanagementHelper::getPictureThumb($row->icon, $desc, 0, 21, 4);
					?>
                </td>
                <td class="center">
					<?php
					echo Text::_(sportsmanagementHelper::getSportsTypeName($row->sports_type_id));
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'eventtypes.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'eventtypes');
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'eventtypes');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
						}
						?>
                    </div>
                </td>
                <td class="order">
                        <span>
                            <?php
                            echo $this->pagination->orderUpIcon($i, $i > 0, 'eventtypes.orderup', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER_UP', $row->ordering);
                            ?>
                        </span>
                    <span>
    <?php
    echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'eventtypes.orderdown', 'COM_SPORTSMANAGEMENT_GLOBAL_ORDER_DOWN', $row->ordering);
    ?>
    <?php
    $disabled = true ? '' : 'disabled="disabled"';
    ?>
                        </span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled ?>
                           class="form-control form-control-inline" style="text-align: center"/>
                </td>
                <td class="center">
					<?php
					echo $row->id;
					?>
                </td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
