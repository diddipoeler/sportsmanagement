<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_attendance_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage teamstats
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>  
		<div class="<?php echo $this->divclassrow;?> table-responsive" id="attendancestats">
			<table class="table">
				<tr class="sectiontableheader">
					<th colspan="2" class="le">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE');
		?>
					</th>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_TOTAL');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->totalattendance;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_PER_MATCH');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->averageattendance;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_BEST');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->bestattendance;
		?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
		<?php
		echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_WORST');
		?>:
					</td>
					<td class="statvalue">
		<?php
		echo $this->worstattendance;
		?>
					</td>
				</tr>
			</table>
		</div>                      

