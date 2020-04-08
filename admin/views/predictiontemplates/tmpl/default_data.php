<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictiontemplates
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.tooltip');
?>

<!--	<div id='editcell'> -->
<!--		<fieldset class='adminform'> -->
			<legend>
				<?php
				if ($this->pred_id > 0)
				{
					$outputStr = Text::sprintf(
						'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TITLE2',
						'<i>' . $this->predictiongame->name . '</i>',
						' ' . $this->predictiongame->id . ' '
					);
				}
				else
				{
					$outputStr = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_DESCR');
				}

				echo $outputStr;
				?>
			</legend>
	<?php
	if (( $this->pred_id > 0 ) && ( $this->predictiongame->master_template ))
	{
		echo $this->loadTemplate('import');
	}


	if ($this->pred_id > 0)
	{
		?>
		  <table class="<?php echo $this->table_data_class; ?>">
		  <thead>
		   <tr>
		<th class='title' width='5'>
								<?php
								echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM');
								?>
			</th>
			<th class='title' width='20'>
								<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
			</th>
			<th class='title' width='20'>
								&nbsp;
			</th>
			<th class='title' nowrap='nowrap'>
								<?php
								echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TMPL_FILE'), 'tmpl.template', $this->sortDirection, $this->sortColumn);
								?>
			</th>                          
			<th class='title' nowrap='nowrap'>
								<?php
								echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TITLE3'), 'tmpl.title', $this->sortDirection, $this->sortColumn);
								?>
			</th>

			<th class='title' width='20' nowrap='nowrap'>
								<?php
								echo HTMLHelper::_('grid.sort', Text::_('JGRID_HEADING_ID'), 'tmpl.id', $this->sortDirection, $this->sortColumn);
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
								<td colspan='6'>
			<?php
			echo $this->pagination->getListFooter();
			?>
			  </td>
								<td colspan="2"><?php echo $this->pagination->getResultsCounter(); ?>
			</td>
			 </tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;

			for ($i = 0, $n = count($this->items); $i < $n; $i++)
			{
				$row =& $this->items[$i];

				$link = Route::_('index.php?option=com_sportsmanagement&task=predictiontemplate.edit&id=' . $row->id . '&predid=' . $this->prediction_id);
							$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
							$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
							$checked = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'predictiontemplates.', $canCheckin);
				?>
					  <tr class='<?php echo "row$k"; ?>'>
					 <td>
					<?php
					echo $this->pagination->getRowOffset($i);
					?>
					   </td>
					   <td>
					<?php
					echo HTMLHelper::_('grid.id', $i, $row->id);
					?>
					   </td>
					   <td style='text-align:center; '>
								<?php
								if ($row->checked_out)
								:
	?>
				<?php echo HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'predictiontemplates.', $canCheckin); ?>
								<?php endif; ?>  
			<a href='<?php echo $link; ?>'>
				<?php
				echo HTMLHelper::_(
					'image',
					'administrator/components/com_sportsmanagement/assets/images/edit.png',
					Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_EDIT_SETTINGS'),
					'title= "' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_EDIT_SETTINGS') . '"'
				);
							?>
						</a>
					   </td>
					   <td style='text-align:left; ' nowrap='nowrap'>
						<?php
						echo $row->template;
						?>
					   </td>                              
					   <td style='text-align:left; ' nowrap='nowrap'>
						<?php
						echo Text::_($row->title);
						?>
					   </td>
					   <td style='text-align:center; '>
						<?php
						echo $row->id;
						?>
					   </td>
								<td><?php echo $row->modified; ?></td>
							<td><?php echo $row->username; ?></td>
					  </tr>
					<?php
					$k = 1 - $k;
			}
		?>
		</tbody>
		  </table>
		<?php
	}
	?>
<!--		</fieldset> -->
<!--	</div> -->

