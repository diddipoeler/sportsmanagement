<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage sportstypes
 * @file       default_4_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;


$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="1%"
                class="nowrap text-center d-none d-md-table-cell"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="1%" class="text-center">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>

            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_NAME', 's.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_TRANSLATION'); ?>
            </th>
            <th width="10%" class="title text-center">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_ICON'); ?>
            </th>
            <th width="10%" class="title">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_SPORTSART'); ?>
            </th>

            <th width="1%" class="nowrap center">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 's.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>

            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 's.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'sportstypes.saveorder');
				?>
            </th>
            <th width='1%' class="text-center">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 's.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="4">
				<?php echo $this->pagination->getListFooter(); ?>
            </td>
            <td colspan="4">
				<?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$row        = &$this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=sportstype.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'sportstypes.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.sportstype.' . $row->id) && $canCheckin;
			?>
            <tr class="<?php echo "row$k"; ?>">
                <td class="text-center">
					<?php
					echo $this->pagination->getRowOffset($i);
					?>
                </td>
                <td class="text-center">
					<?php
					echo HTMLHelper::_('grid.id', $i, $row->id);
					?>
                </td>

				<?php
				$inputappend = '';
				?>
                <td class="has-context">
					<?php if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'sportstypes.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=sportstype.edit&id=' . (int) $row->id); ?>">
                            <span class="fa fa-pencil-square mr-2"
                                  aria-hidden="true"></span> <?php echo $this->escape($row->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($row->name); ?>
					<?php endif; ?>



					<?php //echo $checked;  ?>
                </td>
				<?php ?>
                <td><?php echo Text::_($row->name); ?></td>

                <td class="text-center">
					<?php
					$picture = JPATH_SITE . DIRECTORY_SEPARATOR . $row->icon;
					$desc    = Text::_($row->name);
					//echo sportsmanagementHelper::getPictureThumb($picture, $desc, 0, 21, 4);
					if (file_exists(Uri::root() . $row->icon))
					{
						echo '<a href="' . Uri::root() . $row->icon . '" title="' . $desc . '" class="modal">
                            <img src="' . Uri::root() . $row->icon . '>" alt="' . $desc . '" width="20" />
                        </a>';
					}
					else
					{
						echo '<i class="fa fa-picture-o text-danger"></i>';
					}
					?>
                </td>
                <td>
					<?php
					$append = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" style="background-color:#bbffff"';
					echo HTMLHelper::_('select.genericlist', $this->lists['sportart'], 'sportstype_id' . $row->id, 'class="custom-select form-control" size="1"' . $append, 'value', 'text', $row->sportsart);
					?>
                </td>
                <td class="text-center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'sportstypes.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'sportstypes');
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'sportstypes');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
						}
						?>
                    </div>
                </td>

                <td class="order">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'sportstype.orderup', 'JLIB_HTML_MOVE_UP', 's.ordering'); ?>
                        </span>
                    <span>
                            <?php
                            echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'sportstype.orderdown', 'JLIB_HTML_MOVE_DOWN', 's.ordering');
                            ?>
                            <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                        </span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled ?>
                           class="form-control form-control-inline" style="text-align: center"/>
                </td>
                <td class="text-center"><?php echo $row->id; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
