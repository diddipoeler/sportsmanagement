<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage templates
 */

defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');JHtml::_('behavior.modal');
?>
<script>
	function searchTemplate(val,key)
	{
		var f = $('adminForm');
		if(f){
			f.elements['search'].value=val;
			f.elements['search_mode'].value= 'matchfirst';
			f.submit();
		}
	}
</script>
<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_LEGEND','<i>'.$this->projectws->name.'</i>'); ?></legend>
			<table class="<?php echo $this->table_data_class; ?>">
				<thead>
					<?php 
        if ($this->projectws->master_template)
        {
            ?>
                    <tr>
                    <td align="right" colspan="6">
                    <?php echo $this->lists['mastertemplates']; ?>
                    </td>
                    </tr>
                    <?php
                    } 
            ?>
                    <tr>
						<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th width="20">&nbsp;</th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TEMPLATE','tmpl.template',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort','COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_DESCR','tmpl.title',$this->sortDirection,$this->sortColumn); ?>
						</th>
						<th>
							<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TYPE'); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort','JGRID_HEADING_ID','tmpl.id',$this->sortDirection,$this->sortColumn); ?>
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
                <td colspan="5">
                <?php 
                echo $this->pagination->getListFooter(); 
                ?>
                </td>
                <td colspan="6"><?php echo $this->pagination->getResultsCounter(); ?>
            </td>
                </tr>
                </tfoot>
				<tbody>
					<?php
					$k=0;
					for ($i=0, $n=count($this->templates); $i < $n; $i++)
					{
						$row =& $this->templates[$i];
						$link1 = JRoute::_('index.php?option=com_sportsmanagement&task=template.edit&id='.$row->id);
                        $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
						
                        $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
                        $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'templates.', $canCheckin);
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
							<td class="center"><?php echo JHtml::_('grid.id', $i, $row->id); ?></td>
							<td>
                            <?php
                            if ( ( $row->checked_out != $this->user->get ('id') ) && $row->checked_out ) :  ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'templates.', $canCheckin); ?>
									<?php else: ?>
                            
                            <?php
								$imageFile = 'administrator/components/com_sportsmanagement/assets/images/edit.png';
								$imageTitle = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_EDIT_DETAILS');
								$imageParams = 'title= "'.$imageTitle.'"';
								$image = JHtml::image($imageFile,$imageTitle,$imageParams);
								$linkParams = '';
								echo JHtml::link($link1,$image);
                                
                                endif;
                                
								?>
                                </td>
							<td><?php echo $row->template; ?></td>
							<td><?php echo JText::_($row->title); ?></td>
							<td><?php
								echo '<span style="font-weight:bold; color:';
								echo ($row->isMaster) ? 'red; ">'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_MASTER') : 'green;">&nbsp;'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_INDEPENDENT');
								echo '</span>';
								?></td>
							<td class="center"><?php
								echo $row->id;
								?><input type='hidden' name='isMaster[<?php echo $row->id; ?>]' value='<?php echo $row->isMaster; ?>' /><?php ?>
                                </td>
                            <td><?php echo $row->modified; ?></td>
                            <td><?php echo $row->username; ?></td>    
						</tr>
						<?php
						$k=1 - $k;
					}
					?>
				</tbody>
			</table>
			
