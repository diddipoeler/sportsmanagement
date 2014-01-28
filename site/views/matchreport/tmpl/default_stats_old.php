<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<!-- START: game stats -->
<?php
if (!empty($this->matchplayerpositions ))
{
	$hasMatchPlayerStats = false;
	$hasMatchStaffStats = false;
	foreach ( $this->matchplayerpositions as $pos )
	{
		if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) {
			foreach ($this->stats[$pos->position_id] as $stat) {
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()) {
					$hasMatchPlayerStats = true;
					break;
				}
			}
		}
	}	
	foreach ( $this->matchstaffpositions as $pos )
	{
		if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) {
			foreach ($this->stats[$pos->position_id] as $stat) {
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()) {
					$hasMatchStaffStats = true;
				}
			}
		}
	}
	if($hasMatchPlayerStats || $hasMatchStaffStats) :
	?>

<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STATISTICS'); ?></h2>	
	<table class="matchstats" border="0">
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
								<?php foreach ( $this->matchplayers as $player ):	?>
								<?php if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam1_id): ?>
									<tr class="starter">
										<td class="playername">
										<?php
										    $player_link = JoomleagueHelperRoute::getPlayerRoute( $this->project->slug, $player->team_slug, $player->person_slug );
										    $prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
										    $match_player = JoomleagueHelper::formatName($prefix,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										    echo JHtml::link( $player_link, $match_player );
										?>
										</td>
										<?php foreach ($this->stats[$pos->position_id] as $stat): ?>
											<?php if ($stat->showInMatchReport()):?>
												<td><?php echo $stat->getMatchPlayerStat($this->model, $player->teamplayer_id); ?></td>
											<?php endif; 
											  endforeach; ?>
									</tr>
									<?php endif; ?>
								<?php endforeach; ?>
								<?php	foreach ( $this->substitutes as $sub ):	?>
									<?php if ($sub->pposid1 == $pos->pposid && $sub->ptid == $this->match->projectteam1_id): ?>
									<tr class="sub">
										<td class="playername">
										<?php
										    $player_link = JoomleagueHelperRoute::getPlayerRoute( $this->project->slug, $sub->team_slug, $sub->person_slug );
										    $match_player = JoomleagueHelper::formatName(null,$sub->firstname,$sub->nickname,$sub->lastname, $this->config["name_format"]);
										    echo JHtml::link( $player_link, $match_player );
										?>
										</td>
										<?php foreach ($this->stats[$pos->position_id] as $stat): ?>
											<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()):?>
												<td><?php echo $stat->getMatchPlayerStat($this->model, $sub->teamplayer_id); ?></td>
											<?php endif; ?>
										<?php endforeach; ?>
									</tr>
									<?php endif; ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					</td>
					<!-- list of guest-team -->
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
										<?php endif; ?>
									<?php 
										endforeach;
									endif;  
									?>
								</tr>
							</thead>
							<tbody>
								<?php	foreach ( $this->matchplayers as $player ):	?>
								<?php if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam2_id): ?>
									<tr class="starter">
										<td class="playername">
										<?php
										    $player_link = JoomleagueHelperRoute::getPlayerRoute( $this->project->slug, $player->team_slug, $player->person_slug );
										    $prefix = $player->jerseynumber ? $player->jerseynumber."." : null;
										    $match_player = JoomleagueHelper::formatName($prefix,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										    echo JHtml::link( $player_link, $match_player );
										?>
										</td>
										<?php 
										if(isset($this->stats[$pos->position_id])) :
											foreach ($this->stats[$pos->position_id] as $stat): ?>
											<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()):?>
												<td><?php echo $stat->getMatchPlayerStat($this->model, $player->teamplayer_id); ?></td>
											<?php endif; ?>
										<?php 
											endforeach; 
										endif; 
										?>
									</tr>
									<?php endif; ?>
								<?php endforeach; ?>
								<?php	foreach ( $this->substitutes as $sub ):	?>
									<?php if ($sub->pposid2 == $pos->pposid && $sub->ptid == $this->match->projectteam2_id): ?>
									<tr class="sub">
										<td class="playername">
										<?php
										    $player_link = JoomleagueHelperRoute::getPlayerRoute( $this->project->slug, $sub->team_slug, $sub->person_slug );
										    $match_player = JoomleagueHelper::formatName(null,$sub->firstname,$sub->nickname,$sub->lastname, $this->config["name_format"]);
										    echo JHtml::link( $player_link, $match_player );
										?>
										</td>
										<?php foreach ($this->stats[$pos->position_id] as $stat): ?>
											<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()):?>
												<td><?php echo $stat->getMatchPlayerStat($this->model, $sub->teamplayer_id); ?></td>
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
		endif;
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
										    $player_link = JoomleagueHelperRoute::getStaffRoute( $this->project->slug, $player->team_slug, $player->person_slug );
										    $match_player = JoomleagueHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										    echo JHtml::link( $player_link, $match_player ); ?>
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
					<!-- list of guest-team -->
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
								<?php if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam2_id): ?>
									<tr class="starter">
										<td class="playername">
										<?php
										    $player_link = JoomleagueHelperRoute::getStaffRoute( $this->project->slug, $player->team_slug, $player->person_slug );
										    $match_player = JoomleagueHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										    echo JHtml::link( $player_link, $match_player );
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
	<?php
}
?>
<!-- END of game stats -->
