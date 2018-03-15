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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<!-- Start of show matches through all projects -->
<?php
if ( $this->games )
{
	?>

<h4><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY'); ?></h4>
<table class="<?php echo $this->config['hystory_table_class']; ?>">
	<tr>
		<td>
		<table class="<?php echo $this->config['hystory_table_class']; ?>">
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

                    $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['r'] = $game->round_slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$result_link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);
			
					$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $game->project_slug;
$routeparameter['mid'] = $game->match_slug;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);

					$home = $this->gamesteams[$game->projectteam1_id];
					$away = $this->gamesteams[$game->projectteam2_id];
					?>
			<tr class="">
				<td><?php
				echo JHtml::link( $result_link, $game->roundcode );
				?></td>
				<td class="nowrap"><?php
				echo JHtml::date( $date, JText::_( 'COM_SPORTSMANAGEMENT_MATCHDAYDATE' ) );
				?></td>
				<td><?php
				echo substr( $game->match_date, 11, 5 );
				?></td>
				<td class="nowrap"><?php
				echo $home->name;
				?></td>
                
                <td class="nowrap">
                <?php
                if ( !sportsmanagementHelper::existPicture($home->picture) )
    {
    $home->picture = sportsmanagementHelper::getDefaultPlaceholder('logo_big');    
    }
				echo sportsmanagementHelperHtml::getBootstrapModalImage('nextmatch'.$game->id.'-'.$game->projectteam1_id,$home->picture,$home->name,'20')
				?>
                </td>
                                
				<td class="nowrap">-</td>
                
                <td class="nowrap">
                <?php
                if ( !sportsmanagementHelper::existPicture($away->picture) )
    {
    $away->picture = sportsmanagementHelper::getDefaultPlaceholder('logo_big');    
    }
				echo sportsmanagementHelperHtml::getBootstrapModalImage('nextmatch'.$game->id.'-'.$game->projectteam2_id,$away->picture,$away->name,'20')
				?>
                </td>
                
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
					$desc = JHtml::image( JURI::base()."media/com_sportsmanagement/jl_images/zoom.png",
					JText::_( 'Match Report' ),
					array( "title" => JText::_( 'Match Report' ) ) );
					echo JHtml::link( $report_link, $desc);
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
