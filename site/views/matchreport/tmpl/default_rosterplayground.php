<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_rosterplayground.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$startfade = $this->config['roster_playground_player_fade'];

if ($this->config['roster_playground_player_jquery_fade'])
{
	$div_display = "none";
	?>
    <script>
        jQuery(document).ready(function () {
            setTimeout(function () {
				<?php
				foreach ($this->matchplayers as $player)
				{
				?>
                jQuery("#<?PHP echo $player->person_id; ?>").delay(<?PHP echo $startfade; ?>).slideToggle("slow");
				<?php
				$startfade += $this->config['roster_playground_player_fade'];
				}
				?>
            }, 2000);
        });
    </script>
	<?php
}
else
{
	$div_display = "";
}

$favteams1 = explode(",", $this->project->fav_team);
$favteams  = array();

for ($a = 0; $a < sizeof($favteams1); $a++)
{
	$favteams[$favteams1[$a]] = $favteams1[$a];
}


?>

<div class="flash">
    <table align="center" style="width: 100% ;" border="0">
        <tr>
            <td colspan="5" align="center">
				<?php
				/** Diddipoeler schema der mannschaften */
				$schemahome  = '';
				$schemaguest = '';

				if ($this->config['roster_playground_use_fav_teams'])
				{
					foreach ($favteams as $key => $value)
					{
						if ($value == $this->team1->id)
						{
							$schemahome = $this->formation1;
						}
                        elseif ($value == $this->team2->id)
						{
							$schemaguest = $this->formation2;
						}
					}
				}
				else
				{
					$schemahome  = $this->formation1;
					$schemaguest = $this->formation2;
				}

				$backgroundimage = 'media/com_sportsmanagement/rosterground/' . $this->config['roster_playground_select'];

				list($width, $height, $type, $attr) = getimagesize($backgroundimage);
				$spielfeldhaelfte = $height / 2;

				echo "<div id=\"gesamt\" >";

				if ($schemahome && $schemaguest)
				{
					/** heim und gast */
					echo "<div id=\"heimgast\" style=\"background-position:left;position:relative;height:" . $height . "px;width:" . $width . "px;\">";
					echo "<img class=\"bild_s\" style=\"width:" . $width . "px;\" src=\"" . $backgroundimage . "\" alt=\"\" >";
				}
                elseif (!$schemahome && $schemaguest)
				{
					/** nur gast */
					?>
                    <style>
                        #gast {
                            height: <?PHP echo $height; ?>px;
                            width: <?PHP echo $width; ?>px;
                            top: -<?PHP echo $spielfeldhaelfte; ?>px;
                            overflow: hidden;
                            position: relative;
                        }

                        div img.bild_s {
                            clip: rect(<?PHP echo $spielfeldhaelfte; ?>px,<?PHP echo $width; ?>px,<?PHP echo $height; ?>px, 0px);
                            position: absolute;
                            left: 0px;
                        }

                        #gesamt {
                            height: <?PHP echo $spielfeldhaelfte; ?>px;
                            width: <?PHP echo $width; ?>px;
                            overflow: hidden;
                            position: relative;
                        }
                    </style>

					<?PHP
					echo "<div id=\"gast\" >";
					echo "<img class=\"bild_s\" style=\"width:" . $width . "px;\" src=\"" . $backgroundimage . "\" alt=\"\" >";
				}
                elseif ($schemahome && !$schemaguest)
				{
					/** Nur heim */
					?>
                    <style>
                        #heim {
                            clip: rect(0px <?PHP echo $width; ?>px <?PHP echo $spielfeldhaelfte; ?>px 0px);
                            height: <?PHP echo $spielfeldhaelfte; ?>px;
                            width: <?PHP echo $width; ?>px;

                            overflow: hidden;
                            position: relative;
                        }

                        #gesamt {
                            height: <?PHP echo $spielfeldhaelfte; ?>px;
                            width: <?PHP echo $width; ?>px;
                            overflow: hidden;
                            position: relative;
                        }
                    </style>
					<?PHP
					echo "<div id=\"heim\" >";
					echo "<img class=\"bild_s\" style=\"width:" . $width . "px;\" src=\"" . $backgroundimage . "\" alt=\"\" >";
				}
				else
				{
					/** garnichts angegeben */
					echo "<div id=\"nichts\" style=\"background-position:left;position:relative;height:" . $height . "px;width:" . $width . "px;\">";
					echo "<img class=\"bild_s\" style=\"width:" . $width . "px;\" src=\"" . $backgroundimage . "\" alt=\"\" >";
				}

				/** positionen aus der rostertabelle benutzen */
				?>
                <table class="taktischeaufstellung" summary="Taktische Aufstellung">
                    <tr>
                    </tr>
                    <tr>
                        <td>

							<?PHP
							/** die logos */
							if ($schemahome)
							{
								?>
                                <div style="position:absolute; width:103px; left:0px; top:0px; text-align:center;">
									<?PHP
									echo sportsmanagementHelperHtml::getBootstrapModalImage(
										'rosterplaygroundteamhome',
										$this->team1_club->logo_big,
										$this->team1_club->name,
										'50',
										'',
										$this->modalwidth,
										$this->modalheight,
										$this->overallconfig['use_jquery_modal']
									);
									?>
                                </div>
								<?PHP
							}

							if ($schemaguest)
							{
								?>
                                <div style="position:absolute; width:103px; left:0px; top:950px; text-align:center;">


									<?PHP

									echo sportsmanagementHelperHtml::getBootstrapModalImage(
										'rosterplaygroundteamaway',
										$this->team2_club->logo_big,
										$this->team2_club->name,
										'50',
										'',
										$this->modalwidth,
										$this->modalheight,
										$this->overallconfig['use_jquery_modal']
									);
									?>


                                </div>
								<?PHP
							}

							if ($schemahome)
							{
								/** hometeam */
								$testlauf = 0;

								foreach ($this->matchplayerpositions as $pos)
								{
									$personCount = 0;

									foreach ($this->matchplayers as $player)
									{
										if ($player->pposid == $pos->pposid)
										{
											$personCount++;
										}
									}

									if ($personCount > 0)
									{
										foreach ($this->matchplayers as $player)
										{
											if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam1_id)
											{
$picture2 = sportsmanagementHelper::getDefaultPlaceholder("player");
if ( $player->ppic )
{
$picture  = $player->ppic;  
}
else
{
$picture  = ($player->picture != $picture2) ? $player->picture : $player->ppic;
}


												?>

                                                <div id="<?php echo $player->person_id; ?>"
                                                     style="display:<?php echo $div_display; ?>;position:absolute; width:103px; left:<?PHP echo $this->schemahome[$schemahome][$testlauf]['heim']['links']; ?>px; top:<?PHP echo $this->schemahome[$schemahome][$testlauf]['heim']['oben']; ?>px; text-align:center;">

													<?PHP
													echo sportsmanagementHelperHtml::getBootstrapModalImage(
														'rosterplaygroundperson' . $player->person_id,
														$picture,
														$player->lastname,
														$this->config['roster_playground_player_picture_width'],
														'',
														$this->modalwidth,
														$this->modalheight,
														$this->overallconfig['use_jquery_modal']
													);

													if ($this->config['show_player_profile_link'])
													{
														$routeparameter                       = array();
														$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
														$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
														$routeparameter['p']                  = $this->project->slug;
														$routeparameter['tid']                = $player->team_slug;
														$routeparameter['pid']                = $player->person_slug;
														$player_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
														?>
                                                        <br />
                                                        <a class="link" href="<?php echo $player_link; ?>"><font
                                                                    color=""><?PHP echo $player->lastname . " "; ?></font></a>
														<?php
													}
													?>
                                                </div>

												<?PHP
												$testlauf++;
											}
										}
									}
								}
							}

							if ($schemaguest)
							{
								/** guestteam */
								$testlauf = 0;

								foreach ($this->matchplayerpositions as $pos)
								{
									$personCount = 0;

									foreach ($this->matchplayers as $player)
									{
										if ($player->pposid == $pos->pposid)
										{
											$personCount++;
										}
									}

									if ($personCount > 0)
									{
										foreach ($this->matchplayers as $player)
										{
											if ($player->pposid == $pos->pposid && $player->ptid == $this->match->projectteam2_id)
											{
												$picture2 = sportsmanagementHelper::getDefaultPlaceholder("player");
												$picture  = ($player->picture != $picture2) ? $player->picture : $player->ppic;
												?>
                                                <div id="<?php echo $player->person_id; ?>"
                                                     style="display:<?php echo $div_display; ?>;position:absolute; width:103px; left:<?PHP echo $this->schemaaway[$schemaguest][$testlauf]['gast']['links']; ?>px; top:<?PHP echo $this->schemaaway[$schemaguest][$testlauf]['gast']['oben']; ?>px; text-align:center;">
													<?PHP
													echo sportsmanagementHelperHtml::getBootstrapModalImage(
														'rosterplaygroundperson' . $player->person_id,
														$picture,
														$player->lastname,
														$this->config['roster_playground_player_picture_width'],
														'',
														$this->modalwidth,
														$this->modalheight,
														$this->overallconfig['use_jquery_modal']
													);

													if ($this->config['show_player_profile_link'])
													{
														$routeparameter                       = array();
														$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
														$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
														$routeparameter['p']                  = $this->project->slug;
														$routeparameter['tid']                = $player->team_slug;
														$routeparameter['pid']                = $player->person_slug;
														$player_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
														?>
                                                        <br />
                                                        <a class="link" href="<?php echo $player_link; ?>"><font
                                                                    color=""><?PHP echo $player->lastname . " "; ?></font></a>
														<?php
													}
													?>
                                                </div>

												<?PHP
												$testlauf++;
											}
										}
									}
								}
							}
							?>

                        </td>
                    </tr>
                </table>

				<?PHP


				echo "</div>";
				echo "</div>";


				?>
            </td>
        </tr>
    </table>
</div>
