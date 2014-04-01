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



<fieldset>
<legend>
<strong>
<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY'); ?>
</strong>
</legend>

<table width="100%" class="fixtures">
<thead>
	<tr class="sectiontableheader">
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON'); ?></th>
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE'); ?></th>
		<?php if($this->project->project_type=='DIVISIONS_LEAGUE') { ?> 
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; ">><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_DIVISION'); ?></th>
		<?php } ?> 
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_RANK'); ?></th>
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES'); ?></th>
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_POINTS'); ?></th>
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL'); ?></th>
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS'); ?></th>
		<th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS'); ?></th>
        
        <?PHP
        if( $this->config['show_teams_roster_mean_age'] )
        {
        ?>
        <th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS_MEAN_AGE'); ?></th>
        <?PHP    
        }
        if( $this->config['show_teams_roster_market_value'] )
        {
        ?>
        <th class="title" nowrap="nowrap" style="vertical-align:top;background:#BDBDBD; "><?php echo JText::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?></th>
        <?PHP    
        }
        ?>
    
    
	</tr>
	</thead>
	<?php
	$k=0;
	foreach ($this->seasons as $season)
	{
		$ranking_link   = sportsmanagementHelperRoute::getRankingRoute($season->project_slug, $season->current_round, null, null, 0, $season->division_slug);
		$results_link   = sportsmanagementHelperRoute::getResultsRoute($season->project_slug, $season->current_round, $season->division_slug);
		$teamplan_link  = sportsmanagementHelperRoute::getTeamPlanRoute($season->project_slug, $this->team->slug, $season->division_slug, NULL);
		$teamstats_link = sportsmanagementHelperRoute::getTeamStatsRoute($season->project_slug, $this->team->slug);
		$players_link   = sportsmanagementHelperRoute::getPlayersRoute($season->project_slug, $season->team_slug,null,$season->ptid);
		?>
	<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
		<td><?php echo $season->season; ?></td>
		<td><?php echo $season->league; ?></td>
		<?php if($this->project->project_type=='DIVISIONS_LEAGUE') { ?> 
		<td><?php echo $season->division_name; ?></td>
		<?php } ?> 
		<?php if($this->config['show_teams_ranking_link'] == 1): ?>
		<td><?php echo JHtml::link($ranking_link, $season->rank); ?></td>
		<?php else: ?>
		<td><?php echo $season->rank; ?></td>
		<?php endif; ?>
		<td><?php echo $season->games; ?></td>
		<?php if($this->config['show_teams_results_link'] == 1): ?>
		<td><?php echo JHtml::link($results_link, $season->points);	?></td>
		<?php else: ?>
		<td><?php echo $season->points; ?></td>
		<?php endif; ?>
		<?php if($this->config['show_teams_teamplan_link'] == 1): ?>
		<td><?php echo JHtml::link($teamplan_link, $season->series); ?></td>
		<?php else: ?>
		<td><?php echo $season->series; ?></td>
		<?php endif; ?>
		<?php if($this->config['show_teams_teamstats_link'] == 1): ?>
		<td><?php echo JHtml::link($teamstats_link, $season->goals); ?></td>
		<?php else: ?>
		<td><?php echo $season->goals; ?></td>
		<?php endif; ?>
		<?php if($this->config['show_teams_roster_link'] == 1): ?>
		<td><?php echo JHtml::link($players_link, $season->playercnt); ?></td>
		<?php else: ?>
		<td><?php echo $season->playercnt; ?></td>
		<?php endif; ?>
    
    <?php if($this->config['show_teams_roster_mean_age'] == 1): ?>
		<td align="right"><?php echo JHtml::link($players_link, $season->playermeanage); ?></td>
		<?php else: ?>
		
		<?php endif; ?>
        
        <?php if($this->config['show_teams_roster_market_value'] == 1): ?>
		<td align="right"><?php echo JHtml::link($players_link, number_format($season->market_value,0, ",", ".") ); ?></td>
		<?php else: ?>
		
		<?php endif; ?>
    
	</tr>
	<?php
	$k = 1 - $k;
	}
	?>
</table>
