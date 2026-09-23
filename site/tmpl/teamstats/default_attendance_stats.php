<?php
/**
 * Native Joomla 5/6 team attendance statistics layout.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage teamstats
 * @file       default_attendance_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$rows = [
    ['COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_TOTAL', $this->totalattendance ?? 0],
    ['COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_PER_MATCH', $this->averageattendance ?? 0],
    ['COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_BEST', $this->bestattendance ?? 0],
    ['COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE_WORST', $this->worstattendance ?? 0],
];
?>
<div class="<?php echo $this->escape((string) $this->divclassrow); ?> table-responsive" id="attendancestats">
    <table class="table">
        <tbody>
            <tr class="sectiontableheader">
                <th colspan="2"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_ATTENDANCE'); ?></th>
            </tr>
            <?php foreach ($rows as $index => [$label, $value]) : ?>
                <tr class="<?php echo $index % 2 === 0 ? 'sectiontableentry1' : 'sectiontableentry2'; ?>">
                    <td class="statlabel"><?php echo Text::_($label); ?>:</td>
                    <td class="statvalue"><?php echo $this->escape((string) $value); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
