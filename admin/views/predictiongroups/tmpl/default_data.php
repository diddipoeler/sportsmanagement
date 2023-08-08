<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictiongroups
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

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
HTMLHelper::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl,$this->saveOrderButton);    
}
}


?>
<div class="table-responsive" id="editcell_predictiongames">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th width="20">&nbsp;</th>
            <th>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_NAME', 's.name', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 's.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'predictiongroups.saveorder');
				?>
            </th>
            <th width="20">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 's.id', $this->sortDirection, $this->sortColumn); ?>
            </th>

            <th width="" class="title">
				<?php
				echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL');
				?>
            </th>
            <th width="" class="title">
				<?php
				echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL');
				?>
            </th>

        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="6"><?php echo $this->pagination->getListFooter(); ?>
            </td>
            </td>
            <td colspan="2"><?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" <?php endif; ?>>
		<?php
		$k = 0;
			foreach ($this->items as $this->count_i => $this->item)
		{

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
}    
			//$this->item        =& $this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=predictiongroup.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'predictiongroups.', $canCheckin);
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center"><?php echo $this->pagination->getRowOffset($this->count_i); ?></td>
                <td class="center"><?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?></td>
				<?php

				$inputappend = '';
				?>
                <td class="center">
					<?php
					if ($this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'predictiongroups.', $canCheckin); ?>
					<?php endif; ?>
                    <a href="<?php echo $link; ?>">
						<?php
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICTIONGROUPS_EDIT_DETAILS');
						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/edit.png',
							$imageTitle, 'title= "' . $imageTitle . '"'
						);
						?>
                    </a>
                </td>
				<?php

				?>
                <td><?php echo $this->item->name; ?></td>
                <td class="order">
                            <span>
                                <?php echo $this->pagination->orderUpIcon($this->count_i, $this->count_i > 0, 'predictiongroups.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?>
                            </span>
                    <span>
                                <?php echo $this->pagination->orderDownIcon($this->count_i, $n, $this->count_i < $n, 'predictiongroups.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?>
                                <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                            </span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $this->item->ordering; ?>" <?php echo $disabled ?>
                           class="text_area" style="text-align: center"/>
                </td>
                <td class="center"><?php echo $this->item->id; ?></td>
                <td><?php echo $this->item->modified; ?></td>
                <td><?php echo $this->item->username; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
