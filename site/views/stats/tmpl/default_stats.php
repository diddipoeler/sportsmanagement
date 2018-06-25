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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<div id="jl_stats">

<div class="jl_substats">
<table class="<?php echo $this->config['stats_table_class'];?>">
<thead>
	<tr class="sectiontableheader">
		<th colspan="2"><?php	echo JText::_('COM_SPORTSMANAGEMENT_STATS_GENERAL'); ?></th>
	</tr>
</thead>
<tbody>
	<tr class="sectiontableentry1">
		<td class="statlabel"><?php	echo JText::_('COM_SPORTSMANAGEMENT_STATS_MATCHDAYS'); ?>:</td>
		<td class="statvalue"><?php	echo $this->totalrounds; ?></td>
	</tr>
	<tr class="sectiontableentry2">
		<td class="statlabel"><?php	echo JText::_('COM_SPORTSMANAGEMENT_STATS_CURRENT_MATCHDAY');	?>:
		</td>
		<td class="statvalue"><?php	echo $this->actualround; ?></td>
	</tr>
	<tr class="sectiontableentry1">
		<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_PER_MATCHDAY'); ?>:</td>
		<td class="statvalue"><?php	echo ($this->totalrounds > 0 ? round (($this->totals->totalmatches / $this->totalrounds),2) : 0); ?>
		</td>
	</tr>
	<tr class="sectiontableentry2">
		<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_OVERALL');?>:</td>
		<td class="statvalue"><?php	echo $this->totals->totalmatches;?></td>
	</tr>
	<tr  class="sectiontableentry1">
		<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_PLAYED');?>:</td>
		<td class="statvalue"><?php	echo $this->totals->playedmatches;?></td>
	</tr>
	
	<?php	if ($this->config['home_away_stats']): ?>
	<tr  class="sectiontableentry2">
		<td class="statlabel"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_HIGHEST_WON_HOME');?>:</b>
		<br />
		<?php
		if($this->totals->playedmatches>0 && $this->highest_home)
		echo $this->highest_home->hometeam." - ".$this->highest_home->guestteam; ?>
		</td>
		<td class="statvalue"><br />
		<?php
		if($this->totals->playedmatches>0 && $this->highest_home)
		echo $this->highest_home->homegoals.$this->overallconfig['seperator'].$this->highest_home->guestgoals; ?>
		</td>
	</tr>
	<tr  class="sectiontableentry1">
		<td class="statlabel"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_HIGHEST_WON_AWAY');?>:</b>
		<br />
		<?php
		if($this->totals->playedmatches>0 && $this->highest_away) {
			$projectteamid = $this->highest_away->project_hometeam_id;
			$homeTeaminfo = sportsmanagementModelProject::getTeaminfo($projectteamid);
			echo $homeTeaminfo->name ." - ".$this->highest_away->guestteam; 
		}
		
		?>
		</td>
		<td class="statvalue"><br />
		<?php
		if($this->totals->playedmatches>0 && $this->highest_away)
		echo $this->highest_away->homegoals.$this->overallconfig['seperator'].$this->highest_away->guestgoals;?>
		</td>
	</tr>
	<?php	else :
		if ( ( $this->highest_home->homegoals - $this->highest_home->guestgoals ) >
		( $this->highest_away->guestgoals - $this->highest_away->homegoals ) )
		{
			$this->highest = $this->highest_home;
		}
		else
		{
			$this->highest = $this->highest_away;
		}
		?>
	<tr  class="sectiontableentry2">
		<td class="statlabel"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_HIGHEST_WIN');?>:</b>
		<br />
		<?php echo $this->highest->hometeam." - ".$this->highest->guestteam; ?>
		</td>
		<td class="statvalue"><br />
		<?php echo $this->highest->homegoals." : ".$this->highest->guestgoals;?>
		</td>
	</tr>
	<?php endif; ?>
</tbody>	
</table>
</div>

</div>
