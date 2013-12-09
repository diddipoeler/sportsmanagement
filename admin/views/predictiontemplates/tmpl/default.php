<?php 


defined('_JEXEC') or die('Restricted access');

JHtml::_( 'behavior.tooltip' );
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
	<table width='100%'>
		<tr>
			<td nowrap='nowrap' style='text-align: right; '>
				<?php
				echo $this->lists['predictions'] . '&nbsp;&nbsp;';
				?>
			</td>
		</tr>
	</table>
	<div id='editcell'>
		<fieldset class='adminform'>
			<legend>
				<?php
				if ( $this->pred_id > 0 )
				{
					$outputStr = JText::sprintf( 	'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TITLE2',
													'<i>' . $this->predictiongame->name . '</i>',
													' ' . $this->predictiongame->id . ' ' );
				}
				else
				{
					$outputStr = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_DESCR' );
				}
				echo $outputStr;
				?>
			</legend>
			<?php
			if ( ( $this->pred_id > 0 ) && ( $this->predictiongame->master_template ) )
			{
				echo $this->loadTemplate( 'import' );
			}
			if ( $this->pred_id > 0 )
			{
				?>
				<table class='adminlist'>
					<thead>
						<tr>
							<th class='title' width='5'>
								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_NUM' );
								?>
							</th>
							<th class='title' width='20'>
								<input  type="checkbox" name="toggle" value=""
										onclick="checkAll(<?php echo count( $this->items ); ?>);" />
							</th>
							<th class='title' width='20'>
								&nbsp;
							</th>
							<th class='title' nowrap='nowrap'>
								<?php
								echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TMPL_FILE' ), 'tmpl.template', $this->lists['order_Dir'], $this->lists['order'] );
								?>
							</th>							
							<th class='title' nowrap='nowrap'>
								<?php
								echo JHtml::_( 'grid.sort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TITLE3' ), 'tmpl.title', $this->lists['order_Dir'], $this->lists['order'] );
								?>
							</th>

							<th class='title' width='20' nowrap='nowrap'>
								<?php
								echo JHtml::_( 'grid.sort', JText::_( 'JGRID_HEADING_ID' ), 'tmpl.id', $this->lists['order_Dir'], $this->lists['order'] );
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
							</tr>
						</tfoot>
						<tbody>
						<?php
						$k = 0;
						for ( $i = 0, $n = count( $this->items ); $i < $n; $i++ )
						{
							$row =& $this->items[$i];

							$link	= JRoute::_( 'index.php?option=com_sportsmanagement&task=predictiontemplate.edit&id=' . $row->id );
							$checked = JHtml::_( 'grid.checkedout', $row, $i );
							?>
							<tr class='<?php echo "row$k"; ?>'>
								<td>
									<?php
									echo $this->pagination->getRowOffset( $i );
									?>
								</td>
								<td>
									<?php
									echo $checked;
									?>
								</td>
								<td style='text-align:center; '>
									<a href='<?php echo $link; ?>'>
										<?php
										echo JHtml::_(	'image',
														'administrator/components/com_sportsmanagement/assets/images/edit.png',
														JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_EDIT_SETTINGS' ),
														'title= "' . JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_EDIT_SETTINGS' ) . '"' );
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
									echo JText::_( $row->title );
									?>
								</td>
								<td style='text-align:center; '>
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
			<?php
			}
			?>
		</fieldset>
	</div>
  
	<input type='hidden' name='task'				value='' />
	<input type='hidden' name='boxchecked'			value='0' />
	<input type='hidden' name='filter_order_Dir'	value='' />
	<input type='hidden' name='filter_order'		value='<?php echo $this->lists['order']; ?>' />
	
	<?php echo JHtml::_( 'form.token' ); ?>
</form>