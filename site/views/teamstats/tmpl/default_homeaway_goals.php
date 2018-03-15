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

defined( '_JEXEC' ) or die( 'Restricted access' );

?>

		<div class="jl_teamsubstats">
			<table cellspacing="0" border="0" width="100%">
			<thead>
				<tr class="sectiontableheader">
					<th colspan="2">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_HOME_STATS');
						?>
					</th>
				</tr>
			</thead>
			<tbody>					
				<tr class="sectiontableentry1">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_HOME_GAME_PERCENTAGE');
						?>:
					</td>
					<td class="statvalue">
						<?php
						if(!empty($this->totalrounds)) {
							echo round( ( $this->totalshome->totalmatches / $this->totalrounds ), 2 );
						} else {
							echo "0";
						}
						?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_OVERALL');
						?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->totalshome->totalmatches;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_PLAYED');
						?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->totalshome->playedmatches;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL');
						?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->totalshome->totalgoals;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL_PER_MATCH');
						?>:
					</td>
					<td class="statvalue">
						<?php
						if (!empty($this->totalshome->playedmatches))
						{
							echo round ( ( $this->totalshome->totalgoals / $this->totalshome->playedmatches ), 2 );
						}
						else
						{
							echo '0';
						}
						?>
					</td>
				</tr>
				<?php
				if ( $this->config['home_away_stats'] )
				{
					?>
					<tr class="sectiontableentry2">
						<td class="statlabel">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR');
							?>
						</td>
						<td class="statvalue">
							<?php
							echo $this->totalshome->goalsfor;
							?>
						</td>
					</tr>
					<tr class="sectiontableentry1">
						<td class="statlabel">
							<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR_PER_MATCH');?>:
						</td>
						<td class="statvalue">
							<?php
							if ( !empty($this->totalshome->playedmatches))
							{
								echo round( ( $this->totalshome->goalsfor / $this->totalshome->playedmatches ), 2 );
							}
							else
							{
								echo '0';
							}
							?>
						</td>
					</tr>
					<tr class="sectiontableentry2">
						<td class="statlabel">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST');
							?>
						</td>
						<td class="statvalue">
							<?php
							echo $this->totalshome->goalsagainst;
							?>
						</td>
					</tr>
					<tr class="sectiontableentry1">
						<td class="statlabel">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST_PER_MATCH');
							?>:
						</td>
						<td class="statvalue">
							<?php
							if ( !empty($this->totalshome->playedmatches))
							{
									echo round( ( $this->totalshome->goalsagainst / $this->totalshome->playedmatches ), 2 );
							}
							else
							{
									echo '0';
							}
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
			</table>
		</div>
		<div class="jl_teamsubstats">
			<table cellspacing="0" border="0" width="100%">
			<thead>
				<tr class="sectiontableheader">
					<th colspan="2">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_AWAY_STATS');
						?>
					</th>
				</tr>
			</thead>
			<tbody>	
				<tr class="sectiontableentry1">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_AWAY_GAME_PERCENTAGE');
						?>:
					</td>
					<td class="statvalue">
						<?php
						if(!empty($this->totalrounds)) {
							echo round( ( $this->totalsaway->totalmatches / $this->totalrounds ), 2 );
						} else {
							echo "0";
						}
						?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_OVERALL');
						?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->totalsaway->totalmatches;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_PLAYED');
						?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->totalsaway->playedmatches;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL');
						?>:
					</td>
					<td class="statvalue">
						<?php
						echo $this->totalsaway->totalgoals;
						?>
					</td>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel">
						<?php
						echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL_PER_MATCH');
						?>:
					</td>
					<td class="statvalue">
						<?php
						if ( !empty($this->totalsaway->playedmatches))
						{
							echo round (($this->totalsaway->totalgoals / $this->totalsaway->playedmatches),2);
						}
						else
						{
							echo '0';
						}
						?>
					</td>
				</tr>

					<tr class="sectiontableentry2">
						<td class="statlabel">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR');
							?>
						</td>
						<td class="statvalue">
							<?php
							echo $this->totalsaway->goalsfor;
							?>
						</td>
					</tr>
					<tr class="sectiontableentry1">
						<td class="statlabel">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR_PER_MATCH');
							?>:
						</td>
						<td class="statvalue">
							<?php
							if (!empty($this->totalsaway->playedmatches))
							{
								echo round( ( $this->totalsaway->goalsfor / $this->totalsaway->playedmatches ), 2 );
							}
							else
							{
								echo '0';
							}
							?>
						</td>
					</tr>
					<tr class="sectiontableentry2">
						<td class="statlabel">
						 <?php
						 echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST');
						 ?>
						</td>
						<td class="statvalue">
						 <?php
						 echo $this->totalsaway->goalsagainst;
						 ?>
						</td>
					</tr>
					<tr class="sectiontableentry1">
						<td class="statlabel">
							<?php
							echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST_PER_MATCH');
							?>:
						</td>
						<td class="statvalue">
							<?php
							if (!empty($this->totalsaway->playedmatches))
							{
								echo round( ( $this->totalsaway->goalsagainst / $this->totalsaway->playedmatches ), 2 );
							}
							else
							{
								echo '0';
							}
							?>
						</td>
					</tr>
			</tbody>	
			</table>
		</div>

	