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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<!-- Start of show matches through all projects -->
<?php
if ( $this->games )
{
	?>

<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY'); ?></h2>
<table width="100%">
	<tr>
		<td>
		<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
			<?php
			//sort games by dates
			$gamesByDate = Array();

			$pr_id = 0;
			$k=0;
			foreach ( $this->games as $game )
			{
				$gamesByDate[substr( $game->match_date, 0, 10 )][] = $game;
			}
			// $teams = $this->project->getTeamsFromMatches( $this->games );

			foreach ( $gamesByDate as $date => $games )
			{
				foreach ( $games as $game )
				{
					if ($game->prid != $pr_id)
					{
						?>
			<thead>
			<tr class="sectiontableheader">
				<th colspan=10><?php echo $game->project_name;?></th>
			</tr>
			</thead>
			<?php
			$pr_id = $game->prid;
					}
					?>
					<?php
					$class = ($k == 0)? 'sectiontableentry1' : 'sectiontableentry2';
					$result_link = sportsmanagementHelperRoute::getResultsRoute( $game->project_id,$game->roundid);
					$report_link = sportsmanagementHelperRoute::getMatchReportRoute( $game->project_id,$game->id);
					$home = $this->gamesteams[$game->projectteam1_id];
					$away = $this->gamesteams[$game->projectteam2_id];
					?>
			<tr class="<?php echo $class; ?>">
				<td><?php
				echo JHTML::link( $result_link, $game->roundcode );
				?></td>
				<td class="nowrap"><?php
				echo JHTML::date( $date, JText::_( 'COM_SPORTSMANAGEMENT_MATCHDAYDATE' ) );
				?></td>
				<td><?php
				echo substr( $game->match_date, 11, 5 );
				?></td>
				<td class="nowrap"><?php
				echo $home->name;
				?></td>
				<td class="nowrap">-</td>
				<td class="nowrap"><?php
				echo $away->name;
				?></td>
				<td class="nowrap"><?php
				echo $game->team1_result;
				?></td>
				<td class="nowrap"><?php echo $this->overallconfig['seperator']; ?></td>
				<td class="nowrap"><?php
				echo $game->team2_result;
				?></td>
				<td class="nowrap"><?php
				if ($game->show_report==1)
				{
					$desc = JHTML::image( "media/com_sportsmanagement/jl_images/zoom.png",
					JText::_( 'Match Report' ),
					array( "title" => JText::_( 'Match Report' ) ) );
					echo JHTML::link( $report_link, $desc);
				}
				$k = 1 - $k;
				?></td>
			</tr>
			<?php
				}
			}
			?>
		</table>
		</td>
	</tr>
</table>
<!-- End of  show matches through all projects -->
			<?php
}
?>
