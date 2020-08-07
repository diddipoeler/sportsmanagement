<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
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
use Joomla\CMS\Session\Session;

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
    
if ($this->saveOrder && !empty($this->items))
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task='.$this->view.'.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';    
HTMLHelper::_('draggablelist.draggable');
}    
}
else
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task='.$this->view.'.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';    
JHtml::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl);
}   
?>
<div class="table-responsive" id="editcell">
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
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
        <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" data-nested="true"<?php endif; ?>>
		<?php
    foreach ($this->items as $this->count_i => $this->item)
	{
//$this->count_i = $i;
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
}         

			$link       = Route::_('index.php?option=com_sportsmanagement&task=eventtype.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'eventtypes.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.eventtype.' . $this->item->id) && $canCheckin;
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center">
					<?php
					echo $this->pagination->getRowOffset($this->count_i);
					?>
                </td>
                <td class="center">
					<?php
					echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id);
					?>
                </td>
				<?php
				$inputappend = '';
				?>
                <td class="center">
					<?php if ($this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'eventtypes.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=eventtype.edit&id=' . (int) $this->item->id); ?>">
							<?php echo $this->escape($this->item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($this->item->name); ?>
					<?php endif; ?>




                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
				<?php ?>

                <td>
					<?php
					if ($this->item->name == Text::_($this->item->name))
					{
						echo '&nbsp;';
					}
					else
					{
						echo Text::_($this->item->name);
					}
					?>
                </td>
                <td class="center">
					<?php
					$desc = Text::_($this->item->name);
					echo sportsmanagementHelper::getPictureThumb($this->item->icon, $desc, 0, 21, 4);
					?>
                </td>
                <td class="center">
					<?php
					echo Text::_(sportsmanagementHelper::getSportsTypeName($this->item->sports_type_id));
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'eventtypes.', $canChange, 'cb'); ?>
						<?php
						/** Create dropdown items and render the dropdown list. */
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'eventtypes');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'eventtypes');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->item->name));
						}
						?>
                    </div>
                </td>
                <td class="order" id="defaultdataorder">
<?php
echo $this->loadTemplate('data_order');
?>
</td>
                <td class="center">
					<?php
					echo $this->item->id;
					?>
                </td>
            </tr>
			<?php

		}
		?>
        </tbody>
    </table>
</div>
