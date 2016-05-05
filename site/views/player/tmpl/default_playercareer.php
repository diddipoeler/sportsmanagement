<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

defined('_JEXEC') or die('Restricted access');

if (count($this->historyPlayer) > 0)
{
	?>
	<!-- Player history START -->
	<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER'); ?></h2>
	<table class="<?PHP echo $this->config['history_table_class']; ?>" >
		<tr>
			<td>
				<table id="playerhistory" class="<?PHP echo $this->config['history_table_class']; ?>" >
					<tr class="sectiontableheader">
						<th class="td_l">
                        <?php echo 
                        JText::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION');
							?>
                            </th>
						<th class="td_l">
                        <?php 
                        echo JText::_('COM_SPORTSMANAGEMENT_PERSON_SEASON');
							?>
                            </th>
                            
                             <?PHP
                if ( $this->config['show_plcareer_team'] )
	{
	   ?>
						<th class="td_l">
                        <?php 
                        echo JText::_('COM_SPORTSMANAGEMENT_PERSON_TEAM');
							?>
                            </th>
                            <?PHP
                            }
                            if ( $this->config['show_plcareer_ppicture'] )
	{
                            ?>
                        <th class="td_l">
                        <?php 
                        echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE');
							?>
                            </th>  
                             <?PHP
                            }
                            
                            ?>  
						<th class="td_l">
                        <?php 
                        echo JText::_('COM_SPORTSMANAGEMENT_PERSON_POSITION');
							?>
                            </th>
					</tr>
					<?php
					$k=0;
					foreach ($this->historyPlayer AS $station)
					{
					   $routeparameter = array();
       $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
       $routeparameter['s'] = JRequest::getInt('s',0);
       $routeparameter['p'] = $station->project_slug;
       $routeparameter['tid'] = $station->team_slug;
       $routeparameter['pid'] = $station->person_slug;
            
                    $link1 = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);
                     $routeparameter = array();
       $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
       $routeparameter['s'] = JRequest::getInt('s',0);
       $routeparameter['p'] = $station->project_slug;
       $routeparameter['tid'] = $station->team_slug;
       $routeparameter['ptid'] = 0;
					$link2 = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
                    

						?>
						<tr class="">
							<td class="td_l">
							<?php
                            if ( $this->config['show_project_logo'] ) 
                            { 
if ( !curl_init( $station->project_picture ) )
				{
					$station->project_picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
				}                                
                            echo sportsmanagementHelperHtml::getBootstrapModalImage('playercareerproject'.$station->project_id.'-'.$station->team_id,$station->project_picture,$station->project_name,'20'); 
							}	
                                echo JHtml::link($link1,$station->project_name);
							?></td>
							<td class="td_l">
                            <?php 
                            echo $station->season_name;
								?>
                                </td>
                                 <?PHP
                if ( $this->config['show_plcareer_team'] )
	{
	   ?>
							<td class="td_l">
                            <?php 
                            if ( $this->config['show_team_logo'] ) 
                            { 
if ( !curl_init( $station->club_picture ) )
				{
					$station->club_picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
				}                                  
                            echo sportsmanagementHelperHtml::getBootstrapModalImage('playercareerteam'.$station->project_id.'-'.$station->team_id,$station->club_picture,$station->team_name,'20');
                            }
							if ( $this->config['show_playercareer_teamlink'] ) 
                            {
								echo JHtml::link($link2,$station->team_name);
							} 
                            else 
                            {
								echo $station->team_name;
							}
							?>
                            </td>
                            <?PHP
                            }
                            if ( $this->config['show_plcareer_ppicture'] )
	{
                            ?>
                            <td>
                <?PHP
                //echo $player_hist->season_picture;
                echo sportsmanagementHelperHtml::getBootstrapModalImage('playercareerperson'.$station->project_id.'-'.$station->team_id,$station->season_picture,$station->team_name,'50');
                ?>
                </td>
                 <?PHP
                            }
                            
                            ?>
							<td class="td_l"><?php echo JText::_($station->position_name);
								?></td>
						</tr>
						<?php
						$k=(1-$k);
					}
					?>
				</table>
			</td>
		</tr>
	</table>

	<!-- Player history END -->
	<?php
}
?>
