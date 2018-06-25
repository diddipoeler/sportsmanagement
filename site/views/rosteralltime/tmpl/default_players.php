<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

// Show team-players as defined
if (!empty($this->rows))
{
	$k = 0;
	$position = '';
	$totalEvents = array();

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
	//if ($this->overallconfig['use_jl_substitution'])
	//{
		if ($this->config['show_games_played'])
		{
			$totalcolspan++;
		}
		if ($this->config['show_substitution_stats'])
		{
			$totalcolspan += 3;
		}
	//}
	?>
<table class="<?php echo $this->config['table_class'];?>">
	<?php
foreach ($this->playerposition as $position_id )
	{
	   $meanage = 0;
    $countplayer = 0;
    $age = 0;
	?>
	<thead>
	<tr class="sectiontableheader rosterheader">
		<th width="60%" colspan="<?php echo $positionHeaderSpan; ?>">
			<?php echo '&nbsp;'.JText::_($position_id->name); ?>
		</th>
		<?php
		if ($this->config['show_birthday'] > 0)
		{ 
		  ?>
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
        
        if ($this->config['show_games_played'])
			{ ?>
		<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ROSTER_PLAYED');
				echo JHTML::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/played.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		?></th>
			<?php 
			}
			if ($this->config['show_substitution_stats'])
			{ ?>
		<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ROSTER_STARTING_LINEUP');
				echo JHTML::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/startroster.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		?></th>
		<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ROSTER_IN');
				echo JHTML::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/in.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		?></th>
		<th class="td_c"><?php
				$imageTitle=JText::_('COM_SPORTSMANAGEMENT_ROSTER_OUT');
				echo JHTML::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/out.png',
				$imageTitle,array('title'=> $imageTitle,'height'=> 20));
		?></th>
			<?php
			}
            
       if ($this->config['show_events_stats'])
		{
			if ($this->positioneventtypes)
			{
				if (isset($this->positioneventtypes[$position_id->id]) &&
					count($this->positioneventtypes[$position_id->id]))
				{
					$eventCounter=0;

					foreach ($this->positioneventtypes[$position_id->id] AS $eventtype)
					{
						if (empty($eventtype->icon))
						{
							$eventtype_header = JText::_($eventtype->name);
						}
						else
						{
							$iconPath=$eventtype->icon;
							if (!strpos(' '.$iconPath,'/'))
							{
								$iconPath='images/com_sportsmanagement/database/events/'.$iconPath;
							}
							$eventtype_header = JHTML::image(	$iconPath,
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
                
	?>
	</tr>
	</thead>
	<!-- end position header -->
	<!-- Players row-->
	<?php
foreach ($this->rows as $players)
	{    
	
    if ( $players->position_id == $position_id->id )
    {
    ?>
	<tr >
	<?php
    $pnr = '&nbsp;';
    $value = $pnr;
    ?>
		<td width="30" class="td_c"><?php echo $value;?></td>
        <?php
    
    //}    
    $playerName = sportsmanagementHelper::formatName(null, $players->firstname, 
													$players->nickname, 
													$players->lastname, 
													$this->config["name_format"]);
		if ($this->config['show_player_icon'])
		{
			$picture = $players->picture;
			if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ))
			{
				$picture = $players->ppic;
			}
			if ( !file_exists( $picture ) )
			{
				$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
			} ?>
		<td width="40" class="td_c" nowrap="nowrap">
        
            
<?PHP
echo sportsmanagementHelperHtml::getBootstrapModalImage('allplayer'.$players->pid,$picture,$playerName,$this->config['player_picture_width']);
?>            
		</td><?php
		}
        
        if ( $this->config['show_country_flag'] )
		{ ?>
		<td width="16" nowrap="nowrap" style="text-align:center; ">
			<?php echo JSMCountries::getCountryFlag($players->country);?>
		</td><?php
		}
        
        ?>
		<td class="td_l"><?php
		if ( $this->config['link_player'] )
		{
		  $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $players->team_slug;
       $routeparameter['pid'] = $players->person_slug;
		
			$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter);
			echo JHTML::link($link,'<span class="playername">'.$playerName.'</span>');
		}
		else
		{
			echo '<span class="playername">'.$playerName.'</span>';
		}
		?>
        </td>
        <?php    
        
        if ($this->config['show_birthday'] > 0)
		{
			?>
		<td width="10%" nowrap="nowrap" style="text-align: center;"><?php
			if ($players->birthday !="0000-00-00")
			{
				switch ($this->config['show_birthday'])
				{
					case 1:	 // show Birthday and Age
						$birthdateStr = JHTML::date($players->birthday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
						$birthdateStr.="&nbsp;(".sportsmanagementHelper::getAge($players->birthday,$players->deathday).")";
						break;
					case 2:	 // show Only Birthday
						$birthdateStr = JHTML::date($players->birthday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
						break;
					case 3:	 // show Only Age
						$birthdateStr = "(".sportsmanagementHelper::getAge($players->birthday,$players->deathday).")";
						break;
					case 4:	 // show Only Year of birth
						$birthdateStr = JHTML::date($players->birthday, 'Y');
						break;
					default:
						$birthdateStr = "";
						break;
				}
        // das alter berechnen zur weiterberechnung des durchschnittsalters
        $age += sportsmanagementHelper::getAge( $players->birthday,$players->deathday );
        $countplayer++;
        
			}
			else
			{
				$birthdateStr="-";
			}
			// deathday
			if ( $players->deathday !="0000-00-00" )
			{
				$birthdateStr .= ' [&dagger; '.JHTML::date($players->deathday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')).']';
			}
					
			echo $birthdateStr;
		?>
		</td>
        <?php
		}
        
        if ($this->config['show_games_played'])
			{
			$played  = $players->start + $players->came_in; 
				?>
		<td class="td_c" nowrap="nowrap"><?php echo $played;?></td>
				<?php
			}
			
            if ($this->config['show_substitution_stats'])
			{
				?>
		<td class="td_c"><?php echo $players->start;?></td>
		<td class="td_c"><?php echo $players->came_in;?></td>
		<td class="td_c"><?php echo $players->out;?></td>
				<?php
			}
            
        if ( $this->config['show_events_stats'] )
		{
			if ( isset($this->positioneventtypes[$position_id->id]) )
            {
            // user_defined events in the database are shown
			foreach ($this->positioneventtypes[$position_id->id] AS $eventId )
			{
			 $event_type_id = 'event_type_id_'.$eventId->eventtype_id;
             //echo 'event_type_id -> '.$event_type_id.'<br>';
				?>
		<td class="td_c"><?php
        $stat = $players->$event_type_id;
				if ($stat !='' && $stat > 0)
				{
					if (!isset($totalEvents[$eventId->eventtype_id]))
					{
						$totalEvents[$eventId->eventtype_id]=0;
					}
					$totalEvents[$eventId->eventtype_id]=(int) $totalEvents[$eventId->eventtype_id] + (int) $stat;
				}
				echo ($stat !='' && $stat > 0) ? number_format($stat, 0, '', '.') : 0;
				?>
		</td>
				<?php
			}
            }
		}
        
        
	?>
	</tr>
	<?php
}

}
?>
	<!-- end players rows -->
	<!-- position totals -->
	<?php
    if ( $age && $countplayer )
  {
  $meanage = round( $age / $countplayer , 2);
  }
    
    ?>
	<tr class="">
    <td class="td_r" colspan="3"><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS_MEAN_AGE').' '.$meanage; ?></td>
		<td class="td_r" colspan="<?php echo $totalcolspan - 3; ?>"><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ROSTER_TOTAL'); ?></b></td>
		<?php
        if ($this->config['show_events_stats'])
		{
			if (isset($this->positioneventtypes[$position_id->id]))
			{
				foreach ($this->positioneventtypes[$position_id->id] AS $eventtype)
				{
					$value='0';
					if (array_key_exists($eventtype->eventtype_id,$totalEvents))
					{
						$value=$totalEvents[$eventtype->eventtype_id];
					}
					?>
		<td class="td_c"><?php echo number_format($value, 0, '', '.');?></td>
					<?php
				}
			}
		}
        ?>
	</tr>
	<?php
}
	
	?>
</table>
	<?php
}
?>

<div class="pagination">
<p class="counter">
<?php echo $this->pagination->getPagesCounter(); ?>
</p>
<p class="counter">
<?php echo $this->pagination->getResultsCounter(); ?>
</p>
<?php echo $this->pagination->getPagesLinks(); ?>
</div>