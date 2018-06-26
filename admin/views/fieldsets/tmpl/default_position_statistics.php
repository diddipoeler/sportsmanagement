<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_position_statistics.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fieldsets
 */

defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_STATISTICS_LEGEND'); ?></legend>
	<table class="admintable">
		<tr>
			<td style="width:auto;"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EXISTING_STATISTICS'); ?></b><br /><?php echo $this->lists['statistic']; ?></td>
			<td style="width:auto;">
				<input  type="button" class="inputbox"
						onclick="move_list_items('statistic','position_statistic');jQuery('#position_statistic option').prop('selected', true);"
						value="&gt;&gt;" />
				<br /><br />
				<input  type="button" class="inputbox"
						onclick="move_list_items('position_statistic','statistic');jQuery('#position_statistic option').prop('selected', true);"
						value="&lt;&lt;" />
			</td>
			<td style="width:auto;"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_ASSIGNED_STATS_TO_POS'); ?></b><br /><?php echo $this->lists['position_statistic']; ?></td>
			<td align='center' style="width:auto;">
				<input  type="button" class="inputbox"
						onclick="move_up('position_statistic');jQuery('#position_statistic option').prop('selected', true);"
						value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" />
				<br /><br />
				<input type="button" class="inputbox"
					   onclick="move_down('position_statistic');jQuery('#position_statistic option').prop('selected', true);"
					   value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?>" />
			</td>
			<td style="width:auto;">
			<fieldset class="adminform">
					<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_STATS_HINT'); ?>
			</fieldset>
			</td>			
		</tr>
	</table>
</fieldset>
