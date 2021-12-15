<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_trainingsdata
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

?>
<div class="">
    <legend class="scheduler-border">
        <strong>
			<?php
			if ($params->get('show_training_modul_header'))
			{
				echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING');
			}
			?>
        </strong>
    </legend>

	<?php

	?>

    <table class="<?php echo $params->get('table_class'); ?>">
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
			<?PHP
			if ($params->get('show_training_note'))
			{
				?>
                <th class="" nowrap=""
                    style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NOTE'); ?></th>
				<?PHP
			}
			?>
        </tr>
        </thead>
		<?php
		$k          = 0;
		$count_note = 0;
		if (!empty($trainingsdata))
		{
			foreach ($trainingsdata as $training)
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
                    <td><?php echo $daysOfWeek[$training->dayofweek]; ?></td>
                    <td><?php echo $startTime; ?></td>
                    <td><?php echo $endTime; ?></td>
                    <td><?php echo $training->place; ?></td>


					<?php
					if ($params->get('show_training_note'))
					{
						if ($training->notes != "") :
							$count_note++;
							?>
                            <td>*<sup><?php echo $count_note; ?></sup></td>
						<?php else: ?>
                            <td><?php echo $training->notes; ?></td>
						<?php
						endif;
					}
					?>

                </tr>
				<?php
				$k = 1 - $k;
			}
			if ($params->get('show_training_note'))
			{
				$count_note = 0;
				$k          = 0;
				foreach ($trainingsdata as $training)
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
					<?php
					endif;
					?>
					<?php
					$k = 1 - $k;
				}
			}
		}
		else
		{
			?>

            <div class="alert alert-error">
                <h4>
					<?php
					echo Text::_('COM_SPORTSMANAGEMENT_ERROR');
					?>
                </h4>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NODATA');
				?>
            </div>

			<?php
		}

		?>
    </table>
    <br/>
	<?php

	?>
</div>
