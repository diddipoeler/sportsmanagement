<?php
/**
 * Native Joomla 5/6 attendance statistics layout.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage stats
 * @file       default_attendance_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$total = (int) ($this->totals->sumspectators ?? 0);
$attendedMatches = (int) ($this->totals->attendedmatches ?? 0);
$average = $attendedMatches > 0 ? round($total / $attendedMatches, 2) : 0;
$tableClass = trim((string) ($this->config['attendance_table_class'] ?? 'table'));
?>
<div class="<?php echo $this->escape((string) $this->divclassrow); ?> table-responsive" id="attendancestats">
    <table class="<?php echo $this->escape($tableClass); ?>">
        <tbody>
            <tr class="sectiontableheader">
                <th colspan="2"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE'); ?></th>
            </tr>
            <tr class="sectiontableentry1">
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_TOTAL'); ?>:</td>
                <td class="statvalue"><?php echo $total; ?></td>
            </tr>
            <tr class="sectiontableentry2">
                <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_PER_MATCH'); ?>:</td>
                <td class="statvalue"><?php echo $average; ?></td>
            </tr>
            <tr class="sectiontableentry1">
                <td class="statlabel">
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_BEST_AVG'); ?>:</strong><br>
                    <?php echo $this->escape((string) ($this->bestavgteam ?? '')); ?>
                </td>
                <td class="statvalue"><?php echo $this->escape((string) ($this->bestavg ?? 0)); ?></td>
            </tr>
            <tr class="sectiontableentry2">
                <td class="statlabel">
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_WORST_AVG'); ?>:</strong><br>
                    <?php echo $this->escape((string) ($this->worstavgteam ?? '')); ?>
                </td>
                <td class="statvalue"><?php echo $this->escape((string) ($this->worstavg ?? 0)); ?></td>
            </tr>
        </tbody>
    </table>
</div>
