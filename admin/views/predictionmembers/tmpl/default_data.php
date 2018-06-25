<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionmembers
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_( 'behavior.tooltip' );
?>

	<div id="editcell">
		<table class="<?php echo $this->table_data_class; ?>">
			<thead>
				<tr>
					<th width="5">
						<?php
						echo JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_NUM' );
						?>
					</th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th class="title" nowrap="nowrap">
						<?php
						echo JHtml::_( 'grid.sort',  JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_USERNAME' ), 'u.username', $this->sortDirection, $this->sortColumn );
						?>
					</th>
					<th class="title" nowrap="nowrap">
						<?php
						echo JHtml::_( 'grid.sort',  JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REAL_NAME' ), 'u.name', $this->sortDirection, $this->sortColumn );
						?>
					</th>
					<th class="title" nowrap="nowrap">
						<?php
						echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_PRED_NAME' ), 'p.name', $this->sortDirection, $this->sortColumn );
						?>
					</th>
					<th class="title" nowrap="nowrap">
						<?php
						echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_DATE_LAST_TIP' ), 'tmb.last_tipp', $this->sortDirection, $this->sortColumn );
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER' ), 'tmb.reminder', $this->sortDirection, $this->sortColumn );
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_RECEIPT' ), 'tmb.receipt', $this->sortDirection, $this->sortColumn );
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_PROFILE' ), 'tmb.show_profile', $this->sortDirection, $this->sortColumn );
						?>
					</th>
					<th class="title">
						<?php
						echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ADMIN_TIP' ), 'tmb.admintipp', $this->sortDirection, $this->sortColumn );
						?>
					</th>
					<th width="1%">
						<?php
						echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVED' ), 'tmb.approved', $this->sortDirection, $this->sortColumn );
						?>
					</th>
                    
                    <th width="" class="title">
						<?php
						echo JText::_('JGLOBAL_FIELD_MODIFIED_LABEL');
						?>
					</th>
                    <th width="" class="title">
						<?php
						echo JText::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL');
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
			<tbody>
			<?php
      if ( isset($this->items) )
      {
			$k = 0;
			for ( $i = 0, $n = count( $this->items ); $i < $n; $i++ )
			{
				$row =& $this->items[$i];

				$link	= JRoute::_( 'index.php?option=com_sportsmanagement&task=prediction.edit&id=' . $row->id );
				//$link2	= JRoute::_( 'index.php?option=com_users&view=user&layout=edit&cid[]=' . $row->user_id );
                $link2	= JRoute::_( 'index.php?option=com_sportsmanagement&task=predictionmember.edit&id=' . $row->id );
                $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
                $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'predictionmembers.', $canCheckin);

				//$checked = JHtml::_( 'grid.checkedout', $row, $i );
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php
						echo $this->pagination->getRowOffset( $i );
						?>
					</td>
					<td>
						<?php
						echo JHtml::_('grid.id', $i, $row->id);
						?>
					</td>
					<td>
					<?php
                            if ($row->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'predictionmembers.', $canCheckin); ?>
									<?php endif; ?>	
							<a  href="<?php echo $link2; ?>"
								title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_EDIT_USER' ); ?>" >
								<?php
								echo $row->username;
								?>
							</a>
							<?php
						
						?>
					</td>
					<td>
						<?php
						#if ( $this->table->($this->user->get( 'id' ), $row->checked_out ) )
						#{
						#	echo $row->realname;
						#}
						#else
						{
						?>
							<?php /* ?>
							<a  href="<?php echo $link; ?>"
								title="<?php echo JText::_( 'Edit JoomLeague-Prediction User' ); ?>">
							<?php */ ?>
								<?php
								echo $row->realname;
								?>
							<?php /* ?>
							</a>
							<?php */ ?>
							<?php
						}
						?>
					</td>
					<td nowrap='nowrap'>
						<?php
						echo $row->predictionname;
						?>
					</td>
					<td style='text-align: center; '>
						<?php
						if ( isset( $row->last_tipp ) )
						{
							list( $date, $time ) = explode( " ", $row->last_tipp );
							$time = strftime( "%H:%M", strtotime( $time ) );
							echo sportsmanagementHelper::convertDate( $date );
							echo ' / ';
							echo $time;
						}
						else
						{
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NEVER_TIPPED' );
						}
						?>
					</td>
					<td style='text-align: center; '>
						<?php
						if ($row->reminder){$imgfile='ok.png';$imgtitle=JText::_('Active');}else{$imgfile='delete.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE');}
						echo JHtml::_(	'image', 'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
										$imgtitle, 'title= "' . $imgtitle . '"' );
						?>
					</td>
					<td style='text-align: center; '>
						<?php
						if ($row->receipt){$imgfile='ok.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE');}else{$imgfile='delete.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE');}
						echo JHtml::_(	'image', 'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
										$imgtitle, 'title= "' . $imgtitle . '"' );
						?>
					</td>
					<td style='text-align: center; '>
						<?php
						if ($row->show_profile){$imgfile='ok.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ALLOWED');}else{$imgfile='delete.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NOT_ALLOWED');}
						echo JHtml::image(	'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
											$imgtitle, 'title= "' . $imgtitle . '"' );
						?>
					</td>
					<td style='text-align: center; '>
						<?php
						if ($row->admintipp){$imgfile='ok.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE');}else{$imgfile='delete.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE');}
						echo JHtml::_(	'image', 'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
										$imgtitle, 'title= "' . $imgtitle . '"' );
						?>
					</td>
					<td style='text-align: center; '>
						<?php
						if ($row->approved){$imgfile='ok.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVED');}else{$imgfile='delete.png';$imgtitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NOT_APPROVED');}
						echo JHtml::_(	'image', 'administrator/components/com_sportsmanagement/assets/images/' . $imgfile,
										$imgtitle, 'title= "' . $imgtitle . '"' );
						?>
					</td>
                    <td><?php echo $row->modified; ?></td>
                            <td><?php echo $row->modusername; ?></td> 
				</tr>
				<?php
				$k = 1 - $k;
			}
      }
			?>
			</tbody>
		</table>
	</div>

	
