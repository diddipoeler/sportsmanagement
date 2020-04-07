<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_attendance_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage stats
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>

<div class="<?php echo $this->divclassrow;?> table-responsive" id="attendancestats">

<table class="<?php echo $this->config['attendance_table_class'];?>">
	<tr class="sectiontableheader">
		<th colspan="2"><?php	echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE'); ?></th>
	</tr>

	<tr class="sectiontableentry1">
		<td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_TOTAL');?>:</td>
		<td class="statvalue"><?php echo $this->totals->sumspectators;?></td>
	</tr>
	<tr class="sectiontableentry2">
		<td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_PER_MATCH');?>:</td>
		<td class="statvalue">
		<?php
		if (isset($this->totals->sumspectators) && $this->totals->attendedmatches)
		{
			echo round(($this->totals->sumspectators / $this->totals->attendedmatches), 2);
		}
		else
		{
			echo 0;
		}
		?>
		</td>
	</tr>
	<tr class="sectiontableentry1">
		<td class="statlabel"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_BEST_AVG');?>:</b>
		<br />
	<?php echo $this->bestavgteam;?></td>
		<td class="statvalue"><?php echo $this->bestavg;?></td>
	</tr>
	<tr class="sectiontableentry2">
		<td class="statlabel"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_WORST_AVG');?>:</b>
		<br />
	<?php echo $this->worstavgteam;?></td>
		<td class="statvalue"><?php echo $this->worstavg;?></td>
	</tr>
</table>

</div>
