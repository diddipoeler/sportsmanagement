<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access'); ?>




<h4>

<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING'); ?>

</h4>

<?php

?>

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
	$k=0;
	$count_note=0;
	if (!empty($this->trainingData))
	{
		foreach ($this->trainingData as $training)
		{
			$hours=($training->time_start / 3600); $hours=(int)$hours;
			$mins=(($training->time_start - (3600*$hours)) / 60); $mins=(int)$mins;
			$startTime=sprintf('%02d',$hours).':'.sprintf('%02d',$mins);
			$hours=($training->time_end / 3600); $hours=(int)$hours;
			$mins=(($training->time_end - (3600*$hours)) / 60); $mins=(int)$mins;
			$endTime=sprintf('%02d',$hours).':'.sprintf('%02d',$mins);
			?>
			<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
				<td><?php echo $this->daysOfWeek[$training->dayofweek]; ?></td>
				<td><?php echo $startTime; ?></td>
				<td><?php echo $endTime; ?></td>
				<td><?php echo $training->place; ?></td>
				
				
				<?php if($training->notes != ""): 
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
		$k=0;
		foreach ($this->trainingData as $training)
		{
		
		?>
		
		<?php if($training->notes != ""):
		$count_note++;
		?>
		<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>note" >
			<td align="right">*<sup><?php echo $count_note; ?></sup></td>
			<td align="left" colspan="4" ><?php echo $training->notes; ?></td>
		</tr>
		<?php endif; ?>
	<?php	
		$k = 1 - $k;
		}
	}
	else
	{
	?>
	
    
    <div class="alert alert-error">
<h4>
<?php
echo JText::_('COM_SPORTSMANAGEMENT_ERROR');
?>
</h4>
<?php
echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING_NODATA');
?>
</div>
    
	<?php
	}
	
	?>
</table>
<br/>
<?php

?>
