<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

//Ordering allowed ?
$ordering = ($this->sortColumn=='pre.ordering');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
	<table>
		<tr>
			<td align="left" width="100%">
				<?php
				if ( $this->dPredictionID == 0 )
				{
				?>
					<?php
				echo JText::_('JSEARCH_FILTER_LABEL');
				?>&nbsp;<input	type="text" name="filter_search" id="filter_search"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
                                                        
                                                        
					<button onclick="this.form.submit();">
						<?php
						echo JText::_('JSEARCH_FILTER_SUBMIT');
						?>
					</button>
					<button onclick="document.getElementById('filter_search').value='';this.form.submit();">
						<?php
						echo JText::_('JSEARCH_FILTER_CLEAR');
						?>
					</button>
					<?php
					}
					else {echo '&nbsp';}
					?>
			</td>
			<td nowrap='nowrap' align='right'>
				<?php
				echo $this->lists['predictions'] . '&nbsp;&nbsp;';
				?>
			</td>
			<?php
			if ($this->dPredictionID==0)
			{
			?>
				<td class="nowrap" align="right"><select name="filter_published" id="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php 
                echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);
                ?>
			</select></td>
			<?php
			}
			?>
		</tr>
	</table>
	<div id='editcell'>
		<?php
		if ($this->dPredictionID > 0)
		{
			?>
			<fieldset class="adminform">
				<legend>
					<?php
					echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE2','<i>'.$this->items[0]->name.'</i>');
					?>
				</legend>
			<?php
		}
		?>
		<table width='100%' class='adminlist'>
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
					<th class='title' width='20' nowrap='nowrap'>
						<?php
						echo JHtml::_('grid.sort',JText::_('JGRID_HEADING_ID'),'pre.id',$this->sortDirection,$this->sortColumn);
						?>
					</th>
				</tr>
			</thead>
			<?php
			if ($this->dPredictionID==0)
			{
				?><tfoot><tr><td colspan='10'><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot><?php
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
				$checked		= JHtml::_('grid.checkedout',$row,$i);
				//$published		= JHtml::_('grid.published',$row,$i);
        $published  = JHtml::_('grid.published',$row,$i,'tick.png','publish_x.png','predictiongames.');
        
				$link			= JRoute::_('index.php?option=com_sportsmanagement&task=predictiongame.edit&id=' . $row->id);
				?>
				<tr class='<?php echo "row$k"; ?>'>
					<td style='text-align:right; '><?php echo $this->pagination->getRowOffset( $i ); ?></td>
					<td><?php echo $checked; ?></td>
					<td style='text-align:center; '><?php
						if ( JTable::isCheckedOut( $this->user->get ('id'), $row->checked_out ) )
						{
							$inputappend = " disabled='disabled'";
							?>&nbsp;<?php
						}
						else
						{
							?><a href='<?php echo $link; ?>'>
									<img	src='<?php echo JUri::root(); ?>administrator/components/com_sportsmanagement/assets/images/edit.png'
											border='0'
											alt='<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_EDIT_DETAILS' ); ?>'
											title='<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_EDIT_DETAILS' ); ?>'>
								</a>
							<?php
						}
					?></td>
					<td>
						<?php
						if ( JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) )
						{
							echo $row->name;
						}
						else
						{
						?><a	href="#"
								onclick="document.adminForm.prediction_id.value='<?php echo $row->id; ?>';document.adminForm.submit();"
								title="<?php echo JText::sprintf(	'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_SELECT_PGAME',
																	$row->name ); ?>">
								<?php
								echo $row->name;
								?>
							</a>
						<?php
						}
						?>
					</td>
					<td style='text-align:center; ' colspan='2'><?php echo count( $pred_projects ); ?></td>
					<td style='text-align:center; ' colspan='2'><?php echo count( $pred_admins ); ?></td>
					<td style='text-align:center; '><?php echo $published; ?></td>
					<td style='text-align:center; '><?php echo $row->id; ?></td>
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
			</fieldset>
			<?php
		}
		?>
	</div>

<input type="hidden" name="option" value="<?php echo $this->option; ?>" />	
<input type="hidden" name="task" value="" />
	<input type='hidden' name='boxchecked'			value='0' />
	<input type='hidden' name='filter_order'		value='<?php echo $this->sortColumn; ?>' />
	<input type='hidden' name='filter_order_Dir'	value='' />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>