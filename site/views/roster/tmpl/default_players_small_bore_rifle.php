<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage roster
 * @file       default_players.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * https://github.com/eKoopmans/html2pdf.js
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

$picture_path_sport_type_name = 'images/com_sportsmanagement/database/events';
//https://pdfmake.github.io/docs/getting-started/client-side/methods/
?>
<!-- <script src="https://cdn.jsdelivr.net/npm/html-to-pdfmake/docs/browser.js"></script> -->

<script src="https://raw.githack.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>

<script>

<?php


if (PluginHelper::isEnabled('system', 'jsm_bootstrap'))
{
foreach ( $this->projectpositions as $positions => $position ) if( $position->persontype == 1 )
{
?>

jQuery(document).ready(function ($) {
        $('#tableplayer<?php echo $position->id;?>').DataTable({
            scrollX: true,
            paging:         false,
            ordering: false,
            searching: false,
            info: false,
            fixedColumns: {
                leftColumns: 4
            },
            dom: 'Bfrtip',
            buttons: [
            {
            extend: 'print',
            text: 'Print',
            exportOptions: {
                stripHtml: false
            }
        },
        {
            extend: 'csvHtml5',
            text: 'CSV',
            exportOptions: {
                stripHtml: false
            }
        },
        {
            extend: 'excelHtml5',
            text: 'Excel',
            exportOptions: {
                stripHtml: false
            }
       },
        {
                extend: 'pdfHtml5',
                messageTop: '<?php echo $this->project->name;?>',
                
               exportOptions: {
        stripHtml: true
    },

                
                customize: function ( doc ) {
                  
//Create a date string that we use in the footer. Format is dd-mm-yyyy
						var now = new Date();
						var jsDate = now.getDate()+'-'+(now.getMonth()+1)+'-'+now.getFullYear();
doc['footer']=(function(page, pages) {
							return {
								columns: [
									{
										alignment: 'left',
										text: ['Created on: ', { text: jsDate.toString() }]
									},
									{
										alignment: 'right',
										text: ['page ', { text: page.toString() },	' of ',	{ text: pages.toString() }]
									}
								],
								margin: 20
							}
  });
  
                // Logo converted to base64 

doc.content.splice( 1, 0, {
                        margin: [ 0, 0, 0, 12 ],
                        alignment: 'center',
                        image: 'data:image/png;base64,<?php echo base64_encode(file_get_contents(Uri::root() .$this->projectteam->picture));?>',
  width: 150,
                    } );

            
            }
            }
    ]
		
		
        });
    });


//var blob = new Blob([document.getElementById('tableplayer2_wrapper').innerHTML]);
//console.log(blob);

/*
// Function to convert an img URL to data URL 
 	function getBase64FromImageUrl(url) { 
 	console.log(url);
     var img = new Image(); 
 		img.crossOrigin = "anonymous"; 
     img.onload = function () { 
         var canvas = document.createElement("canvas"); 
         canvas.width =this.width; 
         canvas.height =this.height; 
         var ctx = canvas.getContext("2d"); 
         ctx.drawImage(this, 0, 0); 
         var dataURL = canvas.toDataURL("image/png"); 
         return dataURL.replace(/^data:image\/(png|jpg);base64,/, ""); 
     }; 
     img.src = url; 
 	} 
 	
var logo1 = getBase64FromImageUrl('<?php echo Uri::root() .$this->projectteam->picture;?>'); 
console.log(logo1);
*/








<?php
//$this->project->season_id
//$this->team->picture
//$this->projectteam->picture
}
?>




<?php
}
?>

</script>

<?php
/**
 * das sind alle projektdaten
 * $this->project
 * das ist die spielzeit im projekt
 * $this->project->game_regular_time
 * das sind die positionen mit den spielern
 * $this->rows
 */
//echo 'teambild '.$this->projectteam->picture.'<br>';
//  echo base64_encode(file_get_contents(Uri::root() .$this->projectteam->picture));

/**
 *
 * Show team-players as defined
 */
