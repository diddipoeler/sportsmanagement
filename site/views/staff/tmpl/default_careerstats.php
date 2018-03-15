<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access'); ?>

<!-- Player stats History START -->
<h4><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS' );	?></h4>
<table class="<?php echo $this->config['table_class'];?>" >
	<tr>
		<td>
			<br/>
			<table id="stats_history" class="<?php echo $this->config['table_class'];?>">
				<tr class="sectiontableheader">
					<th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
					<th class="td_l" class="nowrap"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                     <th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE');?></th> 
					<th class="td_c"><?php
						$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_PLAYED');
						echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/played.png',
											$imageTitle,array(' title' => $imageTitle,' width' => 20,' height' => 20));
						?></th>
					<?php
					if ($this->config['show_careerstats'])
					{
						if (!empty($stats))
						{
							foreach ($this->stats as $stat){ ?><th class="td_c"><?php echo $stat->getImage(); ?></th><?php }
						}
					}
					?>
				</tr>
				<?php
				$k=0;
				$career=array();
				$career['played']=0;
				$mod = JModelLegacy::getInstance('Staff','sportsmanagementModel');
				if (count($this->history) > 0)
				{
					foreach ($this->history as $player_hist)
					{
						$model = $this->getModel();
						$present = $model->getPresenceStats($player_hist->project_id,$player_hist->pid);
						?>
						<tr class="">
							<td class="td_l" nowrap="nowrap"><?php
								echo $player_hist->project_name;
								# echo " (".$player_hist->project_id.")";
								?></td>
							<td class="td_l" class="nowrap"><?php echo $player_hist->team_name; ?></td>
                            
                            <td>
                <?PHP
                //echo $player_hist->season_picture;
                echo sportsmanagementHelperHtml::getBootstrapModalImage('careerstats'.$player_hist->project_id.'-'.$player_hist->team_id,$player_hist->season_picture,$player_hist->team_name,'50');
                ?>
                </td>
							<!-- Player stats History - played start -->
							<td class="td_c"><?php
								echo ($present > 0) ? $present : '-';
								$career['played'] += $present;
								?></td>
							<!-- Player stats History - allevents start -->
							<?php
							if ($this->config['show_careerstats'])
							{
								if (!empty($staffstats))
								{
									foreach ($this->stats as $stat)
									{
										?>
										<td class="td_c">
											<?php echo (isset($this->staffstats[$stat->id][$player_hist->project_id]) ? $this->staffstats[$stat->id][$player_hist->project_id] : '-'); ?>
										</td>
										<?php
									}
								}
							}
							?>
							<!-- Player stats History - allevents end -->
						</tr>
						<?php
						$k=(1-$k);
					}
				}
				?>
				<tr class="career_stats_total">
					<td class="td_r" colspan="2"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_CAREER_TOTAL'); ?></b></td>
					<td class="td_c"><?php echo ($career['played'] > 0) ? $career['played'] : '-'; ?></td>
					<?php // stats per project
					if ($this->config['show_careerstats'])
					{
						if (!empty($historystats))
						{
							foreach ($this->stats as $stat)
							{
								?>
								<td class="td_c">
									<?php echo (isset($this->historystats[$stat->id]) ? $this->historystats[$stat->id] : '-'); ?>
								</td>
								<?php
							}
						}
					}
					?>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br/>
<!-- staff stats History END -->
