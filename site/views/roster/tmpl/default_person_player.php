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

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'project<br /><pre>~' . print_r($this->project,true) . '~</pre><br />';    
}

?>
<div class="jl_rosterperson jl_rp<?php echo $this->k;?>">
<?php 
$personName = sportsmanagementHelper::formatName(null ,$this->row->firstname, $this->row->nickname, $this->row->lastname, $this->config["name_format"]);
if ($this->config['show_player_icon']) 
{
	$imgTitle = JText::sprintf( $personName );
	$picture = $this->row->picture;
	if ((empty($picture)) || ($picture == 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png' ))
	{
		$picture = $this->row->ppic;
	}
	
    
	
?>
			<div class="jl_rosterperson_picture_column">
				<div class="jl_rosterperson_pic">
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage('personplayer'.$this->row->person_id,$picture,$personName,$this->config['player_picture_width']);
     
      	
    
?>
				</div><!-- /.jl_rosterperson_pic -->
			</div><!-- /.jl_rosterperson_picture_column -->
<?php
}
//if ($this->config['show_player_icon']) ends
?>
		<div class="jl_rosterperson_detail_column">
			<h3>
<?php
if ($this->config['show_player_numbers']) 
{
	$pnr = ($this->row->position_number !='') ? $this->row->position_number : '&nbsp;';
?>
				<span class="jl_rosterperson_position_number">
				<?php
				$playerNumber = ($this->config['player_numbers_pictures'] AND function_exists('imagecreatefrompng')) ? 
					JHtml::image(JURI::root().'images/com_sportsmanagement/database/teamplayers/shirt.php?text='.$pnr,$pnr,array('title'=> $pnr)) 
					: $pnr;
					echo $playerNumber;
				
?>
				</span><!-- /.jl_rosterperson_position_number -->
<?php
}
?>
				<span class="jl_rosterperson_name">
				<?php
                $routeparameter = array();
       $routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
       $routeparameter['s'] = JRequest::getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $this->team->slug;
       $routeparameter['pid'] = $this->row->person_slug;
		echo ($this->config['link_player']==1) ? 
			JHtml::link(sportsmanagementHelperRoute::getSportsmanagementRoute('player',$routeparameter),$personName)
			: $personName;
?>
					<br />&nbsp;
				</span>	
			</h3>
			<div class="jl_roster_persondetails">
<?php 
			if ((isset($this->row->is_injured) && $this->row->is_injured > 0) OR ($this->row->suspension > 0 && $this->row->suspension_end > $joomleague->current_round))
			{
?>
					<div>
						<span class="jl_roster_persondetails_label">
							<?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_STATUS'); ?>
						</span><!-- /.jl_roster_persondetails_label -->
						<span class="jl_roster_persondetails_data">
<?php 
		$model =& $this->getModel();
		$this->assignRef('playertool',$model->getTeamPlayer($this->project->current_round,$this->row->playerid));
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
?>
						</span><!-- /.jl_roster_persondetails_data -->
					</div>
<?php
			}// if ((isset($this->row->is_injured) && $this->row->is_injured ends
?>
<?php 
	if ($this->config['show_birthday'] > 0 AND $this->row->birthday !="0000-00-00") 
	{
		switch ($this->config['show_birthday'])
		{
			case 1:	 // show Birthday and Age
				$showbirthday = 1;
				$showage = 1;
				$birthdayformat = JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE');
				break;
			case 2:	 // show Only Birthday
				$showbirthday = 1;
				$showage = 0;
				$birthdayformat = JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE');
				break;
			case 3:	 // show Only Age
				$showbirthday = 0;
				$showage = 1;
				break;
			case 4:	 // show Only Year of birth
				$showbirthday = 1;
				$showage = 0;
				$birthdayformat = JText::_('%Y');
				break;
			default:
				$showbirthday = 0;
				$showage = 0;
			break;
		}
		if ($showage == 1)
		{
?>
					<div>
						<span class="jl_roster_persondetails_label">
<?php
			echo JText::_("COM_SPORTSMANAGEMENT_PERSON_AGE");
?>
						</span>
						<span class="jl_roster_persondetails_data">
<?php
			echo sportsmanagementHelper::getAge($this->row->birthday,$this->row->deathday);
?>
						</span>
					</div>
<?php
		}
		if ($showbirthday == 1)
		{
?>
					<div>
						<span class="jl_roster_persondetails_label">
							<?php echo JText::_("COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY");?>
						</span>
						<span class="jl_roster_persondetails_data">
							<?php echo JHtml::date($this->row->birthday,$birthdayformat);?>
						</span>
					</div>
<?php
		}
		// deathday
		if ( $this->row->deathday !="0000-00-00" )
		{
?>
					<div>
						<span class="jl_roster_persondetails_label">
<?php 
			echo JText::_("COM_SPORTSMANAGEMENT_ROSTER_DEATHDAY");
?>
 [ &dagger; ]
						</span>
						<span class="jl_roster_persondetails_data">
<?php echo JHtml::date($this->row->deathday,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));?>
						</span>
					</div>
<?php
		}
	}// if ($this->config['show_birthday'] > 0 AND $this->row->birthday !="0000-00-00") ends
	if ($this->config['show_country_flag'])
	{
?>
					<div>
						<span class="jl_roster_persondetails_label">
							<?php echo JText::_("COM_SPORTSMANAGEMENT_PERSON_NATIONALITY");?>
						</span><!-- /.jl_roster_persondetails_label -->
						<span class="jl_roster_persondetails_data">
<?php
		echo JSMCountries::getCountryFlag($this->row->country);
?>
						</span><!-- /.jl_roster_persondetails_data -->
					</div>
<?php
	}// if ($this->config['show_country_flag']) ends
?>
				</div><!-- /.jl_roster_persondetails -->
			</div><!-- /.jl_rosterperson_detail_column -->
<?php
	if ($this->overallconfig['use_jl_substitution'] OR $this->config['show_events_stats']) 
	{
?>
		<div class="jl_rosterstats">
<?php
		if ($this->overallconfig['use_jl_substitution'])
		{
			// Events of JL_substitutions are shown
			//$model =& $this->getModel();
			//$this->assignRef('InOutStat',sportsmanagementModelPlayer::getInOutStats($this->row->pid));
            $this->assign('InOutStat',sportsmanagementModelPlayer::getInOutStats($this->row->project_id,$this->row->season_team_id,$this->row->season_team_person_id,$this->project->game_regular_time));
            
            //echo ' project_id<br><pre>'.print_r($this->row->project_id,true).'</pre>';
            //echo ' InOutStat<br><pre>'.print_r($this->InOutStat,true).'</pre>';
            
			$cnt=0;
				if ($this->config['show_games_played'] AND isset($this->InOutStat->played) )
				{
					$cnt++;
					echo '<div title="'.$this->InOutStat->played.' '.JText::_('COM_SPORTSMANAGEMENT_ROSTER_PLAYED').'" class="jl_roster_in_out'.'1'.' jl_roster_in_out">
					'.$this->InOutStat->played.'		</div>
				';
				}
				if ($this->config['show_substitution_stats']) 
				{
					if ( isset($this->InOutStat->started) )
					{
						$cnt++;
						echo '<div title="'.$this->InOutStat->started.' '.JText::_('COM_SPORTSMANAGEMENT_ROSTER_STARTING_LINEUP').'" class="jl_roster_in_out'.'2'.' jl_roster_in_out">
						'.$this->InOutStat->started.'		</div>
						';
					}
					if ( isset($this->InOutStat->sub_in) )
					{
						$cnt++;
						echo '<div title="'.$this->InOutStat->sub_in.' '.JText::_('COM_SPORTSMANAGEMENT_ROSTER_IN').'" class="jl_roster_in_out'.'3'.' jl_roster_in_out">
						'.$this->InOutStat->sub_in.'		</div>
						';
					}
					if ( isset($this->InOutStat->sub_out) )
					{
						$cnt++;
						echo '<div title="'.$this->InOutStat->sub_out.' '.JText::_('COM_SPORTSMANAGEMENT_ROSTER_OUT').'" class="jl_roster_in_out'.'4'.' jl_roster_in_out">
						'.$this->InOutStat->sub_out.'		</div>
						';
					}
				}
			}
			// Events statistics:
			if ($this->config['show_events_stats'] AND count($this->playereventstats) > 0 AND isset($this->playereventstats[$this->row->pid]))
			{
				foreach ($this->playereventstats[$this->row->pid] AS $eventId => $stat) {
						if (!empty($stat)) 
						{
							$cnt++;
							if (!isset($totalEvents[$eventId]))
							{
								$totalEvents[$eventId]=0;
							}
							$totalEvents[$eventId] += (int) $stat;
							echo '<div title="'.$this->positioneventtypes[$this->row->position_id][$eventId]->name.': '.$stat.'" class="jl_roster_event'.$eventId.' jl_roster_event">
							'.$stat.'		</div>
							';
						}
					}
				}
				if ($cnt == 0)
				{
						echo '<div class="jl_roster_in_out jl_rosternostats">
						'.JText::_('Keine Statistik für diesen Spieler vorhanden').'		</div>
						';
				}
?>
		</div><!-- /.jl_rosterstats -->
<?php
	}//if ($this->overallconfig['use_jl_substitution'] OR $this->config['show_events_stats']) ends
?>
		</div><!-- /.jl_rp<?php echo $this->k;?> -->