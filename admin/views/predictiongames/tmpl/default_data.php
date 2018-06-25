<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictiongames
 */

defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
$ordering = ($this->sortColumn=='pre.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');

?>

<!--	<div id='editcell'> -->
		<?php
		if ($this->dPredictionID > 0)
		{
			?>
<!--			<fieldset class="adminform"> -->
				<legend>
					<?php
					echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE2','<i>'.$this->items[0]->name.'</i>');
					?>
				</legend>
			<?php
		}
		?>
		<table class="<?php echo $this->table_data_class; ?>">
			<thead>
				<tr>
					<th width='10'><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width='20'>
						<input	type='checkbox' name='toggle' value='' onclick="checkAll(<?php echo count( $this->items ); ?>);" />
					</th>
					<th width='20'>&nbsp;</th>
					<th class='title' nowrap='nowrap'>
						<?php
						echo JHtml::_('grid.sort',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NAME'),'pre.name',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					<th class='title' nowrap='nowrap' colspan='2'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PROJ_COUNT'); ?></th>
					<th class='title' nowrap='nowrap' colspan='2'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_ADMIN_COUNT'); ?></th>
					<th class='title' width='5%' nowrap='nowrap'>
						<?php
						echo JHtml::_('grid.sort',JText::_('JSTATUS'),'pre.published',$this->sortDirection,$this->sortColumn);
						?>
					</th>
					
					<th width="" class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USERS');
						?>
					</th>
					<th width="" class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_GROUPS');
						?>
					</th>
					<th width="" class="title">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TEMPLATES');
						?>
					</th>
					
					<th class='title' width='20' nowrap='nowrap'>
						<?php
						echo JHtml::_('grid.sort',JText::_('JGRID_HEADING_ID'),'pre.id',$this->sortDirection,$this->sortColumn);
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
			<?php
			if ( $this->dPredictionID == 0 )
			{
				?>
                <tfoot>
                <tr>
                <td colspan='10'><?php echo $this->pagination->getListFooter(); ?>
                </td>
                <td colspan="8"><?php echo $this->pagination->getResultsCounter(); ?>
            </td>
                </tr>
                </tfoot>
                <?php
			}
			?>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count($this->items); $i < $n; $i++)
			{
				$row			=& $this->items[$i];
				$pred_projects	= $this->getModel()->getChilds($row->id);
				$pred_admins	= $this->getModel()->getAdmins($row->id);
				//$checked		= JHtml::_('grid.checkedout',$row,$i);
				//$published		= JHtml::_('grid.published',$row,$i);
        $published  = JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','predictiongames.');
        $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
        $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
        $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'predictiongames.', $canCheckin);
				$link = JRoute::_('index.php?option=com_sportsmanagement&task=predictiongame.edit&id=' . $row->id);
				?>
				<tr class='<?php echo "row$k"; ?>'>
					<td style='text-align:right; '><?php echo $this->pagination->getRowOffset( $i ); ?></td>
					<td><?php echo JHtml::_('grid.id', $i, $row->id); ?></td>
					<td style='text-align:center; '>
                    <?php
                            if ($row->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'predictiongames.', $canCheckin); ?>
									<?php endif; ?>
							<a href='<?php echo $link; ?>'>
									<img	src='<?php echo JURI::root(); ?>administrator/components/com_sportsmanagement/assets/images/edit.png'
											border='0'
											alt='<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_EDIT_DETAILS' ); ?>'
											title='<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_EDIT_DETAILS' ); ?>'>
								</a>
							<?php
						
					?></td>
					<td>
						<?php
//						if ( $this->table->isCheckedOut($this->user->get ('id'), $row->checked_out ) )
//						{
//							echo $row->name;
//						}
//						else
//						{
						$link = JRoute::_('index.php?option=com_sportsmanagement&view=predictiongames&prediction_id='.$row->id);  
						?><a href="<?php echo $link; ?>"
								title="<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_SELECT_PGAME',$row->name); ?>">
								<?php
								echo $row->name;
								?>
							</a>
						<?php
						//}
						?>
					</td>
					<td style='text-align:center; ' colspan='2'><?php echo count( $pred_projects ); ?></td>
					<td style='text-align:center; ' colspan='2'><?php echo count( $pred_admins ); ?></td>
					<td style='text-align:center; '><?php echo $published; ?></td>
					
					<td style='text-align:center; '>
						<?php 
				$image = 'players.png';
                        $title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_USERS');
			$link2 = JRoute::_('index.php?option=com_sportsmanagement&view=predictionmembers&prediction_id='.$row->id);
						?>
<a href="<?php echo $link2; ?>">
<?php
$attribs['title'] = $title;
echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/'.$image,
	$title, $attribs);
?>
</a>						
					</td>
					
<td style='text-align:center; '>
						<?php 
				$image = 'division.png';
                        $title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_GROUPS');
			$link2 = JRoute::_('index.php?option=com_sportsmanagement&view=predictiongroups&prediction_id='.$row->id);
						?>
<a href="<?php echo $link2; ?>">
<?php
$attribs['title'] = $title;
echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/'.$image,
	$title, $attribs);
