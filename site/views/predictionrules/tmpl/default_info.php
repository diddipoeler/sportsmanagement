<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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



defined('_JEXEC') or die('Restricted access');
?><h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_01'); ?></h3>
<p><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_01'); ?></p>
<p><?php
		if ($this->actJoomlaUser->id < 62)
		{
			echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_02','<a href="index.php?option=com_user&view=register"><b><i>','</i></b></a>');
		}
		else
		{
			if (!$this->predictionMember->pmID){echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_01_03');}
		}
		?></p>
<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_02'); ?></h3>
<p><?php
	if ($this->predictionGame->auto_approve)
	{
		echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_01');
	}
	else
	{
		echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_02');
	}
	echo '<br />';
	echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_02_03');
	?></p>
<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_03'); ?></h3>
<p><?php
	echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_01') . '<br />';
	if (!$this->predictionGame->admin_tipp)
	{
		echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_02');
	}
	else
	{
		echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_03_03');
	}
	?></p>
<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_04'); ?></h3>
<p><?php  echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_04_01'); ?></p>
<p><?php  echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_04_02'); ?></p>
<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05'); ?></h3>
<p><?php  echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_05_01'); ?></p>
<?php
if ($this->config['show_points'])
{
	?>
	<p><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_05_02'); ?></p>
	<?php
	foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
	{
		if ($predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id))
		{
			?>
			<table class='blog' cellpadding='0' cellspacing='0' border='1'>
				<tr>
					<td class='sectiontableheader' style='text-align:center; '><?php
						echo $predictionProjectSettings->name . ' - ';
						if ($predictionProject->mode=='0')
						{
							echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_STANDARD_MODE');
						}
						else
						{
							echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_TOTO_MODE');
						}
						?></td>
				</tr>
			</table>
			<table class='blog' cellpadding='0' cellspacing='0'>
				<tr>
					<td class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_RESULT'); ?></td>
					<td class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_YOUR_PREDICTION'); ?></td>
					<td class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_POINTS'); ?></td>
					<?php
					if (($predictionProject->joker) && ($predictionProject->mode==0))
					{
						?><td class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_05_JOKER_POINTS'); ?></td><?php
					}
					?>
				</tr>
				<tr class='sectiontableentry1'>
					<td class='info'><?php echo '2:1'; ?></td>
					<td class='info'><?php echo '2:1'; ?></td>
					<td class='info'><?php
						$result = sportsmanagementModelPrediction::createResultsObject(2,1,1,2,1,0);
						echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
						?></td>
					<?php
					if (($predictionProject->joker) && ($predictionProject->mode==0))
					{
						?>
						<td class='info'><?php
							$result = sportsmanagementModelPrediction::createResultsObject(2,1,1,2,1,1);
							echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
							?></td><?php
					}
					?>
				</tr>
				<tr class='sectiontableentry2'>
					<td class='info'><?php echo '2:1'; ?></td>
					<td class='info'><?php echo '3:2'; ?></td>
					<td class='info'><?php
						$result = sportsmanagementModelPrediction::createResultsObject(2,1,1,3,2,0);
						echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
						?></td>
					<?php
					if (($predictionProject->joker) && ($predictionProject->mode==0))
					{
						?>
						<td class='info'><?php
							$result = sportsmanagementModelPrediction::createResultsObject(2,1,1,3,2,1);
							echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
							?></td><?php
					}
					?>
				</tr>
				<tr class='sectiontableentry1'>
					<td class='info'><?php echo '1:1'; ?></td>
					<td class='info'><?php echo '2:2'; ?></td>
					<td class='info'><?php
						$result = sportsmanagementModelPrediction::createResultsObject(1,1,0,2,2,0);
						echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
						?></td>
					<?php
					if (($predictionProject->joker) && ($predictionProject->mode==0))
					{
						?>
						<td class='info'><?php
							$result = sportsmanagementModelPrediction::createResultsObject(1,1,0,2,2,1);
							echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
							?></td><?php
					}
					?>
				</tr>
				<tr class='sectiontableentry2'>
					<td class='info'><?php echo '1:2'; ?></td>
					<td class='info'><?php echo '1:3'; ?></td>
					<td class='info'><?php
						$result = sportsmanagementModelPrediction::createResultsObject(1,2,1,1,3,0);
						echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
						?></td>
					<?php
					if (($predictionProject->joker) && ($predictionProject->mode==0))
					{
						?>
						<td class='info'><?php
							$result = sportsmanagementModelPrediction::createResultsObject(1,2,1,1,3,1);
							echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
							?></td><?php
					}
					?>
				</tr>
				<tr class='sectiontableentry1'>
					<td class='info'><?php echo '2:1'; ?></td>
					<td class='info'><?php echo '0:1'; ?></td>
					<td class='info'><?php
						$result = sportsmanagementModelPrediction::createResultsObject(2,1,2,0,1,0);
						echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
						?></td>
					<?php
					if (($predictionProject->joker) && ($predictionProject->mode==0))
					{
						?>
						<td class='info'><?php
							$result = sportsmanagementModelPrediction::createResultsObject(2,1,2,0,1,1);
							echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionProject,$result);
							?></td><?php
					}
					?>
				</tr>
			</table>
			<?php
		}
	}
}
?>
<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06'); ?></h3>
<p><?php
	echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_01');
	?></p><ul><?php
	foreach (sportsmanagementModelPrediction::$_predictionProjectS AS $predictionProject)
	{
		if ($predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id))
		{
			if ($predictionProject->champ > 0)
			{
				?><li><?php
					if ($predictionProject->overview)
					{
						echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06_HALF_SEASON',
												'<b>'.$predictionProject->points_tipp_champ.'</b>',
												'<b><i>'.$predictionProjectSettings->name.'</i></b>');
					}
					else
					{
						echo JText::sprintf(	'COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_06_FULL_SEASON',
												'<b>'.$predictionProject->points_tipp_champ.'</b>',
												'<b><i>'.$predictionProjectSettings->name.'</i></b>');
					}
					?></li>
				<?php
			}
		}
	}
	?></ul>
<p><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_02'); ?></p>
<p><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_06_03'); ?></p>
<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_07'); ?></h3>
<p><?php  echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_07_01'); ?></p>
<h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_TOPIC_08'); ?></h3>
<p><?php  echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_08_01'); ?></p>
<p><?php  echo JText::_('COM_SPORTSMANAGEMENT_PRED_RULES_INFO_08_02'); ?></p>
<br />