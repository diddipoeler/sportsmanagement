<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
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
use Joomla\CMS\Session\Session;

$this->saveOrder = $this->sortColumn == 's.ordering';
if ($this->saveOrder && !empty($this->items))
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task='.$this->view.'.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{    
HTMLHelper::_('draggablelist.draggable');
}
else
{
JHtml::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl,$this->saveOrderButton);    
}
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
        </tr>
        </tfoot>
        <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" <?php endif; ?>>
		<?php
			foreach ($this->items as $this->count_i => $this->item) 
			{
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
}                     
            
			$link = Route::_('index.php?option=com_sportsmanagement&task=season.edit&id=' . $this->item->id);

			$assignteams   = Route::_('index.php?option=com_sportsmanagement&tmpl=component&view=teams&layout=assignteams&season_id=' . $this->item->id);
			$assignpersons = Route::_('index.php?option=com_sportsmanagement&tmpl=component&view=players&layout=assignpersons&season_id=' . $this->item->id.'&whichview=seasons');
			$canEdit       = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin    = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked       = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'seasons.', $canCheckin);
			$canChange     = $this->user->authorise('core.edit.state', 'com_sportsmanagement.season.' . $this->item->id) && $canCheckin;
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
				if ($this->table->isCheckedOut($this->user->get('id'), $this->item->checked_out))
				{
					$inputappend = ' disabled="disabled"';
					?>
                    <td class="center">&nbsp;</td><?php
				}
				else
				{
					$inputappend = '';
					?>
                    <td class="center" nowrap="nowrap">
						<?php
						$image = 'teams.png';
						$title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_ASSIGN_TEAM');
						echo sportsmanagementHelper::getBootstrapModalImage('assignteams' . $this->item->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/' . $image, $title, '20', $assignteams, $this->modalwidth, $this->modalheight);
						$image = 'players.png';
						$title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_ASSIGN_PERSON');
						echo sportsmanagementHelper::getBootstrapModalImage('assignperson' . $this->item->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/' . $image, $title, '20', $assignpersons, $this->modalwidth, $this->modalheight);
						?>
                    </td>
					<?php
				}
				?>
                <td>
					<?php if ($this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'seasons.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=season.edit&id=' . (int) $this->item->id); ?>">
							<?php echo $this->escape($this->item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($this->item->name); ?>
					<?php endif; ?>

                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'seasons.', $canChange, 'cb'); ?>
						<?php
						/** Create dropdown items and render the dropdown list. */
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'seasons');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'seasons');
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
                <td class="center"><?php echo $this->item->id; ?></td>
            </tr>
			<?php

		}
		?>
        </tbody>
    </table>
</div>
