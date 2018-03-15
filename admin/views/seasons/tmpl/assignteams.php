<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');

//echo '<pre>'.print_r($this->items,true).'</pre><br>';

//save and close 
$close = JFactory::getApplication()->input->getInt('close',0);
if($close == 1) {
	?><script>
	window.addEvent('domready', function() {
		$('cancel').onclick();	
	});
	</script>
	<?php 
}

?>
<div id="editcell">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_ASSIGN_TEAM'); ?></legend>
		
		<!-- Start list -->
		<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id='adminForm'>
        
        <fieldset>
        <table class="<?php echo $this->table_data_class; ?>" border='0'>
        <tr>
        <td>
        <div class="fltlft">
        <input	type="text" name="filter_search" id="filter_search"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
								class="text_area" onchange="$('adminForm').submit(); " />
                                
				<button onclick="this.form.submit(); "><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<?php
					echo JText::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
        </div>
        </td>
        </tr>
        <tr>
        <td>
		<div class="fltlft">
        
                
        <button type="button" onclick="Joomla.submitform('seasons.applyteams', this.form);">
						<?php echo JText::_('JAPPLY');?></button>
					<button type="button" onclick="$('close').value=1; Joomla.submitform('seasons.saveteams', this.form);">
						<?php echo JText::_('JSAVE');?></button>
			<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		
        
        
        
         </div>
         </td>
        <td nowrap='nowrap' align='right'><?php echo $this->lists['nation2'].'&nbsp;&nbsp;'; ?>
        </td>
        
        </div>
        <tr>
		</table>  
	</fieldset>
    
    <table class="<?php echo $this->table_data_class; ?>" border='0'>
				<thead>
					<tr>
						<th width="5" ><?php echo count($this->items).'/'.$this->pagination->total; ?></th>
						<th width="20" >
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
                        <th class="title" nowrap="nowrap" ><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME'); ?></th>
						
                        </tr>
                </thead>      
                <tfoot><tr><td colspan="3"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
                <tbody>  
        <?php
					$k=0;
					for ($i=0,$n=count($this->items); $i < $n; $i++)
					{
					   $row		=& $this->items[$i];
					//	$checked	= JHtml::_('grid.checkedout',$row,$i,'id');
                        
                        $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                    $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
                    $checked = JHtml::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'seasons.', $canCheckin);
                    
					   ?>
						<tr class="<?php echo "row$k"; ?>">
                        <td style="<?php echo $style;?>">
								<?php
								echo $this->pagination->getRowOffset($i);
								?>
							</td>
							<td style="text-align:center; ">
								<?php
								echo JHtml::_('grid.id', $i, $row->id);  
								?>
							</td>
                            <td style="text-align:center; ">
								<?php
								echo $row->name;
								?>
							</td>
                            
                            
                        </tr>
                        <?PHP
                        $k=1 - $k;
                    }
?>                       
    </tbody>
    </table>    
        
    <input type="hidden" name="close" id="close" value="0" />   
    <input type='hidden' name='season_id' value='<?php echo $this->season_id; ?>' /> 
    <input type='hidden' name='act' value='' />
	<input type='hidden' name='task' value='' id='task' />
			<?php echo JHTML::_('form.token')."\n"; ?>
    
    
    </form>
</fieldset>

</div>