?>
</a>						
					</td>
					
					<td style='text-align:center; '>
						<?php 
				$image = 'templates.png';
                        $title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TEMPLATES');
			$link2 = JRoute::_('index.php?option=com_sportsmanagement&view=predictiontemplates&prediction_id='.$row->id);	
						?>
<a href="<?php echo $link2; ?>">
<?php
$attribs['title'] = $title;
echo JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/'.$image,
	$title, $attribs);
?>
</a>						
					</td>
					
					<td style='text-align:center; '><?php echo $row->id; ?></td>
                    <td><?php echo $row->modified; ?></td>
                            <td><?php echo $row->username; ?></td> 
				</tr>
				<?php
				$k = 1 - $k;
			}

			if ( $this->dPredictionID > 0 )
			{
				//echo '<br />#<pre>'; print_r( $this->predictionProjects ); echo '</pre>#<br />';
				//echo '<br />#<pre>'; print_r( $this->predictionAdmins ); echo '</pre>#<br />';
				?>
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php echo JText::_( 'NUM' ); ?></th>
						<th>&nbsp;</th>
						<th class='title'><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PROJ_NAME' ); ?></th>
						<th class='title'><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_MODE' ); ?></th>
						<th class='title'><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_OVERVIEW' ); ?></th>
						<th class='title'><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_JOKER' ); ?></th>
						<th class='title'><?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_CHAMP' ); ?></th>
						<th class='title'><?php echo JText::_( 'JSTATUS' ); ?></th>
						<th class='title'><?php echo JText::_( 'JGRID_HEADING_ID' ); ?></th>
					</tr>
				</thead>
				<?php
				$ii	= 0;
				$k	= 1;
				if ( count( $this->predictionProjects ) > 0 )
				{
					foreach ( $this->predictionProjects AS $pred_project )
					{
						//echo '<br />#<pre>'; print_r( $pred_project ); echo '</pre>#<br />';
						$link = JRoute::_(	'index.php?option=com_sportsmanagement&' .
											'' .
											'task=predictionproject.edit&tmpl=component&id=' . $pred_project['id'].'&project_id='.$pred_project['project_id'] );
                                            
                                            
						?>
						<tr class='<?php echo "row$k"; ?>'>
							<td style='text-align:right; '>&nbsp;</td>
							<td style='text-align:center; '><?php echo $ii+1; ?></td>
							<td style='text-align:center; '>&nbsp;</td>
							<td>
								<a class="modal"	
                                rel="{handler: 'iframe',size: {x: <?php echo $this->modalwidth; ?>,y: <?php echo $this->modalheight; ?>}}"
                                href='<?php echo $link; ?>'
									title='<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_EDIT_SETTINGS' ); ?>' />
									<?php echo $pred_project['project_name']; ?>
								</a>
							</td>
							<td style='text-align:center; '><?php
								if ( $pred_project['mode'] == '0' )
								{
									echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_STANDARD' );
								}
								else
								{
									echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TOTO' );
								}
								?></td>
							<td style='text-align:center; '><?php
								if ( $pred_project['overview'] == '0' )
								{
									echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_FULL_SEASON' );
								}
								else
								{
									echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_HALF_SEASON' );
								}
								?></td>
							<td style='text-align:center; '><?php
								if ( $pred_project['joker'] == '1' )
								{
									if ($pred_project['joker_limit']==0)
										{
											$maxJ = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_UNLIMITED_JOKER');
										}
									else
										{
											$maxJ = $pred_project['joker_limit'];
										}
									$imageTitle = JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_MAX_JOKER', $maxJ );
									$imageFile = 'administrator/components/com_sportsmanagement/assets/images/ok.png';
								}
								else
								{
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_JOKER' );
									$imageFile = 'administrator/components/com_sportsmanagement/assets/images/delete.png';
								}
								echo JHtml::_(	'image', $imageFile, $imageTitle, 'title= "' . $imageTitle . '"' );
								?></td>
							<td style='text-align:center; '><?php
								if ( $pred_project['champ'] == '1' )
								{
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PICK_CHAMP' );
									$imageFile = 'administrator/components/com_sportsmanagement/assets/images/ok.png';
								}
								else
								{
									$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PICK_CHAMP' );
									$imageFile = 'administrator/components/com_sportsmanagement/assets/images/delete.png';
								}
								echo JHtml::_(	'image', $imageFile, $imageTitle, 'title= "' . $imageTitle . '"' );
								?></td>
							<td style='text-align:center; ' >
								<?php
									if ( $pred_project['published'] == '1' )
									{
										$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_PUBLISHED' );
										$imageFile = 'administrator/components/com_sportsmanagement/assets/images/ok.png';
									}
									else
									{
										$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_UNPUBLISHED' );
										$imageFile = 'administrator/components/com_sportsmanagement/assets/images/delete.png';
									}
									echo JHtml::_(	'image', $imageFile, $imageTitle, 'title= "' . $imageTitle . '"' );
								?>
							</td>
							<td style='text-align:center; ' nowrap='nowrap'><?php echo $pred_project['project_id']; ?></td>
						</tr>
						<?php
						$k = 1 - $k;
						$ii++;
					}
				}
			}
			?>
			</tbody>
		</table>
		<?php
		if ( $this->dPredictionID > 0 )
		{
			?>
<!--			</fieldset> -->
			<?php
		}
		?>
<!--	</div> -->
