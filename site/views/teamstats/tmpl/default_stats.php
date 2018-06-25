<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

?>

<div id="jl_teamstats">
	<div class="jl_teamsubstats">
		<table class="table">
			<thead>
				<tr class="sectiontableheader">
					<th>&nbsp;</th>
					<th align="right" width=""><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_TOTAL'); ?></th>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<th align="right" width=""><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_HOME'); ?></th>
					<th align="right" width=""><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_AWAY'); ?></th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
			<?php
				if ( $this->config['show_general_stats'] )
				{
				$totalmatches = $this->totalshome->totalmatches + $this->totalsaway->totalmatches;
				$totalplayedmatches = $this->totalshome->playedmatches + $this->totalsaway->playedmatches;
			?>
				<tr class="sectiontableentry1">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_OVERALL'); ?>:</td>
					<td class="statvalue"><?php echo $totalmatches; ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><?php echo $this->totalshome->totalmatches; ?></td>
					<td class="statvalue"><?php echo $this->totalsaway->totalmatches; ?></td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_PLAYED'); ?>:</td>
					<td class="statvalue"><?php echo $totalplayedmatches; ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><?php echo $this->totalshome->playedmatches; ?></td>
					<td class="statvalue"><?php echo $this->totalsaway->playedmatches; ?></td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_WIN'); ?>:</td>
					<td class="statvalue"><?php echo count($this->results['win']); ?></td>
					<?php if ( $this->config['home_away_stats'] ):?>
					<td class="statvalue"><?php echo $this->results['home_wins']; ?></td>
					<td class="statvalue"><?php echo $this->results['away_wins']; ?></td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_DRAW'); ?>:</td>
					<td class="statvalue"><?php echo count($this->results['tie']); ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><?php echo $this->results['home_draws']; ?></td>
					<td class="statvalue"><?php echo $this->results['away_draws']; ?></td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_LOST'); ?>:</td>
					<td class="statvalue"><?php echo count($this->results['loss']); ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><?php echo $this->results['home_losses']; ?></td>
					<td class="statvalue"><?php echo $this->results['away_losses']; ?></td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry2">
				<?php if (count($this->results['forfeit'])): ?>
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_FORFEIT'); ?>:</td>
					<td class="statvalue"><?php echo count($this->results['forfeit']); ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><!-- TODO: determine home forfeits --></td>
					<td class="statvalue"><!-- TODO: determine away forfeits --></td>
					<?php endif; ?>
				<?php else: ?>
					<td class="statlabel">&nbsp;</td>
					<td class="statvalue">&nbsp;</td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue">&nbsp;</td>
					<td class="statvalue">&nbsp;</td>
					<?php endif; ?>
				<?php endif; ?>
				</tr>
				<?php if ( $this->config['home_away_stats'] ): ?>
				<tr class="sectiontableentry1">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_HIGHEST_WIN'); ?>:</td>
					<td class="statvalue">
						<?php if (!empty($this->highest_home)): ?>
						<!-- TODO: determine total highest win -->
						<?php endif; ?>
					</td>
					<!-- highest win home -->
					<?php if ( $this->config['home_away_stats'] ): ?>
						<?php if (!empty($this->highest_home)): ?>
					<td class="statvalue">
						<?php 
                        $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['mid'] = $this->highest_home->match_slug;
							$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
							$highest_home_result = $this->highest_home->homegoals . $this->overallconfig['seperator'] . $this->highest_home->guestgoals;
							$routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->highest_home->team1_slug;
       $routeparameter['ptid'] = $this->highest_home->pt1_id;
                            $team1 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
                            $routeparameter['tid'] = $this->highest_home->team2_slug;
       $routeparameter['ptid'] = $this->highest_home->pt2_id;
							$team2 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
							$match	= JHTML::link($link,$highest_home_result) . " -  " . JHTML::link($team2,$this->highest_home->guestteam);
							echo $match;
						?>
					</td>
					<?php else: ?>
					<td class="statvalue">
					----
					</td>
						<?php endif; ?>
					<!-- highest win away -->
						<?php if (!empty($this->highest_away)): ?>
					<td class="statvalue">
						<?php 
                        $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['mid'] = $this->highest_away->match_slug;
							$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
							$highest_away_result = $this->highest_away->guestgoals . $this->overallconfig['seperator'] . $this->highest_away->homegoals;
							$routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->highest_away->team1_slug;
       $routeparameter['ptid'] = $this->highest_away->pt1_id;
                            $team1 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
                            $routeparameter['tid'] = $this->highest_away->team2_slug;
       $routeparameter['ptid'] = $this->highest_away->pt2_id;
							$team2 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
							$match	= JHTML::link($link,$highest_away_result) . " - " . JHTML::link($team1,$this->highest_away->hometeam) ;
							echo $match;
						?>
					</td>
					<?php else: ?>
					<td class="statvalue">
					----
					</td>
						<?php endif; ?>
					<?php endif; ?>
				</tr>
                
                
				<tr class="sectiontableentry2">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_HIGHEST_LOSS'); ?>:</td>
					<td class="statvalue">
						<?php if (!empty($this->highestdef_home)): ?>
						<!-- TODO: determine total highest loss -->
						<?php endif; ?>
					</td>
					<!-- highest defeat home -->
					<?php if ( $this->config['home_away_stats'] ): ?>
						<?php if (!empty($this->highestdef_home)): ?>
					<td class="statvalue">
						<?php 
                        $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['mid'] = $this->highestdef_home->match_slug;
							$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
							$highestdef_home_result = $this->highestdef_home->homegoals . $this->overallconfig['seperator'] . $this->highestdef_home->guestgoals;
							
                            $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->highestdef_home->team1_slug;
       $routeparameter['ptid'] = $this->highestdef_home->pt1_id;
                            $team1 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
                            $routeparameter['tid'] = $this->highestdef_home->team2_slug;
       $routeparameter['ptid'] = $this->highestdef_home->pt2_id;
							$team2 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
							$match	= JHTML::link($link,$highestdef_home_result) . " - " . JHTML::link($team2,$this->highestdef_home->guestteam);
							echo $match;
						?>
					</td>
						<?php else: ?>
					<td class="statvalue">
					----
					</td>
						<?php endif; ?>
					<!-- highest defeat away -->
						<?php if (!empty($this->highestdef_away)): ?>
					<td class="statvalue">
						<?php 
                        $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['mid'] = $this->highestdef_away->match_slug;
							$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
							$highestdef_away_result = $this->highestdef_away->guestgoals . $this->overallconfig['seperator'] . $this->highestdef_away->homegoals;
                            $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->highestdef_away->team1_slug;
       $routeparameter['ptid'] = $this->highestdef_away->pt1_id;
							$team1 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
                            $routeparameter['tid'] = $this->highestdef_away->team2_slug;
       $routeparameter['ptid'] = $this->highestdef_away->pt2_id;
							$team2 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
							$match	= JHTML::link($link,$highestdef_away_result) . " - " . JHTML::link($team1,$this->highestdef_away->hometeam);
							echo $match;
						?>
					</td>
						<?php else: ?>
					<td class="statvalue">
					----
					</td>
						<?php endif; ?>
					<?php endif; ?>
				</tr>
                
                <tr class="sectiontableentry2">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_MATCHES_HIGHEST_DRAW'); ?>:</td>
					<td class="statvalue">
						<?php if (!empty($this->highestdraw_home)): ?>
						<!-- TODO: determine total highest loss -->
						<?php endif; ?>
					</td>
					<!-- highest defeat home -->
					<?php if ( $this->config['home_away_stats'] ): ?>
						<?php if (!empty($this->highestdraw_home)): ?>
					<td class="statvalue">
						<?php 
                        $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['mid'] = $this->highestdraw_home->match_slug;
							$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
							$highestdef_home_result = $this->highestdraw_home->homegoals . $this->overallconfig['seperator'] . $this->highestdraw_home->guestgoals;
                            $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->highestdraw_home->team1_slug;
       $routeparameter['ptid'] = $this->highestdraw_home->pt1_id;
							$team1 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
                            $routeparameter['tid'] = $this->highestdraw_home->team2_slug;
       $routeparameter['ptid'] = $this->highestdraw_home->pt2_id;
							$team2 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
							$match	= JHTML::link($link,$highestdef_home_result) . " - " . JHTML::link($team2,$this->highestdraw_home->guestteam);
							echo $match;
						?>
					</td>
						<?php else: ?>
					<td class="statvalue">
					----
					</td>
						<?php endif; ?>
					<!-- highest defeat away -->
						<?php if (!empty($this->highestdraw_away)): ?>
					<td class="statvalue">
						<?php 
                        $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['mid'] = $this->highestdraw_away->match_slug;
							$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
							$highestdef_away_result = $this->highestdraw_away->guestgoals . $this->overallconfig['seperator'] . $this->highestdraw_away->homegoals;
                            $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->highestdraw_away->team1_slug;
       $routeparameter['ptid'] = $this->highestdraw_away->pt1_id;
							$team1 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
                            $routeparameter['tid'] = $this->highestdraw_away->team2_slug;
       $routeparameter['ptid'] = $this->highestdraw_away->pt2_id;
							$team2 	= sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
							$match	= JHTML::link($link,$highestdef_away_result) . " - " . JHTML::link($team1,$this->highestdraw_away->hometeam);
							echo $match;
						?>
					</td>
						<?php else: ?>
					<td class="statvalue">
					----
					</td>
						<?php endif; ?>
					<?php endif; ?>
				</tr>
                
                
                
				<?php endif; ?>
				<?php
				}
				// ======================= Goal statistics ==========================
				if ( $this->config['show_goals_stats'] )
				{
					$totalGoals = $this->totalshome->totalgoals + $this->totalsaway->totalgoals;
					$totalGoalsFor = $this->totalshome->goalsfor + $this->totalsaway->goalsfor;
					$totalGoalsAgainst = $this->totalshome->goalsagainst + $this->totalsaway->goalsagainst;
					$totalPlayedMatches = $this->totalshome->playedmatches + $this->totalsaway->playedmatches;
				?>
				<tr class="sectiontableentry1">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL'); ?>:</td>
					<td class="statvalue"><?php echo $totalGoals; ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><?php echo $this->totalshome->totalgoals; ?></td>
					<td class="statvalue"><?php echo $this->totalsaway->totalgoals; ?></td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_TOTAL_PER_MATCH'); ?>:</td>
					<td class="statvalue"><?php echo empty($totalPlayedMatches) ? 0 : round ( ( $totalGoals / $totalPlayedMatches ), 2 ); ?></td>
					<?php if ( $this->config['home_away_stats'] ) : ?>
					<td class="statvalue">
						<?php echo empty($this->totalshome->playedmatches) ? 0 : round ( ( $this->totalshome->totalgoals / $this->totalshome->playedmatches ), 2 ); ?>
					</td>
					<td class="statvalue">
						<?php echo empty($this->totalsaway->playedmatches) ? 0 : round ( ( $this->totalsaway->totalgoals / $this->totalsaway->playedmatches ), 2 ); ?>
					</td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR'); ?></td>
					<td class="statvalue"><?php echo $totalGoalsFor; ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><?php echo $this->totalshome->goalsfor; ?></td>
					<td class="statvalue"><?php echo $this->totalsaway->goalsfor; ?></td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_FOR_PER_MATCH');?>:</td>
					<td class="statvalue"><?php echo empty($totalPlayedMatches) ? 0 : round( ( $totalGoalsFor / $totalPlayedMatches ), 2 ); ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue">
						<?php echo empty($this->totalshome->playedmatches) ? 0 : round ( ( $this->totalshome->goalsfor / $this->totalshome->playedmatches ), 2 ); ?>
					</td>
					<td class="statvalue">
						<?php echo empty($this->totalsaway->playedmatches) ? 0 : round ( ( $this->totalsaway->goalsfor / $this->totalsaway->playedmatches ), 2 ); ?>
					</td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST'); ?></td>
					<td class="statvalue"><?php echo $totalGoalsAgainst; ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><?php echo $this->totalshome->goalsagainst; ?></td>
					<td class="statvalue"><?php echo $this->totalsaway->goalsagainst; ?></td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry2">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_GOALS_AGAINST_PER_MATCH'); ?>:</td>
					<td class="statvalue"><?php echo empty($totalPlayedMatches) ? 0 : round( ( $totalGoalsAgainst / $totalPlayedMatches ), 2 ); ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue">
						<?php echo empty($this->totalshome->playedmatches) ? 0 : round ( ( $this->totalshome->goalsagainst / $this->totalshome->playedmatches ), 2 ); ?>
					</td>
					<td class="statvalue">
						<?php echo empty($this->totalsaway->playedmatches) ? 0 : round ( ( $this->totalsaway->goalsagainst / $this->totalsaway->playedmatches ), 2 ); ?>
					</td>
					<?php endif; ?>
				</tr>
				<tr class="sectiontableentry1">
					<td class="statlabel"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMSTATS_NO_GOALS_AGAINST'); ?></td>
					<td class="statvalue"><?php echo empty($this->nogoals_against->totalzero) ? 0 : $this->nogoals_against->totalzero; ?></td>
					<?php if ( $this->config['home_away_stats'] ): ?>
					<td class="statvalue"><?php echo empty($this->nogoals_against->homezero) ? 0 : $this->nogoals_against->homezero; ?></td>
					<td class="statvalue"><?php echo empty($this->nogoals_against->awayzero) ? 0 : $this->nogoals_against->awayzero; ?></td>
					<?php endif; ?>
				</tr>
				<?php
				}
			?>
			</tbody>
		</table>
	</div>
</div>
<div class="clr"></div>
