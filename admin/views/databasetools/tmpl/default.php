<?php defined('_JEXEC') or die('Restricted access');

JHtml::_( 'behavior.tooltip' );
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm">
	<div id="editcell">
		<table class="adminlist">
			<thead>
				<tr>
					<th class="title" class="nowrap">
						<?php
						echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TOOL' );
						?>
					</th>
					<th class="title" class="nowrap">
						<?php
						echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_DESCR' );
						?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">
						<?php
						echo "&nbsp;";
						?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&task=databasetool.optimize' );
						?>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE2' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE_DESCR" );
						?>
					</td>
				</tr>

				<tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&task=databasetool.repair' );
						?>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR2' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR_DESCR" );
						?>
					</td>
				</tr>
                
                <tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&task=databasetool.picturepath' );
						?>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION2' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION_DESCR" );
						?>
					</td>
				</tr>
                
                <tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&task=databasetool.updatetemplatemasters' );
						?>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS2' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS_DESCR" );
						?>
					</td>
				</tr>

			</tbody>
		</table>
	</div>

	<input type="hidden" name="task" value="databasetool.execute" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
