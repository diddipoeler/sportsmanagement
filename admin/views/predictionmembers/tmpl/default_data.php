<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionmembers
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$this->saveOrder = $this->sortColumn == 'tmb.ordering';
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
            <th width="5">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM');
				?>
            </th>
            <th width="20">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
				</th>
            <th class="title" nowrap="nowrap">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_USERNAME'), 'u.username', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title" nowrap="nowrap">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REAL_NAME'), 'u.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title" nowrap="nowrap">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_PRED_NAME'), 'p.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title" nowrap="nowrap">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_DATE_LAST_TIP'), 'tmb.last_tipp', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER'), 'tmb.reminder', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_RECEIPT'), 'tmb.receipt', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_PROFILE'), 'tmb.show_profile', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ADMIN_TIP'), 'tmb.admintipp', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVED'), 'tmb.approved', $this->sortDirection, $this->sortColumn);
				?>
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
            <td colspan='8'>
				<?php
				echo $this->pagination->getListFooter();
				?>
            </td>
            <td colspan="5"><?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" <?php endif; ?>>
		<?php
		if (isset($this->items))
		{
			$k = 0;

				foreach ($this->items as $this->count_i => $this->item)
		{

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
}    
				//$this->item =& $this->items[$i];

				$link       = Route::_('index.php?option=com_sportsmanagement&task=prediction.edit&id=' . $this->item->id);
				$link2      = Route::_('index.php?option=com_sportsmanagement&task=predictionmember.edit&id=' . $this->item->id);
				$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
				$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
				$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'predictionmembers.', $canCheckin);

				?>
                <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                    <td>
						<?php
						echo $this->pagination->getRowOffset($this->count_i);
						?>
                    </td>
                    <td>
						<?php
						echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id);
						?>
                    </td>
                    <td>
						<?php
						if ($this->item->checked_out)
							:
							?>
							<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'predictionmembers.', $canCheckin); ?>
						<?php endif; ?>
                        <a href="<?php echo $link2; ?>"
                           title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_EDIT_USER'); ?>">
							<?php
							echo $this->item->username;
							?>
                        </a>
						<?php

						?>
                    </td>
                    <td>
						<?php
						// If ( $this->table->($this->user->get( 'id' ), $this->item->checked_out ) )
						// {
						// 	echo $this->item->realname;
						// }
						// else
						{
							?>
							<?php /*
							?>
							<a  href="<?php echo $link; ?>"
							title="<?php echo Text::_( 'Edit SportsManagement-Prediction User' ); ?>">
							<?php */ ?>
							<?php
							echo $this->item->realname;
							?>
							<?php /*
							?>
							</a>
							<?php */ ?>
							<?php
						}
						?>
                    </td>
                    <td nowrap='nowrap'>
						<?php
						echo $this->item->predictionname;
						?>
                    </td>
                    <td style='text-align: center; '>
						<?php
						if (isset($this->item->last_tipp))
						{
							list($date, $time) = explode(" ", $this->item->last_tipp);
							$time = date('H:i', strtotime($time));
							echo sportsmanagementHelper::convertDate($date);
							echo ' / ';
							echo $time;
						}
						else
						{
							echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NEVER_TIPPED');
						}
						?>
                    </td>
                    <td style='text-align: center; '>
						<?php
						if ($this->item->reminder)
						{
							$imgfile  = 'ok.png';
							$imgtitle = Text::_('Active');
						}
						else
						{
							$imgfile  = 'delete.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE');
						}

						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
							$imgtitle, 'title= "' . $imgtitle . '"'
						);
						?>
                    </td>
                    <td style='text-align: center; '>
						<?php
						if ($this->item->receipt)
						{
							$imgfile  = 'ok.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE');
						}
						else
						{
							$imgfile  = 'delete.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE');
						}

						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
							$imgtitle, 'title= "' . $imgtitle . '"'
						);
						?>
                    </td>
                    <td style='text-align: center; '>
						<?php
						if ($this->item->show_profile)
						{
							$imgfile  = 'ok.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ALLOWED');
						}
						else
						{
							$imgfile  = 'delete.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NOT_ALLOWED');
						}

						echo HTMLHelper::_('image',
							'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
							$imgtitle, 'title= "' . $imgtitle . '"'
						);
						?>
                    </td>
                    <td style='text-align: center; '>
						<?php
						if ($this->item->admintipp)
						{
							$imgfile  = 'ok.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE');
						}
						else
						{
							$imgfile  = 'delete.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE');
						}

						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
							$imgtitle, 'title= "' . $imgtitle . '"'
						);
						?>
                    </td>
                    <td style='text-align: center; '>
						<?php
						if ($this->item->approved)
						{
							$imgfile  = 'ok.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVED');
						}
						else
						{
							$imgfile  = 'delete.png';
							$imgtitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NOT_APPROVED');
						}

						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
							$imgtitle, 'title= "' . $imgtitle . '"'
						);
						?>
                    </td>
                    <td><?php echo $this->item->modified; ?></td>
                    <td><?php echo $this->item->modusername; ?></td>
                </tr>
				<?php
				$k = 1 - $k;
			}
		}
		?>
        </tbody>
    </table>
</div>

  
