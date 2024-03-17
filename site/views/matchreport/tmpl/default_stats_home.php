<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_stats_home.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<div class="<?php echo $this->divclassrow; ?> " id="matchreport-stats-home">
    
		<?php
		foreach ($this->matchplayerpositions as $pos)
		{
			if (isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id]) > 0)
				:
				?>
                
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="">
                  
						<?php echo Text::_($pos->name); ?>
                  </div>
                
               
                    <!-- list of home-team -->
               
                            <div class="row">
                            
                                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-start" style="">
                                  <?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_NAME'); ?>
                                  </div>
                              <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 d-flex justify-content-start" style="">
								<?php
								if (isset($this->stats[$pos->position_id]))
									:
									foreach ($this->stats[$pos->position_id] as $stat)
										:
										?>
										<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport())
										:
										?>
                                        <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 d-flex justify-content-start" style="">
                                          <?php echo $stat->getImage(); ?>
                                          </div>
									<?php endif;
									endforeach;
								endif; ?>
                            </div>
                            </div>
                            
							<?php $person_id_list = array(); ?>
							<?php
          
							foreach ($this->matchplayers as $player)
								:
								?>
								<?php if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam1_id && !in_array($player->person_id, $person_id_list))
								:
								?>
                                <div class="row">
                                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-start" style="">
										<?php
										$routeparameter                       = array();
										$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
										$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
										$routeparameter['p']                  = $this->project->slug;
										$routeparameter['tid']                = $player->team_slug;
										$routeparameter['pid']                = $player->person_slug;
										$player_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);


										$prefix       = $player->jerseynumber ? $player->jerseynumber . "." : null;
										$match_player = sportsmanagementHelper::formatName($prefix, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
										$isFavTeam    = in_array($player->team_id, explode(",", $this->project->fav_team));

										if (($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)))
										{
											echo HTMLHelper::link($player_link, $match_player);
										}
										else
										{
											echo $match_player;
										}
										?>
                                    </div>
                                   <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 d-flex justify-content-start" style="">
									<?php foreach ($this->stats[$pos->position_id] as $stat)
										:
										?>
										<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport())
										:
										?>
                                         <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 d-flex justify-content-start" style="">
                                           <?php echo $stat->getMatchPlayerStat($this->model, $player->teamplayer_id); ?>
                                  </div>
                                     
									<?php endif;
									endforeach; ?>
                                     </div>
                                </div>
								<?php $person_id_list[] = $player->person_id;
							endif; ?>
							<?php endforeach; ?>
                              
                              
                              
                              
                              
							<?php
							foreach ($this->substitutes as $sub)
								:
								?>
								<?php if ($sub->pposid1 == $pos->pposid && $sub->ptid == $this->match->projectteam1_id && !in_array($sub->person_id, $person_id_list))
								:
								?>
                                 <div class="row">
                                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-start" style="">
										<?php
										$routeparameter                       = array();
										$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
										$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
										$routeparameter['p']                  = $this->project->slug;
										$routeparameter['tid']                = $sub->team_slug;
										$routeparameter['pid']                = $sub->sub_person_slug;
										$player_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

										$match_player = sportsmanagementHelper::formatName(null, $sub->firstname, $sub->nickname, $sub->lastname, $this->config["name_format"]);
										$isFavTeam    = in_array($sub->team_id, explode(",", $this->project->fav_team));

										if (($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)))
										{
											echo HTMLHelper::link($player_link, $match_player);
										}
										else
										{
											echo $match_player;
										}
										?>
                                    </div>
                                   <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 d-flex justify-content-start" style="">
									<?php foreach ($this->stats[$pos->position_id] as $stat)
										:
										?>
										<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport())
										:
										?>
                                        <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 d-flex justify-content-start" style="">
                                          <?php echo $stat->getMatchPlayerStat($this->model, $sub->teamplayer_id); ?>
                                          </div>
									<?php endif; ?>
									<?php endforeach; ?>
                                </div>
                                </div>
								<?php $person_id_list[] = $sub->person_id;
							endif; ?>
							<?php endforeach; ?>
                              
                              
                            
                       
                    
                
			<?php
			endif;
		}

		// Staff
		foreach ($this->matchstaffpositions as $pos)
		{
			if (isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id]) > 0)
				:
				?>
                 <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="">
                  
						<?php echo Text::_($pos->name); ?>
                  </div>
                
                    <!-- list of home-team -->
                    
                         <div class="row">
                            
                                <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-start" style="">
                                <?php echo Text::_('Name'); ?>
                                </div>
                                <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 d-flex justify-content-start" style="">
                                
								<?php
								foreach ($this->stats[$pos->position_id] as $stat)
									:
									?>
									<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport())
									:
									?>
                                    <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 d-flex justify-content-start" style="">
                                    <?php echo $stat->getImage(); ?>
                                    </div>
								<?php endif; ?>
								<?php endforeach; ?>
                            
                            
                            
                            
                            </div>
                            </div>
                            
                            
                            
                            
							<?php foreach ($this->matchstaffs as $player)
								:
								?>
								<?php if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam1_id)
								:
								?>
                                 <div class="row">
                                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5 d-flex justify-content-start" style="">
										<?php
										$routeparameter                       = array();
										$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
										$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
										$routeparameter['p']                  = $this->project->slug;
										$routeparameter['tid']                = $player->team_slug;
										$routeparameter['pid']                = $player->person_slug;
										$player_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('staff', $routeparameter);

										$match_player = sportsmanagementHelper::formatName(null, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
										$isFavTeam    = in_array($player->team_id, explode(",", $this->project->fav_team));

										if (($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)))
										{
											echo HTMLHelper::link($player_link, $match_player);
										}
										else
										{
											echo $match_player;
										}
										?>
                                    </div>
                                    <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7 d-flex justify-content-start" style="">
									<?php foreach ($this->stats[$pos->position_id] as $stat)
										:
										?>
										<?php if ($stat->showInSingleMatchReports() && $stat->showInMatchReport())
										:
										?>
                                        <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 d-flex justify-content-start" style="">
                                        <?php echo $stat->getMatchStaffStat($this->model, $player->team_staff_id); ?>
                                        </div>
									<?php endif; ?>
									<?php endforeach; ?>
                                    </div>
                                
							<?php endif; ?>
                            
                            
							<?php endforeach; ?>
                            
                            
                        
                    
                
			<?php
			endif;
		}
		?>
    
</div>
