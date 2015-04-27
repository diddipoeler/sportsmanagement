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

//echo 'project<pre>',print_r($this->project,true),'</pre>';
//echo 'rows <pre>',print_r($this->rows,true),'</pre>';
//echo 'playereventstats <pre>',print_r($this->playereventstats,true),'</pre>';

/*
das sind alle projektdaten
$this->project

das ist die spielzeit im projekt
$this->project->game_regular_time

das sind die positionen mit den spielern
$this->rows

*/

// Show team-players as defined
if (!empty($this->rows))
{
	$k=0;
	$position='';
	$totalEvents=array();

	// Layout of the columns in the table
	//  1. Position number  (optional : $this->config['show_player_numbers'])
	//  2. Player picture   (optional : $this->config['show_player_icon'])
	//  3. Country flag     (optional : $this->config['show_country_flag'])
	//  4. Player name
	//  5. Injured/suspended/away icons
	//  6. Birthday         (optional : $this->config['show_birthday'])
	//  7. Games played     (optional : $this->overallconfig['use_jl_substitution'] && $this->config['show_games_played'])
	//  8. Starting line-up (optional : $this->overallconfig['use_jl_substitution'] && $this->config['show_substitution_stats'])
	//  9. In               (optional : $this->overallconfig['use_jl_substitution'] && $this->config['show_substitution_stats'])
	// 10. Out              (optional : $this->overallconfig['use_jl_substitution'] && $this->config['show_substitution_stats'])
	// 10. Event type       (optional : $this->config['show_events_stats'] && count($this->playereventstats) > 0,
	//                       multiple columns possible (depends on the number of event types for the position))
	// 11. Stats type       (optional : $this->config['show_stats'] && isset($this->stats[$row->position_id]),
	//                       multiple columns possible (depends on the number of stats types for the position))

	$positionHeaderSpan = 0;
	$totalcolspan = 0;
    if ($this->config['show_player_market_value'])
	{
//      $positionHeaderSpan++;
//		$totalcolspan++;
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
	// Player name and injured/suspended/away columns are always there
	$positionHeaderSpan += 2;
	$totalcolspan       += 2;
	if ($this->config['show_birthday'] || $this->config['show_birthday_staff'])
	{
		$totalcolspan++;
	}
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
	?>
    
<table border="0" cellpadding="0" cellspacing="0" style="text-align: center;" class="table table-striped">
	<?php
    // jetzt kommt die schleife über die positionen
	foreach ($this->rows as $position_id => $players)
	{
		$meanage = 0;
    $countplayer = 0;
    $age = 0;
    // position header
		$row = current($players);
        
//        echo ' player <br><pre>'.print_r( $row, true).'</pre>';
        
        
		$position = $row->position;
		$k=0;
		?>
	<thead>
	<tr class="sectiontableheader rosterheader">
		<th width="60%" colspan="<?php echo $positionHeaderSpan; ?>">
			<?php echo '&nbsp;'.JText::_($row->position); ?>
		</th>
		<?php
		if ($this->config['show_birthday'] > 0)
		{ ?>
		<th class="td_c">
		  <?php
				switch ( $this->config['show_birthday'] )
				{
					case 	1:			// show Birthday and Age
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY_AGE';
						break;

					case 	2:			// show Only Birthday
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY';
						break;

					case 	3:			// show Only Age
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_AGE';
						break;

					case 	4:			// show Only Year of birth
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_YEAR_OF_BIRTH';
						break;
				}
				echo JText::_( $outputStr );
				?>
		</th>
		<?php
		}
		elseif ($this->config['show_birthday_staff'] > 0)
		{
			// Put empty column to keep vertical alignment with the staff table
			?>
		<th class="td_c">&nbsp;</th><?php
		}
		if ($this->overallconfig['use_jl_substitution']==1)
		{
			if ($this->config['show_games_played'])
			{ ?>
		<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ROSTER_PLAYED');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/played.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		?></th>
			<?php 
			}
			if ($this->config['show_substitution_stats'])
			{ ?>
		<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ROSTER_STARTING_LINEUP');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/startroster.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		?></th>
		<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ROSTER_IN');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		?></th>
		<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ROSTER_OUT');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		?></th>
        
        <th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PLAYED_TIME');
				echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/uhr.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 11));
		?></th>
        
			<?php
			}
		}
		if ($this->config['show_events_stats'])
		{
			if ($this->positioneventtypes)
			{
				if (isset($this->positioneventtypes[$row->position_id]) &&
					count($this->positioneventtypes[$row->position_id]))
				{
					$eventCounter=0;

					foreach ($this->positioneventtypes[$row->position_id] AS $eventtype)
					{
						if (empty($eventtype->icon))
						{
							$eventtype_header = JText::_($eventtype->name);
						}
						else
						{
							$iconPath = $eventtype->icon;
                            //echo $iconPath.'<br>';
							if (!strpos(' '.$iconPath,'/'))
							{
								$iconPath='images/com_sportsmanagement/database/events/'.$iconPath;
							}
							$eventtype_header = JHtml::image(	$iconPath,
																JText::_($eventtype->name),
																array(	'title'=> JText::_($eventtype->name),
																		  'align'=> 'top',
																		  'hspace'=> '2'));
						}
						?>
		<th class="td_c">
			<?php echo $eventtype_header;?>
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
				    if ($stat->position_id==$row->position_id)
				    {
					?>
		<th class="td_c"><?php echo $stat->getImage(); ?></th>
					<?php
				    }
				}
			}
		}
        // diddipoeler marktwert
        if ($this->config['show_player_market_value'])
	{
	?>
		<th class="td_c">
			<?php echo JText::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE');?>
		</th>
						<?php
	}
        
        
		?>
	</tr>
	</thead>
	<!-- end position header -->
	<!-- Players row-->
	<?php
    $total_market_value = 0;
	foreach ($players as $row)
	{
		?>
	<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
		<?php
		$pnr = ($row->position_number !='') ? $row->position_number : '&nbsp;';
		if ($this->config['show_player_numbers'])
		{
			if ($this->config['player_numbers_pictures'])
			{
				$value = JHtml::image(JURI::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$pnr,$pnr,array('title'=> $pnr));
			}
			else
			{
				$value = $pnr;
			}
			?>
		<td width="30" class="td_c"><?php echo $value;?></td><?php
		}
		$playerName = sportsmanagementHelper::formatName(null, $row->firstname, 
													$row->nickname, 
													$row->lastname, 
													$this->config["name_format"]);
		if ($this->config['show_player_icon'])
		{
			$picture = $row->picture;
			if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ))
			{
				$picture = $row->ppic;
			}
            /*
			if ( !file_exists( $picture ) )
			{
				$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
			} 
            */
            ?>
		<td width="40" class="td_c" nowrap="nowrap">

<a href="#"  title="<?php echo $playerName;?>" data-toggle="modal" data-target=".player<?php echo $row->playerid;?>">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" alt="<?php echo $playerName;?>" width="<?php echo $this->config['player_picture_width'];?>" />
</a>


<div id="" style="display: none;" class="modal fade player<?php echo $row->playerid;?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
<!--  <div class="modal-dialog"> -->
    <div class="modal-content">
    
    <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
          <h4 class="modal-title" id="myLargeModalLabel"><?php echo $playerName;?></h4>
        </div>
        
        <div class="modal-body">
            <img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" class="img-responsive img-rounded center-block">
        </div>
        <div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE');?> </button>
</div>
    </div>
<!--  </div> -->
  </div>
</div>       


<?PHP
        
        
        	?>
		</td><?php
		}
		elseif ($this->config['show_staff_icon'])
		{ 
			// Put empty column to keep vertical alignment with the staff table
			?>
		<td width="40" class="td_c" nowrap="nowrap">&nbsp;</td><?php
		}
		if ($this->config['show_country_flag'])
		{ ?>
		<td width="16" nowrap="nowrap" style="text-align:center; ">
			<?php echo JSMCountries::getCountryFlag($row->country);?>
		</td><?php
		}
		elseif ($this->config['show_country_flag_staff'])
		{
			// Put empty column to keep vertical alignment with the staff table
			?>
		<td width="16" nowrap="nowrap" style="text-align:center; ">&nbsp;</td><?php
		}
		?>
		<td class="td_l"><?php
		if ($this->config['link_player']==1)
		{
			$link = sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$this->team->slug,$row->slug,NULL,JRequest::getInt('cfg_which_database',0));
			echo JHtml::link($link,'<span class="playername">'.$playerName.'</span>');
		}
		else
		{
			echo '<span class="playername">'.$playerName.'</span>';
		}
		?></td>
		<td width="5%" style="text-align: left;" class="nowrap">&nbsp; <?php
		$model = $this->getModel();
		$this->assign('playertool',$model->getTeamPlayer($this->project->current_round,$row->playerid));
		if (!empty($this->playertool[0]->injury))
		{
			$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_INJURED');
			echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
								$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		}
		if (!empty($this->playertool[0]->suspension))
		{
			$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENDED');
			echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
								$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		}
		if (!empty($this->playertool[0]->away))
		{
			$imageTitle=JText::_('COM_SPORTSMANAGEMENT_PERSON_AWAY');
			echo JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
								$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		}
		?></td>
		<?php
		if ($this->config['show_birthday'] > 0)
		{
			?>
		<td width="10%" nowrap="nowrap" style="text-align: center;"><?php
			if ($row->birthday !="0000-00-00")
			{
				switch ($this->config['show_birthday'])
				{
					case 1:	 // show Birthday and Age
						$birthdateStr = JHtml::date($row->birthday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
						$birthdateStr.="&nbsp;(".sportsmanagementHelper::getAge($row->birthday,$row->deathday).")";
						break;
					case 2:	 // show Only Birthday
						$birthdateStr = JHtml::date($row->birthday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
						break;
					case 3:	 // show Only Age
						$birthdateStr = "(".sportsmanagementHelper::getAge($row->birthday,$row->deathday).")";
						break;
					case 4:	 // show Only Year of birth
						$birthdateStr = JHtml::date($row->birthday, 'Y');
						break;
					default:
						$birthdateStr = "";
						break;
				}
        // das alter berechnen zur weiterberechnung des durchschnittsalters
        // nicht das alter normal berechnen, sonder das alter des spielers in der saison
        //$age += sportsmanagementHelper::getAge( $row->birthday,$row->deathday );
        $age += sportsmanagementHelper::getAge( $row->birthday,$this->lastseasondate );
        $countplayer++;
        
			}
			else
			{
				$birthdateStr="-";
			}
			// deathday
			if ( $row->deathday !="0000-00-00" )
			{
				$birthdateStr .= ' [&dagger; '.JHtml::date($row->deathday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')).']';
			}
					
			echo $birthdateStr;
		?>
		</td><?php
		}
		elseif ($this->config['show_birthday_staff'] > 0)
		{
			?>
		<td width="10%" nowrap="nowrap" style="text-align: left;">&nbsp;</td><?php
		}
		if ($this->overallconfig['use_jl_substitution'])
		{
			// Events of COM_SPORTSMANAGEMENT_substitutions are shown
			$model = $this->getModel();
			
            //$this->assign('InOutStat',$model->getInOutStats($row->pid));
            $this->assign('InOutStat',sportsmanagementModelPlayer::getInOutStats($row->project_id,$row->season_team_id,$row->season_team_person_id,$this->project->game_regular_time,0,JRequest::getInt('cfg_which_database',0)));
            
			if (isset($this->InOutStat) && ($this->InOutStat->played > 0))
			{
				//$played  = $this->InOutStat->played;
				//$started = $this->InOutStat->started;
				//$subIn   = $this->InOutStat->sub_in;
				//$subOut  = $this->InOutStat->sub_out;
                $played = ($this->InOutStat->played > 0 ? $this->InOutStat->played : $this->overallconfig['zero_events_value']);
                $started = ($this->InOutStat->started > 0 ? $this->InOutStat->started : $this->overallconfig['zero_events_value']);
				$subIn   = ($this->InOutStat->sub_in > 0 ? $this->InOutStat->sub_in : $this->overallconfig['zero_events_value']);
				$subOut  = ($this->InOutStat->sub_out > 0 ? $this->InOutStat->sub_out : $this->overallconfig['zero_events_value']);
			}
			else
			{
				//$played  = 0;
				//$started = 0;
				//$subIn   = 0;
				//$subOut  = 0;
                $played  = $this->overallconfig['zero_events_value'];
				$started = $this->overallconfig['zero_events_value'];
				$subIn   = $this->overallconfig['zero_events_value'];
				$subOut  = $this->overallconfig['zero_events_value'];
			}
			if ($this->config['show_games_played'])
			{
				?>
		<td class="td_c" nowrap="nowrap"><?php echo $played;?></td>
				<?php
			}
            
            // spielzeit des spielers
            $timePlayed = 0;
            if ( !isset($this->overallconfig['person_events']) )
            {
                $this->overallconfig['person_events'] = NULL;
            }
            $this->assign('timePlayed',sportsmanagementModelPlayer::getTimePlayed($row->season_team_person_id,$this->project->game_regular_time,NULL,$this->overallconfig['person_events']));
            $timePlayed  = $this->timePlayed;
			if ($this->config['show_substitution_stats'])
			{
				?>
		<td class="td_c"><?php echo $started;?></td>
		<td class="td_c"><?php echo $subIn;?></td>
		<td class="td_c"><?php echo $subOut;?></td>
        <td class="td_c"><?php echo $timePlayed;?></td>
				<?php
			}
		}
		if ($this->config['show_events_stats'] && count($this->playereventstats) > 0)
		{
			// user_defined events in the database are shown
			foreach ($this->playereventstats[$row->pid] AS $eventId=> $stat)
			{
				?>
		<td class="td_c"><?php
				if ($stat !='' && $stat > 0)
				{
					if (!isset($totalEvents[$eventId]))
					{
						$totalEvents[$eventId]=0;
					}
					$totalEvents[$eventId]=(int) $totalEvents[$eventId] + (int) $stat;
				}
				//echo ($stat !='' && $stat > 0) ? number_format($stat, 0, '', '.') : 0;
                echo ($stat !='' && $stat > 0) ? number_format($stat, 0, '', '.') : $this->overallconfig['zero_events_value'];
				?>
		</td>
				<?php
			}
		}
		if ($this->config['show_stats'] && isset($this->stats[$row->position_id]))
		{
			foreach ($this->stats[$row->position_id] as $stat)
			{
			    if ($stat->showInRoster() && ($stat->position_id==$row->position_id))
				{
					if (isset($this->playerstats[$row->position_id][$stat->id][$row->pid]) &&
						isset($this->playerstats[$row->position_id][$stat->id][$row->pid]->value))
					{
						$value = $this->playerstats[$row->position_id][$stat->id][$row->pid]->value;
					}
					else
					{
						if ($stat->_name == 'percentage')
						{
							// Check if one of the denominator statistics is greater than 0.
							// If so, show percentage, otherwise show "-"
							$nonZeroDen = false;
							$dens = $stat->getSids();
							if (isset($dens['den']) && count($dens['den']) > 0)
							{
								foreach($dens['den'] as $den)
								{
									if (isset($this->playerstats[$row->position_id][$den][$row->pid]) &&
										isset($this->playerstats[$row->position_id][$den][$row->pid]->value) && 
										$this->playerstats[$row->position_id][$den][$row->pid]->value)
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
							//$value = 0;
                            $value = $this->overallconfig['zero_events_value'];
						}
					}
					if (is_numeric($value))
					{
						$precision = $stat->getPrecision();
						$value = number_format($value, $precision, ',', '.');
						if ($stat->_name == 'percentage')
						{
							$value .= "%";
						}
					}
					?>
		<td class="td_c" class="hasTip" title="<?php echo JText::_($stat->name); ?>">
			<?php echo $value; ?>
		</td>
					<?php
			    }
			}
		}
        
        // diddipoeler marktwert
        if ($this->config['show_player_market_value'])
	   {
	       $total_market_value += $row->market_value;
	?>
		<td class="td_r" class="hasTip" title="">
			<?php 
            //echo number_format($row->market_value,0, ",", ".");
            echo ($row->market_value > 0 ? number_format($row->market_value, 0, ',', '.') : $this->overallconfig['zero_events_value']); 
            ?>
		</td>
					<?php
	   }
        
        
		?>
	</tr>
	<?php
	$k=(1-$k);
	}
	?>
	<!-- end players rows -->
	<!-- position totals -->
	<?php
	if ($this->config['show_totals'] && ($this->config['show_stats'] || $this->config['show_events_stats']))
	{
  
  if ( $age && $countplayer )
  {
  $meanage = round( $age / $countplayer , 2);
  }
		?>
	<tr class='<?php echo ($k==0? 'sectiontableentry1' : 'sectiontableentry2').' totals'; ?>'>
    <td class="td_r" colspan="3"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS_MEAN_AGE').' '.$meanage; ?></td>
		<td class="td_r" colspan="<?php echo $totalcolspan - 3; ?>"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ROSTER_TOTAL'); ?></b></td>
        <td class="td_r"> : </td>
		<?php
		if ($this->config['show_events_stats'])
		{
			if (isset($this->positioneventtypes[$row->position_id]))
			{
				foreach ($this->positioneventtypes[$row->position_id] AS $eventtype)
				{
					$value='0';
					if (array_key_exists($eventtype->eventtype_id,$totalEvents))
					{
						$value=$totalEvents[$eventtype->eventtype_id];
					}
					?>
		<td class="td_c">
        <?php 
        //echo number_format($value, 0, '', '.');
        echo ($value > 0 ? number_format($value, 0, '', '.') : $this->overallconfig['zero_events_value']);
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
			    if ($stat->showInRoster() && $stat->position_id==$row->position_id)
				{
					$value = $this->playerstats[$row->position_id][$stat->id]['totals']->value;
					if (is_numeric($value))
					{
						$precision = $stat->getPrecision();
						$value = number_format($value, $precision, ',', '.');
						if ($stat->_name == 'percentage')
						{
							$value .= "%";
						}
					}
					?>
		<td class="td_c">
        <?php 
        //echo $value;
        echo ($value > 0 ? $value : $this->overallconfig['zero_events_value']); 
        ?>
        </td>
					<?php
				}
			}
		}
        // diddipoeler marktwert
        if ($this->config['show_player_market_value'])
	   {
	   ?>
		<td class="td_r">
        <?php 
        //echo number_format($total_market_value,0, ",", ".");
        echo ($total_market_value > 0 ? number_format($total_market_value, 0, ',', '.') : $this->overallconfig['zero_events_value']); 

        ?>
        </td>
					<?php    
           }
		?>
	</tr>
	<?php
	}
	?>
	<!-- total end -->
	<?php
	$k=(1-$k);
	}
	?>
</table>
	<?php
}
?>
