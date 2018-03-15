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
//$component_text = 'COM_SPORTSMANAGEMENT_';
//echo '<br /><pre>~' . print_r($this->predictionMember->pmID,true) . '~</pre><br />';
if (!isset($this->config['show_scoring'])){$this->config['show_scoring']=1;}
$gameModeStr = ($this->model->predictionProject->mode==0) ? JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_STANDARD_MODE') : JText::_('JL_PRED_RESULTS_TOTO_MODE');
?><p style='font-weight: bold; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_NOTICE'); ?></p><p><ul><li><i><?php
						echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_01');
						if (!$this->config['show_all_user'])
						{
							?></i></li><li><i><?php
							echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_02');
							?></i></li><li><i><?php
						}
						else
						{
							echo ' ';
						}
						echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_03');
						?></i></li><li><i><?php
						echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_04');
						?></i></li><li><i><?php
						echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_05');
						?></i></li><li><i><?php
						echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_06','<b>'.$gameModeStr.'</b>');
						?><?php
						if (($this->config['show_scoring']) && ($this->predictionMember->pmID > 0))
						{
							?></i></li><li><i><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_NOTICE_INFO_07');
							?><table class='blog' cellpadding='0' cellspacing='0'>
								<tr>
									<td class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_RESULT'); ?></td>
									<td class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_YOUR_PREDICTION'); ?></td>
									<td class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_POINTS'); ?></td>
									<?php
									if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0))
									{
										?><td class='sectiontableheader' style='text-align:center; '><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_JOKER_POINTS'); ?></td><?php
									}
									?>
								</tr>
								<tr class='sectiontableentry1'>
									<td class='info'><?php echo '2:1'; ?></td>
									<td class='info'><?php echo '2:1'; ?></td>
									<td class='info'><?php
										$result = sportsmanagementModelPrediction::createResultsObject(2,1,1,2,1,0);
										echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
										?></td>
									<?php
									if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0))
									{
										?>
										<td class='info'><?php
											$result = sportsmanagementModelPrediction::createResultsObject(2,1,1,2,1,1);
											echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
											?></td><?php
									}
									?>
								</tr>
								<tr class='sectiontableentry2'>
									<td class='info'><?php echo '2:1'; ?></td>
									<td class='info'><?php echo '3:2'; ?></td>
									<td class='info'><?php
										$result = sportsmanagementModelPrediction::createResultsObject(2,1,1,3,2,0);
										echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
										?></td>
									<?php
									if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0))
									{
										?>
										<td class='info'><?php
											$result = sportsmanagementModelPrediction::createResultsObject(2,1,1,3,2,1);
											echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
											?></td><?php
									}
									?>
								</tr>
								<tr class='sectiontableentry1'>
									<td class='info'><?php echo '1:1'; ?></td>
									<td class='info'><?php echo '2:2'; ?></td>
									<td class='info'><?php
										$result = sportsmanagementModelPrediction::createResultsObject(1,1,0,2,2,0);
										echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
										?></td>
									<?php
									if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0))
									{
										?>
										<td class='info'><?php
											$result = sportsmanagementModelPrediction::createResultsObject(1,1,0,2,2,1);
											echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
											?></td><?php
									}
									?>
								</tr>
								<tr class='sectiontableentry2'>
									<td class='info'><?php echo '1:2'; ?></td>
									<td class='info'><?php echo '1:3'; ?></td>
									<td class='info'><?php
										$result = sportsmanagementModelPrediction::createResultsObject(1,2,1,1,3,0);
										echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
										?></td>
									<?php
									if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0))
									{
										?>
										<td class='info'><?php
											$result = sportsmanagementModelPrediction::createResultsObject(1,2,1,1,3,1);
											echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
											?></td><?php
									}
									?>
								</tr>
								<tr class='sectiontableentry1'>
									<td class='info'><?php echo '2:1'; ?></td>
									<td class='info'><?php echo '0:1'; ?></td>
									<td class='info'><?php
										$result = sportsmanagementModelPrediction::createResultsObject(2,1,2,0,1,0);
										echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
										?></td>
									<?php
									if (($this->model->predictionProject->joker) && ($this->model->predictionProject->mode==0))
									{
										?>
										<td class='info'><?php
											$result = sportsmanagementModelPrediction::createResultsObject(2,1,2,0,1,1);
											echo sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($this->model->predictionProject,$result);
											?></td><?php
									}
									?>
								</tr>
							</table><?php
						}
						else
						{
							?></i></li><li><i><?php
							echo JText::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_READ_RULES');
						}
						?></i></li></ul></p>