if (!empty($this->rows))
{
	$k           = 0;
	$position    = '';
	$totalEvents = array();

	/**
	 *      Layout of the columns in the table
	 *       1. Position number  (optional : $this->config['show_player_numbers'])
	 *       2. Player picture   (optional : $this->config['show_player_icon'])
	 *       3. Country flag     (optional : $this->config['show_country_flag'])
	 *       4. Player name
	 *       5. Injured/suspended/away icons
	 *       6. Birthday         (optional : $this->config['show_birthday'])
	 *       7. Games played     (optional : $this->overallconfig['use_jl_substitution'] && $this->config['show_games_played'])
	 *       8. Starting line-up (optional : $this->overallconfig['use_jl_substitution'] && $this->config['show_substitution_stats'])
	 *       9. In               (optional : $this->overallconfig['use_jl_substitution'] && $this->config['show_substitution_stats'])
	 *      10. Out              (optional : $this->overallconfig['use_jl_substitution'] && $this->config['show_substitution_stats'])
	 *      10. Event type       (optional : $this->config['show_events_stats'] && count($this->playereventstats) > 0,
	 *                            multiple columns possible (depends on the number of event types for the position))
	 *      11. Stats type       (optional : $this->config['show_stats'] && isset($this->stats[$row->position_id]),
	 */


	$positionHeaderSpan = 0;
	$totalcolspan       = 0;

	if ($this->config['show_player_market_value'])
	{
	}
    
    if ($this->config['show_player_market_text'])
	{
	}


	if ($this->config['show_player_numbers'])
	{
		$positionHeaderSpan++;
		$totalcolspan++;
	}


	if ($this->config['show_player_icon'] || $this->config['show_staff_icon'])
	{
		$positionHeaderSpan++;
		$totalcolspan++;
	}


	if ($this->config['show_country_flag'] || $this->config['show_country_flag_staff'])
	{
		$positionHeaderSpan++;
		$totalcolspan++;
	}

	/**
	 *
	 * Player name and injured/suspended/away columns are always there
	 */
	$positionHeaderSpan += 2;
	$totalcolspan       += 2;

	if ($this->config['show_birthday'] || $this->config['show_birthday_staff'])
	{
		$totalcolspan++;
	}

/*
	if ($this->overallconfig['use_jl_substitution'])
	{
		if ($this->config['show_games_played'])
		{
			$totalcolspan++;
		}


		if ($this->config['show_substitution_stats'])
		{
			$totalcolspan += 3;
		}
	}
	*/
	?>
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultplayers" itemscope itemtype="http://schema.org/SportsTeam">
      <span itemprop="name" content="<?php echo Text::_($this->team->name);?>"></span> 
      <span itemprop="sport" content="<?php echo Text::_($this->project->sport_type_name);?>"></span> 
	    <span itemprop="description" content="<?php echo Text::_($this->project->name);?>"></span>
        <?php
        foreach ( $this->projectpositions as $positions => $position ) if( $position->persontype == 1 )
{
        ?>
        <table class="<?php echo $this->config['table_class']; ?> table-sm nowrap " id="tableplayer<?php echo $position->id;?>" width="100%">
			<?php
			/**
			 *
			 * jetzt kommt die schleife über die positionen
			 */
			foreach ($this->rows as $position_id => $players) if ( $position_id == $position->id )
			{
			$positionpdf = $position->id;
				$meanage     = 0;
				$countplayer = 0;
				$age         = 0;
				/**
				 *
				 * position header
				 */
				$row      = current($players);
				$position = $row->position;
				$k        = 0;
				?>
                <thead>
                <tr class="sectiontableheader rosterheader">
                    <th class="" width="" colspan="">
						<?php echo '&nbsp;' . Text::_($row->position); ?>
                    </th>
					<?php
					
					for ($i = 1, $n = $positionHeaderSpan; $i < $n; $i++)
			{
				?>
				<th class="" width="">
				</th>
				<?php
				}
					
					if ($this->config['show_birthday'])
					{
						?>
                        <th class="" width="">
							<?php
							switch ($this->config['show_birthday'])
							{
								case     1:            /** Show Birthday and Age */
									$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY_AGE';
									break;

								case     2:            /** Show Only Birthday */
									$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY';
									break;

								case     3:            /** Show Only Age */
									$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_AGE';
									break;

								case     4:            /** Show Only Year of birth */
									$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_YEAR_OF_BIRTH';
									break;
							}

							echo Text::_($outputStr);
							?>
                        </th>
						<?php
					}
                    elseif ($this->config['show_birthday_staff'])
					{
						/** Put empty column to keep vertical alignment with the staff table */
						?>
                        <th class="td_c">&nbsp;</th><?php
					}
/*
					if ($this->overallconfig['use_jl_substitution'])
					{
						if ($this->config['show_games_played'])
						{
							?>
                            <th class="" width="">
								<?php
								$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ROSTER_PLAYED');
								$picture    = $picture_path_sport_type_name . '/played.png';

								echo HTMLHelper::image($picture, $imageTitle, array('title' => $imageTitle, 'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px' ));
								?></th>
							<?php
						}

						if ($this->config['show_substitution_stats'])
						{
							?>
                            <th class="" width="">
                            
								<?php
								$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ROSTER_STARTING_LINEUP');
								$picture    = $picture_path_sport_type_name . '/startroster.png';

								echo HTMLHelper::image($picture, $imageTitle, array('title' => $imageTitle, 'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px'));
								?></th>
                            <th class="" width=""><?php
								$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ROSTER_IN');
								$picture    = $picture_path_sport_type_name . '/in.png';

								echo HTMLHelper::image($picture, $imageTitle, array('title' => $imageTitle, 'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px'));
								?></th>
                            <th class="" width=""><?php
								$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ROSTER_OUT');
								$picture    = $picture_path_sport_type_name . '/out.png';

								echo HTMLHelper::image($picture, $imageTitle, array('title' => $imageTitle, 'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px'));
								?></th>

                            <th class="" width="">
								<?php
								$imageTitle = Text::_('COM_SPORTSMANAGEMENT_PLAYED_TIME');
								$picture    = $picture_path_sport_type_name . '/uhr.png';

								echo HTMLHelper::image($picture, $imageTitle, array('title' => $imageTitle, 'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px'));
								?></th>

							<?php
						}
					}
*/
					if ($this->config['show_events_stats'])
					{
						if ($this->positioneventtypes)
						{
							if (isset($this->positioneventtypes[$row->position_id])
								&& count($this->positioneventtypes[$row->position_id])
							)
							{
								$eventCounter = 0;

								foreach ($this->positioneventtypes[$row->position_id] AS $eventtype)
								{
									if (empty($eventtype->icon) || empty($this->config['show_event_icons']))
									{
										$eventtype_header = Text::_($eventtype->name);
									}
									else
									{
										$iconPath = $eventtype->icon;

										if (!strpos(' ' . $iconPath, '/'))
										{
											$iconPath = 'images/com_sportsmanagement/database/events/' . $iconPath;
										}

										$eventtype_header = HTMLHelper::image(
											$iconPath,
											Text::_($eventtype->name),
											array('title'  => Text::_($eventtype->name),
											      'align'  => 'top',
											      'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px',
											      'hspace' => '2')
										);
									}
									?>
                                    <th class="" width="">
										<?php echo $eventtype_header; ?>
                                    </th>
									<?php
								}
							}
						}
					}


					if ($this->config['show_stats'] && isset($this->stats[$row->position_id]))
					{
						foreach ($this->stats[$row->position_id] as $stat)
						{
							if ($stat->showInRoster())
							{
								if ($stat->position_id == $row->position_id)
								{
									?>
                                    <th class="" width=""><?php echo $stat->getImage($this->config['events_picture_height']); ?></th>
									<?php
								}
							}
						}
					}

					/**
					 *
					 * diddipoeler marktwert
					 */
					 /**
					if ($this->config['show_player_market_value'])
					{
						?>
                        <th class="" width="">
							<?php echo Text::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?>
                        </th>
						<?php
					}
*/

					?>
                </tr>
                </thead>
                <!-- end position header -->
                <!-- Players row-->
<div itemprop="member" itemscope itemtype="http://schema.org/OrganizationRole"> 
                      <span itemprop="roleName" content="<?php echo Text::_($row->position);?>"></span>
				<?php
				$total_market_value = 0;

				foreach ($players as $row)
				{
					?>
                    <tr class="" width="" onMouseOver="this.bgColor='#CCCCFF'" onMouseOut="this.bgColor='#ffffff'" itemprop="member" itemscope="" itemtype="http://schema.org/Person">
						<?php
						$pnr = ($row->position_number != '') ? $row->position_number : '&nbsp;';

						if ($this->config['show_player_numbers'])
						{
							if ($this->config['player_numbers_pictures'])
							{
								$value = HTMLHelper::image(Uri::root() . 'images/com_sportsmanagement/database/teamplayers/shirt.php?text=' . $pnr . '#', $pnr, array('title' => $pnr));
							}
							else
							{
								$value = $pnr;
							}
							?>
                            <td class="" width=""><?php echo $value; ?></td><?php
						}

						$playerName = sportsmanagementHelper::formatName(
							null, $row->firstname,
							$row->nickname,
							$row->lastname,
							$this->config["name_format"]
						);
?>
 
  <?php
                  
						if ($this->config['show_player_icon'])
						{
							$picture = $row->ppic;
/*
							if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")))
							{
								$picture = $row->ppic;
							}
                          */
if (preg_match("/placeholder/i", $picture)) {
$picture = $row->picture;
}  
							?>
                            <td class="" width="" nowrap="nowrap">
                              <span itemprop="name" content="<?php echo $playerName;?>"></span> 
                              <span itemprop="birthDate" content="<?php echo $row->birthday;?>"></span>
				   <span itemprop="deathDate" content="<?php echo $row->deathday;?>"></span>
				    <span itemprop="nationality" content="<?php echo JSMCountries::getCountryName($row->country);?>"></span>
                              
								<?PHP
								echo sportsmanagementHelperHtml::getBootstrapModalImage(
									'player' . $row->playerid,
									$picture,
									$playerName,
									$this->config['player_picture_height'],
									'',
									$this->modalwidth,
									$this->modalheight,
									$this->overallconfig['use_jquery_modal'],
                                  'itemprop',
                                  'image'
								);
								?>

                            </td>
							<?php
						}
                        elseif ($this->config['show_staff_icon'])
						{
							/**
							 *
							 * Put empty column to keep vertical alignment with the staff table
							 */
							?>
                            <td class="" width="" nowrap="nowrap">&nbsp;</td><?php
						}


						if ($this->config['show_country_flag'])
						{
							?>
                            <td width=""  class=""nowrap="nowrap" style="text-align:center; ">
							<?php echo JSMCountries::getCountryFlag($row->country); ?>
                            </td><?php
						}
                        elseif ($this->config['show_country_flag_staff'])
						{
							/**
							 *
							 * Put empty column to keep vertical alignment with the staff table
							 */
							?>
                            <td class="" width="" nowrap="nowrap" style="text-align:center; ">&nbsp;</td><?php
						}
						?>
                        <td class="" width=""><?php
							if ($this->config['link_player'] == 1)
							{
								$routeparameter                       = array();
								$routeparameter['cfg_which_database'] = Factory::getApplication()->input->get('cfg_which_database', 0);
								$routeparameter['s']                  = Factory::getApplication()->input->get('s', '');
								$routeparameter['p']                  = $this->project->slug;
								$routeparameter['tid']                = $this->team->slug;
								$routeparameter['pid']                = $row->person_slug;

								$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
								//echo HTMLHelper::link($link, '<span class="playername">' . $playerName . '</span>');
                                echo HTMLHelper::link($link, $playerName);
							}
							else
							{
								//echo '<span class="playername">' . $playerName . '</span>';
                                echo $playerName;
							}
							?></td>
                        <td class="" width="" style="text-align: left;" >&nbsp; <?php
						/*
							$model            = $this->getModel();
							$this->playertool = $model->getTeamPlayer($this->project->current_round, $row->playerid);

							if (!empty($this->playertool[0]->injury))
							{
								$imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURED');
								echo HTMLHelper::image(
									'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/injured.gif',
									$imageTitle, array('title' => $imageTitle, 'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px')
								);
							}


							if (!empty($this->playertool[0]->suspension))
							{
								$imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENDED');
								echo HTMLHelper::image(
									'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/suspension.gif',
									$imageTitle, array('title' => $imageTitle, 'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px')
								);
							}


							if (!empty($this->playertool[0]->away))
							{
								$imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY');
								echo HTMLHelper::image(
									'images/com_sportsmanagement/database/events/' . $this->project->fs_sport_type_name . '/away.gif',
									$imageTitle, array('title' => $imageTitle, 'style' => 'width: auto;height: ' . $this->config['events_picture_height'] . 'px')
								);
							}
							*/
							?></td>
						<?php
						if ($this->config['show_birthday'] > 0)
						{
							?>
                            <td class="" width="" nowrap="nowrap" style="text-align: center;"><?php
							if ($row->birthday != "0000-00-00")
							{
								switch ($this->config['show_birthday'])
								{
									case 1:     // Show Birthday and Age
										$birthdateStr = HTMLHelper::date($row->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
										$birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge($row->birthday, $row->deathday) . ")";
										break;

									case 2:     // Show Only Birthday
										$birthdateStr = HTMLHelper::date($row->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
										break;

									case 3:     // Show Only Age
										$birthdateStr = "(" . sportsmanagementHelper::getAge($row->birthday, $row->deathday) . ")";
										break;

									case 4:     // Show Only Year of birth
										$birthdateStr = HTMLHelper::date($row->birthday, 'Y');
										break;
									default:
										$birthdateStr = "";
										break;
								}

								/**
								 * das alter berechnen zur weiterberechnung des durchschnittsalters
								 * nicht das alter normal berechnen, sonder das alter des spielers in der saison
								 */
								$age += sportsmanagementHelper::getAge($row->birthday, $this->lastseasondate);
								$countplayer++;
							}
							else
							{
								$birthdateStr = "-";
							}

							/**
							 *
							 * deathday
							 */
							if ($row->deathday != "0000-00-00")
							{
								$birthdateStr .= ' [&dagger; ' . HTMLHelper::date($row->deathday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) . ']';
							}

							echo $birthdateStr;
							?>
                            </td><?php
						}
                        elseif ($this->config['show_birthday_staff'])
						{
							?>
                            <td class="" width="" nowrap="nowrap" style="text-align: left;">&nbsp;</td><?php
						}

/*
						if ($this->overallconfig['use_jl_substitution'])
						{
							$model = $this->getModel();

							$this->InOutStat = sportsmanagementModelPlayer::getInOutStats($row->project_id, $row->projectteam_id, $row->season_team_person_id, $this->project->game_regular_time, 0, Factory::getApplication()->input->get('cfg_which_database', 0));

							if (isset($this->InOutStat) && ($this->InOutStat->played > 0))
							{
								$played  = ($this->InOutStat->played > 0 ? $this->InOutStat->played : $this->overallconfig['zero_events_value']);
								$started = ($this->InOutStat->started > 0 ? $this->InOutStat->started : $this->overallconfig['zero_events_value']);
								$subIn   = ($this->InOutStat->sub_in > 0 ? $this->InOutStat->sub_in : $this->overallconfig['zero_events_value']);
								$subOut  = ($this->InOutStat->sub_out > 0 ? $this->InOutStat->sub_out : $this->overallconfig['zero_events_value']);
							}
							else
							{
								$played  = $this->overallconfig['zero_events_value'];
								$started = $this->overallconfig['zero_events_value'];
								$subIn   = $this->overallconfig['zero_events_value'];
								$subOut  = $this->overallconfig['zero_events_value'];
							}


							if ($this->config['show_games_played'])
							{
								?>
                                <td class="" width="" nowrap="nowrap"><?php echo $played; ?></td>
								<?php
							}

							
							
							$timePlayed = 0;

							if (!isset($this->overallconfig['person_events']))
							{
								$this->overallconfig['person_events'] = null;
							}

							$this->timePlayed = sportsmanagementModelPlayer::getTimePlayed($row->season_team_person_id, $this->project->game_regular_time, null, $this->overallconfig['person_events'], $row->project_id);
							$timePlayed       = $this->timePlayed;

							if ($this->config['show_substitution_stats'])
							{
								?>
                                <td class="" width=""><?php echo $started; ?></td>
                                <td class="" width=""><?php echo $subIn; ?></td>
                                <td class="" width=""><?php echo $subOut; ?></td>
                                <td class="" width=""><?php echo $timePlayed; ?></td>
								<?php
							}
						}
*/

						if ($this->config['show_events_stats'] && count($this->playereventstats) > 0)
						{
							/**
							 *
							 * user_defined events in the database are shown
							 */
							foreach ($this->playereventstats[$row->pid] AS $eventId => $stat)
							{
								?>
                                <td class="" width=""><?php
									if ($stat != '' && $stat > 0)
									{
										if (!isset($totalEvents[$row->pposid][$eventId]))
										{
											$totalEvents[$row->pposid][$eventId] = 0;
										}

										$totalEvents[$row->pposid][$eventId] = (int) $totalEvents[$row->pposid][$eventId] + (int) $stat;
									}

									echo ($stat != '' && $stat > 0) ? number_format($stat, 0, '', '.') : $this->overallconfig['zero_events_value'];
									?>
                                </td>
								<?php
							}
						}


						if ($this->config['show_stats'] && isset($this->stats[$row->position_id]))
						{
							foreach ($this->stats[$row->position_id] as $stat)
							{
								if ($stat->showInRoster() && ($stat->position_id == $row->position_id))
								{
									if (isset($this->playerstats[$row->position_id][$stat->id][$row->pid])
										&& isset($this->playerstats[$row->position_id][$stat->id][$row->pid]->value)
									)
									{
										$value = $this->playerstats[$row->position_id][$stat->id][$row->pid]->value;
									}
									else
									{
										if ($stat->_name == 'percentage')
										{
											/**
											 *                             Check if one of the denominator statistics is greater than 0.
											 *                             If so, show percentage, otherwise show "-"
											 */
											$nonZeroDen = false;
											$dens       = $stat->getSids();

											if (isset($dens['den']) && count($dens['den']) > 0)
											{
												foreach ($dens['den'] as $den)
												{
													if (isset($this->playerstats[$row->position_id][$den][$row->pid])
														&& isset($this->playerstats[$row->position_id][$den][$row->pid]->value)
														&& $this->playerstats[$row->position_id][$den][$row->pid]->value
													)
													{
														$nonZeroDen = true;
														break;
													}
												}

												$value = $nonZeroDen ? 0 : "-";
											}
										}
										else
										{
											$value = $this->overallconfig['zero_events_value'];
										}
									}


									if (is_numeric($value))
									{
										$precision = $stat->getPrecision();
										$value     = number_format($value, $precision, ',', '.');

										if ($stat->_name == 'percentage')
										{
											$value .= "%";
										}
									}
									?>
                                    <td class="" width="" title="<?php echo Text::_($stat->name); ?>">
										<?php echo $value; ?>
                                    </td>
									<?php
								}
							}
						}

						/**
						 *
						 * diddipoeler marktwert
						 */
						 /*
						if ($this->config['show_player_market_value'])
						{
							$total_market_value += $row->market_value;
							?>
                            <td class="" width="" title="">
								<?php
								echo($row->market_value > 0 ? number_format($row->market_value, 0, ',', '.') : $this->overallconfig['zero_events_value']);
								?>
                            </td>
							<?php
						}
*/

						?>
                    </tr>
					<?php
					/**
					 *
					 * dartanzeige
					 */
					 /*
					if ($this->project->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART')
					{
						?>
                        <tr>
                            <td colspan="4">
                                <div class="panel-group" id="dartereignisse">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" data-parent="#dartereignisse"
                                                   href="#<?php echo $row->pid; ?>"><?php echo 'Dart Ereignisse'; ?></a>
                                            </h4>
                                        </div>
                                        <div id="<?php echo $row->pid; ?>" class="panel-collapse collapse ">
                                            <div class="panel-body">

                                                <table>
                                                    <tr>
														<?php
														foreach ($this->playereventstats[$row->pid] AS $eventId => $stat)
														{
														foreach ($this->playereventstatsdart[$eventId] AS $key => $value)
														{
														?>
                                                    <tr>
                                                        <td>
															<?php
															if ($value->person_id == $row->pid)
															{
																echo HTMLHelper::image(
																	$this->positioneventtypes[$row->position_id][$eventId]->icon, Text::_($this->positioneventtypes[$row->position_id][$eventId]->name),
																	array('title'  => Text::_($this->positioneventtypes[$row->position_id][$eventId]->name),
																	      'align'  => 'top',
																	      'height' => 20,
																	      'hspace' => '2')
																);
																echo $value->total;
															}
															else
															{
															}
															?>
                                                        </td>
                                                    </tr>
													<?php
													}
													}

													?>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
						<?php
					}
*/
					$k = (1 - $k);
				}
				?>
                  </div>
                <!-- end players rows -->
                <!-- position totals anfang -->
				<?php
				if ($this->config['show_totals'] && ($this->config['show_stats'] || $this->config['show_events_stats']))
				{
					if ($age && $countplayer)
					{
						$meanage = round($age / $countplayer, 2);
					}
					?>
                    <tr class="">
                        <td class="" width=""></td>
                        <?php
						
                        for ($a = 1, $b = 2; $a < $b; $a++)
			{
			?>
			<td>
			</td>
			<?php
			}
			
			?>
                        <td class="" width="" colspan="">
			<?php
			if ($this->config['show_average_age'])
			{
			echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS_MEAN_AGE') . ' ' . $meanage;
			}
			?>
                        </td>
                        <?php
						
                        for ($a = 1, $b = $totalcolspan - 3; $a < $b; $a++)
			{
			?>
			<td>
			</td>
			<?php
			}
			
			?>
                        
                        <td class="td_r" colspan="">
                            <b><?php echo Text::_('COM_SPORTSMANAGEMENT_ROSTER_TOTAL'); ?>:</b></td>
						<?php
						if ($this->config['show_events_stats'])
						{
							if (isset($this->positioneventtypes[$row->position_id]))
							{
								foreach ($this->positioneventtypes[$row->position_id] AS $eventtype)
								{
									$value = '0';

									if (isset($totalEvents[$row->pposid]))
									{
										if (array_key_exists($eventtype->eventtype_id, $totalEvents[$row->pposid]))
										{
											$value = $totalEvents[$row->pposid][$eventtype->eventtype_id];
										}
									}
									?>
                                    <td class="" width="">
										<?php
										echo($value > 0 ? number_format($value, 0, '', '.') : $this->overallconfig['zero_events_value']);
										?>
                                    </td>
									<?php
								}
							}
						}


						if ($this->config['show_stats'] && isset($this->stats[$row->position_id]))
						{
							foreach ($this->stats[$row->position_id] as $stat)
							{
								if ($stat->showInRoster() && $stat->position_id == $row->position_id)
								{
									$value = $this->playerstats[$row->position_id][$stat->id]['totals']->value;

									if (is_numeric($value))
									{
										$precision = $stat->getPrecision();
										$value     = number_format($value, $precision, ',', '.');

										if ($stat->_name == 'percentage')
										{
											$value .= "%";
										}
									}
									?>
                                    <td class="" width="">
										<?php
										echo($value > 0 ? $value : $this->overallconfig['zero_events_value']);
										?>
                                    </td>
									<?php
								}
							}
						}

						/**
						 *
						 * diddipoeler marktwert
						 */
						 /*
						if ($this->config['show_player_market_value'])
						{
							?>
                            <td class="" width="">
								<?php
								echo($total_market_value > 0 ? number_format($total_market_value, 0, ',', '.') : $this->overallconfig['zero_events_value']);
								?>
                            </td>
							<?php
						}
						*/
						?>
                    </tr>
					<?php
				}
				?>
                <!-- position totals ende -->
				<?php
				$k = (1 - $k);
			}
			
			//echo 'position id '.$positionpdf;
			?>
        </table>
        
        <script>
		
var element = document.getElementById('tableplayer<?php echo $positionpdf;?>');
//html2pdf(element);
		
//        var blob = new Blob([document.getElementById('tableplayer2').innerHTML]);
                var blob = '<table>' + document.getElementById('tableplayer<?php echo $positionpdf;?>').innerHTML + '</table>';
        console.log('position id ' );
        var docDefinition = {
    content: [blob],
    exportOptions: {
                stripHtml: true
            }
}
//pdfMake.createPdf(docDefinition).open();
//var val = htmlToPdfmake(blob);
  //  var dd = {content:val};
    //pdfMake.createPdf(dd).download();




/*
pdfDocGenerator = pdfMake.createPdf(docDefinition);
pdfDocGenerator.getDataUrl((dataUrl) => {
	const targetElement = document.querySelector('#iframeContainer');
	const iframe = document.createElement('iframe');
	iframe.src = dataUrl;
	targetElement.appendChild(iframe);
});
*/




//pdfMake.createPdf(docDefinition).open();
/*
const pdfDocGenerator = pdfMake.createPdf(docDefinition);
pdfDocGenerator.getBase64((data) => {
	alert(data);
});
*/

        </script>
        
        
        <?php
        }
        ?>
    </div>
	<?php
}
