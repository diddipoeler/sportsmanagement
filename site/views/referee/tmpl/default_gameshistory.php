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


<?php if (count($this->games))
{
	?>
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY'); ?></h2>
<table class="<?php echo $this->config['history_table_class']; ?>">
	<tr>
		<td><br />
			<table class="<?php echo $this->config['history_table_class']; ?>">
				<thead>
					<tr class="sectiontableheader">
						<th colspan="6"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_GAMES'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$k=0;
				foreach ($this->games as $game)
				{

$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $game->id;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
				    
					//$report_link=sportsmanagementHelperRoute::getMatchReportRoute($this->project->slug,$game->id);
					?>

					<tr class="">
						<td><?php
						echo JHtml::link($report_link,strftime($this->config['games_date_format'],strtotime($game->match_date)));
						?>
						</td>
						<td class="td_r">
                        <?php 
echo sportsmanagementHelperHtml::getBootstrapModalImage('gamehistory'.$game->id.'-'.$game->projectteam1_id,$game->home_logo,$game->home_name,'20');                        
                        echo $this->teams[$game->projectteam1_id]->name; 
                        ?>
						</td>
						<td class="td_r"><?php echo $game->team1_result; ?></td>
						<td class="td_c"><?php echo $this->overallconfig['seperator']; ?>
						</td>
						<td class="td_l"><?php echo $game->team2_result; ?></td>
						<td class="td_l">
                        <?php 
echo sportsmanagementHelperHtml::getBootstrapModalImage('gamehistory'.$game->id.'-'.$game->projectteam2_id,$game->away_logo,$game->away_name,'20');                        
                        echo $this->teams[$game->projectteam2_id]->name; 
                        ?>
						</td>
					</tr>
							<?php
							$k=(1-$k);
						}
						?>
					</tbody>
			</table>
		</td>
	</tr>
</table>
<br />
	<?php
}
?>