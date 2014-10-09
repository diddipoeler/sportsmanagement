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

<?php if ($this->previousx[$this->currentteam]) :	?>
<!-- Start of last 5 matches -->

<h2><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_NEXTMATCH_PREVIOUS', $this->allteams[$this->currentteam]->name); ?></h2>
<table width="100%">
	<tr>
		<td>
		<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
			<?php
			$pr_id = 0;
			$k=0;
			
			foreach ( $this->previousx[$this->currentteam] as $game )
			{
				$class = ($k == 0)? 'sectiontableentry1' : 'sectiontableentry2';
				$result_link = sportsmanagementHelperRoute::getResultsRoute($game->project_id,$game->roundid,0,0,0,null,JRequest::getInt('cfg_which_database',0));
				$report_link = sportsmanagementHelperRoute::getMatchReportRoute($game->project_id,$game->id,JRequest::getInt('cfg_which_database',0));
				$home = $this->allteams[$game->projectteam1_id];
				$away = $this->allteams[$game->projectteam2_id];
				?>
			<tr class="<?php echo $class; ?>">
				<td><?php
				echo JHtml::link( $result_link, $game->roundcode );
				?></td>
				<td nowrap="nowrap"><?php
				echo JHtml::date( $game->match_date, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE' ) );
				?></td>
				<td><?php
				echo substr( $game->match_date, 11, 5 );
				?></td>
				<td nowrap="nowrap"><?php
				echo $home->name;
				?></td>
				<td nowrap="nowrap">-</td>
				<td nowrap="nowrap"><?php
				echo $away->name;
				?></td>
				<td nowrap="nowrap"><?php
				echo $game->team1_result;
				?></td>
				<td nowrap="nowrap"><?php echo $this->overallconfig['seperator']; ?></td>
				<td nowrap="nowrap"><?php
				echo $game->team2_result;
				?></td>
				<td nowrap="nowrap"><?php
				if ($game->show_report==1)
				{
					$desc = JHtml::image( JURI::base()."media/com_sportsmanagement/jl_images/zoom.png",
					JText::_( 'Match Report' ),
					array( "title" => JText::_( 'Match Report' ) ) );
					echo JHtml::link( $report_link, $desc);
				}
				$k = 1 - $k;
				?></td>
				<?php	if (($this->config['show_thumbs_picture'])): ?>
				<td><?php echo sportsmanagementHelperHtml::getThumbUpDownImg($game, $this->currentteam); ?></td>
				<?php endif; ?>
			</tr>
			<?php
			}
			?>
		</table>
		</td>
	</tr>
</table>
<!-- End of  show matches -->
<?php endif; ?>