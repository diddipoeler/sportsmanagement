<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      default_stats_home.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

?>
	<table class="table table-responsive" >
		<?php
		foreach ( $this->matchplayerpositions as $pos )
		{
			if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) :
			?>
				<tr>
					<td colspan="2" class="positionid">
						<?php echo JText::_( $pos->name ); ?>
					</td>
				</tr>
				<tr>
					<!-- list of home-team -->
					<td>
						<table class="playerstats">
							<thead>
								<tr>
									<th class="playername"><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_NAME'); ?></th>
									<?php 
									if(isset($this->stats[$pos->position_id])) :
										foreach ($this->stats[$pos->position_id] as $stat): ?>
										<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()):?>
											<th><?php echo $stat->getImage(); ?></th>
										<?php endif; 
										endforeach; 
									endif; ?>
								</tr>
							</thead>
							<tbody>
								<?php $person_id_list = array(); ?>
								<?php foreach ( $this->matchplayers as $player ):	?>
								<?php if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam1_id && !in_array($player->person_id, $person_id_list)): ?>
									<tr class="starter">
										<td class="playername">
										<?php
$routeparameter = array();  
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);  
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);  
$routeparameter['p'] = $this->project->slug;  
$routeparameter['tid'] = $player->team_slug;  
$routeparameter['pid'] = $player->person_slug;  
$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);  
										

										    $prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
										    $match_player = sportsmanagementHelper::formatName($prefix,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										    $isFavTeam = in_array( $player->team_id, explode(",",$this->project->fav_team)); 
										    
										    if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
										    {
											echo JHtml::link( $player_link, $match_player );
										    } else {
										        echo $match_player;
										    } 
										?>
										</td>
										<?php foreach ($this->stats[$pos->position_id] as $stat): ?>
											<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()):?>
												<td><?php echo $stat->getMatchPlayerStat($this->model, $player->teamplayer_id); ?></td>
											<?php endif; 
											  endforeach; ?>
									</tr>
									<?php $person_id_list[] = $player->person_id; endif; ?>
								<?php endforeach; ?>
								<?php foreach ( $this->substitutes as $sub ): ?>
									<?php if ($sub->pposid1 == $pos->pposid && $sub->ptid == $this->match->projectteam1_id && !in_array($sub->person_id, $person_id_list)): ?>
									<tr class="sub">
										<td class="playername">
										<?php
$routeparameter = array();  
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);  
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);  
$routeparameter['p'] = $this->project->slug;  
$routeparameter['tid'] = $sub->team_slug;  
$routeparameter['pid'] = $sub->sub_person_slug;  
$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);  										

										    $match_player = sportsmanagementHelper::formatName(null,$sub->firstname,$sub->nickname,$sub->lastname, $this->config["name_format"]);
										    $isFavTeam = in_array( $sub->team_id, explode(",",$this->project->fav_team)); 
										    
										    if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
										    {
											echo JHtml::link( $player_link, $match_player );
										    } else {
										        echo $match_player;
										    } 
										?>
										</td>
										<?php foreach ($this->stats[$pos->position_id] as $stat): ?>
											<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()):?>
												<td><?php echo $stat->getMatchPlayerStat($this->model, $sub->teamplayer_id); ?></td>
											<?php endif; ?>
										<?php endforeach; ?>
									</tr>
									<?php $person_id_list[] = $sub->person_id; endif; ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					</td>
				</tr>
				<?php
			endif;
		}
		
		//staff
		foreach ( $this->matchstaffpositions as $pos )
		{
			if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) :
			?>
				<tr>
					<td colspan="2" class="positionid">
						<?php echo JText::_( $pos->name ); ?>
					</td>
				</tr>
				<tr>
					<!-- list of home-team -->
					<td>
						<table class="playerstats">
							<thead>
								<tr>
									<th class="playername"><?php echo JText::_('Name'); ?></th>
									<?php foreach ($this->stats[$pos->position_id] as $stat): ?>
										<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()):?>
											<th><?php echo $stat->getImage(); ?></th>
										<?php endif; ?>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php	foreach ( $this->matchstaffs as $player ):	?>
								<?php if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam1_id): ?>
									<tr class="starter">
										<td class="playername">
										<?php
										$routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $player->team_slug;
       $routeparameter['pid'] = $player->person_slug;
										$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff',$routeparameter);
										    
										    $match_player = sportsmanagementHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										    $isFavTeam = in_array( $player->team_id, explode(",",$this->project->fav_team)); 
										    
										    if ( ($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)) )
										    {
											echo JHtml::link( $player_link, $match_player );
										    } else {
										        echo $match_player;
										    } 
										?>
										</td>
										<?php foreach ($this->stats[$pos->position_id] as $stat): ?>
											<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()):?>
												<td><?php echo $stat->getMatchStaffStat($this->model, $player->team_staff_id); ?></td>
											<?php endif; ?>
										<?php endforeach; ?>
									</tr>
									<?php endif; ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					</td>	
				</tr>
				<?php
			endif;
		}
		?>
	</table>					
