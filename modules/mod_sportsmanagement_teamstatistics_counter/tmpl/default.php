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
