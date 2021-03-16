<?php
/**
 * SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       deafult_training.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

$this->notes = array();
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING');
echo $this->loadTemplate('jsm_notes');
?>
<table class="table table-striped">
    <thead>
    <tr class="sectiontableheader">
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_DAY'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_START'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_END'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_LOCATION'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NOTE'); ?></th>
    </tr>
    </thead>
	<?php
	$k          = 0;
	$count_note = 0;
	if (!empty($this->trainingData))
	{
		foreach ($this->trainingData as $training)
		{
			$hours     = ($training->time_start / 3600);
			$hours     = (int) $hours;
			$mins      = (($training->time_start - (3600 * $hours)) / 60);
			$mins      = (int) $mins;
			$startTime = sprintf('%02d', $hours) . ':' . sprintf('%02d', $mins);
			$hours     = ($training->time_end / 3600);
			$hours     = (int) $hours;
			$mins      = (($training->time_end - (3600 * $hours)) / 60);
			$mins      = (int) $mins;
			$endTime   = sprintf('%02d', $hours) . ':' . sprintf('%02d', $mins);
			?>
            <tr class="">
                <td><?php echo $this->daysOfWeek[$training->dayofweek]; ?></td>
                <td><?php echo $startTime; ?></td>
                <td><?php echo $endTime; ?></td>
                <td><?php echo $training->place; ?></td>

				<?php
				if ($training->notes != "") :
					$count_note++;
					?>
                    <td>*<sup><?php echo $count_note; ?></sup></td>
				<?php else: ?>
                    <td><?php echo $training->notes; ?></td>
				<?php endif; ?>

            </tr>
			<?php
			$k = 1 - $k;
		}
		$count_note = 0;
		$k          = 0;
		foreach ($this->trainingData as $training)
		{
			?>

			<?php
			if ($training->notes != "") :
				$count_note++;
				?>
                <tr class="">
                    <td align="right">*<sup><?php echo $count_note; ?></sup></td>
                    <td align="left" colspan="4"><?php echo $training->notes; ?></td>
                </tr>
			<?php endif; ?>
			<?php
			$k = 1 - $k;
		}
	}
	else
	{
		?>
        <div class="bg-warning alert alert-warning">
			<?php
			echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NODATA');
			?>
        </div>
		<?php
	}
	?>
</table>
<br/>

