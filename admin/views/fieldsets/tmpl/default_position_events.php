<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fieldsets
 * @file       default_position_events.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>
<fieldset class="adminform">	
	<legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EVENTS_LEGEND'); ?></legend>
	<table class="admintable">
		<tr>
			<td style="width:auto;"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EXISTING_EVENTS'); ?></b><br /><?php echo $this->lists['events']; ?></td>
			<td style="width:auto;">
				<input  type="button" class="inputbox"
						onclick="move_list_items('eventslist','position_eventslist');jQuery('#position_eventslist option').prop('selected', true);"
						value="&gt;&gt;" />
				<br /><br />
				<input  type="button" class="inputbox"
						onclick="move_list_items('position_eventslist','eventslist');jQuery('#position_eventslist option').prop('selected', true);"
						value="&lt;&lt;" />
			</td>
			<td style="width:auto;"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_ASSIGNED_EVENTS_TO_POS'); ?></b><br /><?php echo $this->lists['position_events']; ?></td>
			<td align='center' style="width:auto;">
				<input  type="button" class="inputbox"
						onclick="move_up('position_eventslist');jQuery('#position_eventslist option').prop('selected', true);"
						value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" />
				<br /><br />
				<input type="button" class="inputbox"
					   onclick="move_down('position_eventslist');jQuery('#position_eventslist option').prop('selected', true);"
					   value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DOWN'); ?>" />
			</td>
			<td style="width:auto;">
			<fieldset class="adminform">
		<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EVENTS_HINT'); ?>
			</fieldset>
			</td>
		</tr>
	</table>
</fieldset>
