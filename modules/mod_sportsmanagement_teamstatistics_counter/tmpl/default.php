<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_trainingsdata
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/**
 * Offene Punkte
 *      - Counter
 *      - Language Files
 */

$team    = $data['team'];
$project = $data['project'];
$stats   = $data['stats'];

//echo "tuku";

$mode = $params->get('mode');

switch ($mode)
{
	/**
	 *
	 * Classic mode template
	 */
	case 'C':
		?>


<section id="counter" class="counter">
    <div class="main_counter_area">
        <div class="overlay p-y-3">
            <div class="container">
                <div class="row">
					<?php if ($params->get('show_project_name'))
						:
						?>
                        <div class="col-12 text-center">
                            <h4><?php echo $project->name; ?></h4>
                        </div>
					<?php endif; ?>

					<?php if ($params->get('show_team_name'))
						:
						?>
                        <div class="col-12 text-center">
                            <h4><?php echo $team->name; ?></h4>
                        </div>
					<?php endif; ?>
                </div>

                <div class="row">
                    <div class="main_counter_content text-center white-text wow fadeInUp">

						<?php if ($params->get('show_round_numbers'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/startroster.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter"><?php echo $stats['totalrounds']; ?></h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_ROUND_NUMBERS'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_played_matches'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/shirt.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter"><?php echo $stats['totalshome']->playedmatches + $stats['totalsaway']->playedmatches; ?></h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_PLAYED_MATCHES'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_wins'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/win.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter"><?php echo count($stats['results']['win']); ?></h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_WINS'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_draws'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/draw.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter"><?php echo count($stats['results']['tie']); ?></h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_DRAWS'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_loses'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/lose.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter"><?php echo count($stats['results']['loss']); ?></h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_LOSES'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_goals'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/goal.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter">
										<?php echo $stats['totalshome']->totalgoals + $stats['totalsaway']->totalgoals; ?>
                                    </h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_GOALS'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_goals_per_match'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/goal.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter">
										<?php
										$totalGoals         = $stats['totalshome']->totalgoals + $stats['totalsaway']->totalgoals;
										$totalPlayedMatches = $stats['totalshome']->playedmatches + $stats['totalsaway']->playedmatches;
										echo empty($totalPlayedMatches) ? 0 : round(($totalGoals / $totalPlayedMatches), 2);
										?>
                                    </h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_GOALS_PER_MATCH'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_scoring_goals'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/goal.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter">
										<?php echo $stats['totalshome']->goalsfor + $stats['totalsaway']->goalsfor; ?>
                                    </h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_SCORING_GOALS'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_scoring_goals_per_match'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/goal.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter">
										<?php
										$totalGoals         = $stats['totalshome']->goalsfor + $stats['totalsaway']->goalsfor;
										$totalPlayedMatches = $stats['totalshome']->playedmatches + $stats['totalsaway']->playedmatches;
										echo empty($totalPlayedMatches) ? 0 : round(($totalGoals / $totalPlayedMatches), 2);
										?>
                                    </h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_SCORING_GOALS_PER_MATCH'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_against_goals'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/own_goal.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter">
										<?php echo $stats['totalshome']->goalsagainst + $stats['totalsaway']->goalsagainst; ?>
                                    </h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_AGAINST_GOALS'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_against_goals_per_match'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/own_goal.png' class="m-b-1"
                                         style="height: 20px;"/>
                                    <h2 class="statistic-counter">
										<?php
										$totalGoals         = $stats['totalshome']->goalsagainst + $stats['totalsaway']->goalsagainst;
										$totalPlayedMatches = $stats['totalshome']->playedmatches + $stats['totalsaway']->playedmatches;
										echo empty($totalPlayedMatches) ? 0 : round(($totalGoals / $totalPlayedMatches), 2);
										?>
                                    </h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_AGAINST_GOALS_PER_MATCH'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>

						<?php if ($params->get('show_clean_sheets'))
							:
							?>
                            <div class="col-md-3">
                                <div class="single_counter p-y-2 m-t-1">
                                    <img src='images/com_sportsmanagement/database/events/clean-sheets.png'
                                         class="m-b-1" style="height: 20px;"/>
                                    <h2 class="statistic-counter">
										<?php echo empty($stats['nogoals_against']->totalzero) ? 0 : $stats['nogoals_against']->totalzero; ?>
                                    </h2>
                                    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_CLEAN_SHEETS'); ?></p>
                                </div>
                            </div>
						<?php endif; ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

		
		
	<?php
	break;

	/**
	 *
	 * Sticker mode template
	 */
	case 'S':
	
		$border = $params->get('border');
		$border_color = $params->get('border_color');
		$border_rounded = $params->get('border_rounded');
		$border_shadow = $params->get('border_shadow');
		$background_color = $params->get('background_color');
		$text_color = $params->get('text_color');
		$text_size = $params->get('text_size');
		$title_color = $params->get('title_color');
		$title_size = $params->get('title_size');
		
		$style = "";
		
		if ($border)
		 {
			$style = "border: 1px solid " . $border_color . "; " ;
			$style = $style . "background-color: " . $background_color . "; " ;
			if($border_rounded)
			{
				$style =  $style . 'border-radius: 20px ; ';
			}
			if($border_shadow)
			{
				$style =  $style . 'box-shadow: 10px 10px 6px 3px #474747; ';
			}			
			
		 }	

		$style = $style . 'width:250px; ';
		$style = $style . 'margin: 0px 0px 30px 0px;';

			?>	
				
		<div class="container-fluid" style="<? echo $style;?>
											">
											
			<?PHP	
			
				if ($params->get('show_project_name'))
					{							
					?> 	<div>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $title_size;?>px; 
										">	<?php echo $project->name .'<br>'; ?>  </p></div>
				
					<?
					}
					
				if ($params->get('show_team_name'))
					{							
					?>  <div>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $title_size;?>px; 
										">	<?php echo $team->name; ?> </p></div>
				
					<?
					}	

				if ($params->get('show_round_numbers'))
					{							
					?>
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/startroster.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo $stats['totalrounds'] . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_ROUND_NUMBERS'); ?>
						</p>
						</div>
				
					<?
					}	
					
				if ($params->get('show_played_matches'))
					{							
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/shirt.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo $stats['totalshome']->playedmatches + $stats['totalsaway']->playedmatches . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_PLAYED_MATCHES'); ?>
						</p>
						</div>
				
					<?
					}	
				// Show wins
				if ($params->get('show_wins'))
					{							
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/win.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo count($stats['results']['win']) . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_WINS'); ?>
						</p>
						</div>
					<?
					}		
				// Show draws
				if ($params->get('show_draws'))
					{							
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/draw.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo count($stats['results']['tie']) . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_DRAWS'); ?>
						</p>
						</div>
					<?
					}
				// Show loses
				if ($params->get('show_loses'))
					{							
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/lose.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo count($stats['results']['loss']) . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_LOSES'); ?>
						</p>
						</div>
					<?
					}						
				// Show goals
				if ($params->get('show_goals'))
					{							
					?> 	
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/goal.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo $stats['totalshome']->totalgoals + $stats['totalsaway']->totalgoals . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_GOALS'); ?>
						</p>
						</div>
					<?
					}

				// Show goals per match
				if ($params->get('show_goals_per_match'))
					{	
						$totalGoals         = $stats['totalshome']->totalgoals + $stats['totalsaway']->totalgoals;
						$totalPlayedMatches = $stats['totalshome']->playedmatches + $stats['totalsaway']->playedmatches;
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/goal.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo empty($totalPlayedMatches) ? 0 : round(($totalGoals / $totalPlayedMatches), 2) . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_GOALS_PER_MATCH'); ?>
										</p>
						</div>
				
					<?
					}						
				
				// Show total goals for
				if ($params->get('show_scoring_goals'))
					{							
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/goal.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo $stats['totalshome']->goalsfor + $stats['totalsaway']->goalsfor . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_SCORING_GOALS'); ?>
										</p>
						</div>
				
					<?
					}
				// Show goals for per match
				if ($params->get('show_scoring_goals_per_match'))
					{	
						$totalGoals         = $stats['totalshome']->goalsfor + $stats['totalsaway']->goalsfor;
						$totalPlayedMatches = $stats['totalshome']->playedmatches + $stats['totalsaway']->playedmatches;
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/goal.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo empty($totalPlayedMatches) ? 0 : round(($totalGoals / $totalPlayedMatches), 2) . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_SCORING_GOALS_PER_MATCH'); ?>
										</p>
						</div>
					<?
					}	
				
				// Show total goals against
				if ($params->get('show_against_goals'))
					{							
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/own_goal.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo $stats['totalshome']->goalsagainst + $stats['totalsaway']->goalsagainst . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_AGAINST_GOALS'); ?>
						</p>
						</div>
					<?
					}				
				// Show total goals against per match
				if ($params->get('show_against_goals_per_match'))
					{	
						$totalGoals         = $stats['totalshome']->goalsagainst + $stats['totalsaway']->goalsagainst;
						$totalPlayedMatches = $stats['totalshome']->playedmatches + $stats['totalsaway']->playedmatches;				
					?> 
						<div style="display: flex;">
						<img src='images/com_sportsmanagement/database/events/own_goal.png' class="m-b-1" style="height: 20px;"/>
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo empty($totalPlayedMatches) ? 0 : round(($totalGoals / $totalPlayedMatches), 2) . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_AGAINST_GOALS_PER_MATCH'); ?>
						</p>
						</div>
				
					<?
					}				
				// Show clean sheets
				if ($params->get('show_clean_sheets'))
					{							
					?> 
						<p style="color: <? echo $text_color;?> ; 
						font-family: sans-serif;
						width:180px; 
						font-size: <? echo $text_size;?>px; 
										">	<?php echo empty($stats['nogoals_against']->totalzero) ? 0 : $stats['nogoals_against']->totalzero . ' '; 
												  echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATISTICS_COUNTER_CLEAN_SHEETS'); ?>
										</p>
				
					<?
					}	
				
				
			?>
			</div>

	<?

	break;
	

 }

