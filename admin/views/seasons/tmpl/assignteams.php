<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      assignteams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage seasons
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.modal');

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
		<legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SEASONS_ASSIGN_TEAM'); ?></legend>
		
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
                                
				<button onclick="this.form.submit(); "><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<?php
					echo Text::_('JSEARCH_FILTER_CLEAR');
					?>
				</button>
        </div>
        </td>
        </tr>
        <tr>
        <td>
		<div class="fltlft">
        
                
        <button type="button" onclick="Joomla.submitform('seasons.applyteams', this.form);">
						<?php echo Text::_('JAPPLY');?></button>
					<button type="button" onclick="$('close').value=1; Joomla.submitform('seasons.saveteams', this.form);">
						<?php echo Text::_('JSAVE');?></button>
        
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
                        <th class="title" nowrap="nowrap" ><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME'); ?></th>
						
                        </tr>
                </thead>      
                <tfoot><tr><td colspan="3"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
                <tbody>  
        <?php
					$k=0;
					for ($i=0,$n=count($this->items); $i < $n; $i++)
					{
					   $row		=& $this->items[$i];
                        $canEdit	= $this->user->authorise('core.edit','com_sportsmanagement');
                    $canCheckin = $this->user->authorise('core.manage','com_checkin') || $row->checked_out == $this->user->get ('id') || $row->checked_out == 0;
                    $checked = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get ('id'), $row->checked_out_time, 'seasons.', $canCheckin);
                    
					   ?>
						<tr class="<?php echo "row$k"; ?>">
                        <td style="<?php echo $style;?>">
								<?php
								echo $this->pagination->getRowOffset($i);
								?>
							</td>
							<td style="text-align:center; ">
								<?php
								echo HTMLHelper::_('grid.id', $i, $row->id);  
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
<?php echo HTMLHelper::_('form.token')."\n"; ?>
</form>
</fieldset>
</div>
