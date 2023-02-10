<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_roster_card.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
	HTMLHelper::_('behavior.modal');
}

?>

<link rel="stylesheet" href="<?php echo Uri::base(true); ?>/components/com_sportsmanagement/assets/css/matchreport_2.css">

<!-- START: game roster card -->
<!-- Show Match players card-->
<?php
if (!empty($this->matchplayerpositions))
{
	foreach ($this->matchplayerpositions as $pos)
	{
		$personCount = 0;

		foreach ($this->matchplayers as $player)
		{
			if ($player->position_id == $pos->position_id)
			{
				$personCount++;
			}
		}
		?>
		<div class="row-fluid" id="">
			<div class="col-md-12" id="position">
				<div class="col-md-5 text-right" id="clubhomename">
					<?php echo $this->team1_club->name; ?>
				</div>
				<div class="col-md-2 text-center" id="posname">
					<?php echo Text::_($pos->name); ?>
				</div>
				<div class="col-md-5 text-left" id="clubawayteam">
					<?php echo $this->team2_club->name; ?>
				</div>
			</div>

			<div class="col-md-12" id="playerrow">
				<!-- list of home-team -->
				<div class="col-md-5" id="homeplayer">
					<?php
					foreach ($this->matchplayers as $player)
					{
						if ($player->trikot_number != 0)
						{
							$player->jerseynumber = $player->trikot_number;
						}

						if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam1_id)
						{
							/**
							 * ist der spieler ein spielführer ?
							 */
							if ($player->captain != 0)
							{
								echo ' ' . '&copy;';
							}

							$routeparameter					   = array();
							$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
							$routeparameter['s']				  = Factory::getApplication()->input->getInt('s', 0);
							$routeparameter['p']				  = $this->project->slug;
							$routeparameter['tid']				= $player->team_slug;
							$routeparameter['pid']				= $player->person_slug;
							$player_link						  = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

							if ($this->config['show_player_profile_name_trikotnumber'])
							{
								$prefix = $player->jerseynumber ? $player->jerseynumber . "." : null;
							}
							else
							{
								$prefix = null;
							}

							$match_player = sportsmanagementHelper::formatName($prefix, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
							$isFavTeam	= in_array($player->team_id, explode(",", $this->project->fav_team));
							?>
							<div class="text-right" id="homesingleplayer">
								<?php
								if (($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)))
								{
									if ($this->config['show_player_picture'] == 2)
									{
										echo '';
									}
									else
									{
										if ($this->config['show_player_profile_link_alignment'] == 0)
										{
											echo HTMLHelper::link($player_link, $match_player . HTMLHelper::image(Uri::root() . 'images/com_sportsmanagement/database/teamplayers/shirt.php?text=' . $player->jerseynumber, $player->jerseynumber, array('title' => $player->jerseynumber)));
										}
									}
								}
								else
								{
									if ($this->config['show_player_picture'] == 2)
									{
										echo '';
									}
									else
									{
										echo $match_player;
									}
								}

								if (($this->config['show_player_picture'] == 1) || ($this->config['show_player_picture'] == 2))
								{
									$imgTitle = ($this->config['show_player_profile_link'] == 1) ? Text::sprintf('COM_SPORTSMANAGEMENT_MATCHREPORT_PIC', $match_player) : $match_player;
									$picture  = $player->picture;

									if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")) || !curl_init($picture))
									{
										$picture = $player->ppic;
									}


									if (!curl_init($picture))
									{
										$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
									}


									if ( ($this->config['show_player_picture'] == 2) && ( $this->config['show_player_profile_link'] ) )
									{
										echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer' . $player->person_id, $picture, $imgTitle, $this->config['player_picture_height']);
										?>

										<?PHP
									}
									else
									{
										echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer' . $player->person_id, $picture, $imgTitle, $this->config['player_picture_height']);

										if ( $this->config['show_player_profile_link_alignment'] )
										{
											echo '<br>';
											echo HTMLHelper::link($player_link, $match_player . HTMLHelper::image(Uri::root() . 'images/com_sportsmanagement/database/teamplayers/shirt.php?text=' . $player->jerseynumber, $player->jerseynumber, array('title' => $player->jerseynumber)));
										}

										echo '&nbsp;';
									}
								}

								?>
							</div>


							<?php
						}
					}
					?>


				</div>
				<!-- list of line -->
				<div class="col-md-2"></div>
				<!-- list of guest-team -->
				<div class="col-md-5" id="awayplayer">
					<?php
					foreach ($this->matchplayers as $player)
					{
						if ($player->trikot_number != 0)
						{
							$player->jerseynumber = $player->trikot_number;
						}


						if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam2_id)
						{
							/**
							 * ist der spieler ein spielführer ?
							 */
							if ($player->captain != 0)
							{
								echo ' ' . '&copy;';
							}

							$routeparameter					   = array();
							$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
							$routeparameter['s']				  = Factory::getApplication()->input->getInt('s', 0);
							$routeparameter['p']				  = $this->project->slug;
							$routeparameter['tid']				= $player->team_slug;
							$routeparameter['pid']				= $player->person_slug;
							$player_link						  = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

							if ($this->config['show_player_profile_name_trikotnumber'])
							{
								$prefix = $player->jerseynumber ? $player->jerseynumber . "." : null;
							}
							else
							{
								$prefix = null;
							}

							$match_player = sportsmanagementHelper::formatName($prefix, $player->firstname, $player->nickname, $player->lastname, $this->config["name_format"]);
							$isFavTeam	= in_array($player->team_id, explode(",", $this->project->fav_team));


							?>
							<div class="text-left" id="awaysingleplayer">
								<?php
								if (($this->config['show_player_picture'] == 1) || ($this->config['show_player_picture'] == 2))
								{
									$imgTitle = ($this->config['show_player_profile_link'] == 1) ? Text::sprintf('COM_SPORTSMANAGEMENT_MATCHREPORT_PIC', $match_player) : $match_player;
									$picture  = $player->picture;

									if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")) || !curl_init($picture))
									{
										$picture = $player->ppic;
									}


									if (!curl_init($picture))
									{
										$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
									}


									if (($this->config['show_player_picture'] == 2) && ($this->config['show_player_profile_link'] == 1))
									{
										echo HTMLHelper::link($player_link, HTMLHelper::image($picture, $imgTitle, array('title' => $imgTitle, 'width' => $this->config['player_picture_height'])));

										if ($this->config['show_player_profile_link_alignment'] == 1)
										{
											echo '<br>';
											echo HTMLHelper::link($player_link, HTMLHelper::image(Uri::root() . 'images/com_sportsmanagement/database/teamplayers/shirt.php?text=' . $player->jerseynumber, $player->jerseynumber, array('title' => $player->jerseynumber)) . $match_player);
										}
									}
									else
									{
										echo sportsmanagementHelperHtml::getBootstrapModalImage('matchplayer' . $player->person_id, $picture, $imgTitle, $this->config['player_picture_height']);
										?>
										<?PHP

										?>

										<?PHP
										if ($this->config['show_player_profile_link_alignment'] == 1)
										{
											echo '<br>';
											echo HTMLHelper::link($player_link, HTMLHelper::image(Uri::root() . 'images/com_sportsmanagement/database/teamplayers/shirt.php?text=' . $player->jerseynumber, $player->jerseynumber, array('title' => $player->jerseynumber)) . $match_player);
										}

										echo '&nbsp;';
									}
								}

								if (($this->config['show_player_profile_link'] == 1) || (($this->config['show_player_profile_link'] == 2) && ($isFavTeam)))
								{
									if ($this->config['show_player_picture'] == 2)
									{
										echo '';
									}
									else
									{
										if ($this->config['show_player_profile_link_alignment'] == 0)
										{
											echo HTMLHelper::link($player_link, HTMLHelper::image(Uri::root() . 'images/com_sportsmanagement/database/teamplayers/shirt.php?text=' . $player->jerseynumber, $player->jerseynumber, array('title' => $player->jerseynumber)) . $match_player);
										}
									}
								}
								else
								{
									if ($this->config['show_player_picture'] == 2)
									{
										echo '';
									}
									else
									{
										echo $match_player;
									}
								}

								/**
								 * ist der spieler ein spielführer ?
								 */
								if ($player->captain != 0)
								{
									echo ' ' . '&copy;';
								}

								?>
							</div>


							<?php
						}
					}
					?>


				</div>
			</div>


		</div>
		<?php
	}
}
?>
<!-- END of Match players card-->
<br/>

<!-- END: game roster card-->
