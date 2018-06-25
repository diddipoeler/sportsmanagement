<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      deafult_training.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teaminfo
 */
defined('_JEXEC') or die('Restricted access');
?>

<h4>
    <?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING'); ?>
</h4>

<table class="table table-striped" >
    <thead>
        <tr class="sectiontableheader">
            <th class="" nowrap="" style="background:#BDBDBD;"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_DAY'); ?></th>
            <th class="" nowrap="" style="background:#BDBDBD;"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_START'); ?></th>
            <th class="" nowrap="" style="background:#BDBDBD;"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_END'); ?></th>
            <th class="" nowrap="" style="background:#BDBDBD;"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_LOCATION'); ?></th>
            <th class="" nowrap="" style="background:#BDBDBD;"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NOTE'); ?></th>
        </tr>
    </thead>
    <?php
    $k = 0;
    $count_note = 0;
    if (!empty($this->trainingData)) {
        foreach ($this->trainingData as $training) {
            $hours = ($training->time_start / 3600);
            $hours = (int) $hours;
            $mins = (($training->time_start - (3600 * $hours)) / 60);
            $mins = (int) $mins;
            $startTime = sprintf('%02d', $hours) . ':' . sprintf('%02d', $mins);
            $hours = ($training->time_end / 3600);
            $hours = (int) $hours;
            $mins = (($training->time_end - (3600 * $hours)) / 60);
            $mins = (int) $mins;
            $endTime = sprintf('%02d', $hours) . ':' . sprintf('%02d', $mins);
            ?>
            <tr class="">
                <td><?php echo $this->daysOfWeek[$training->dayofweek]; ?></td>
                <td><?php echo $startTime; ?></td>
                <td><?php echo $endTime; ?></td>
                <td><?php echo $training->place; ?></td>

                <?php
                if ($training->notes != ""):
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
        $k = 0;
        foreach ($this->trainingData as $training) {
            ?>

            <?php
            if ($training->notes != ""):
                $count_note++;
                ?>
                <tr class="" >
                    <td align="right">*<sup><?php echo $count_note; ?></sup></td>
                    <td align="left" colspan="4" ><?php echo $training->notes; ?></td>
                </tr>
            <?php endif; ?>
            <?php
            $k = 1 - $k;
        }
    }
    else {
        ?>
        <div class="bg-warning alert alert-warning">
            <?php
            echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NODATA');
            ?>
        </div>
        <?php
    }
    ?>
</table>
<br/>

