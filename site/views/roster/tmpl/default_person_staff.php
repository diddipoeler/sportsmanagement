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

//echo 'getTeamPlayers staff<br><pre>'.print_r($this->row,true).'</pre><br>';

?>
		<div class="jl_rosterperson jl_rp<?php echo $this->k;?>">
<?php 
$personName = sportsmanagementHelper::formatName(null ,$this->row->firstname, $this->row->nickname, $this->row->lastname, $this->config["name_format_staff"]);
if ($this->config['show_staff_icon']==1) 
{
	$imgTitle = JText::sprintf( $personName );
	$picture = $this->row->picture;
	if ((empty($picture)) || ($picture == 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png' ))
	{
		$picture = $this->row->ppic;
	}
	if ( !file_exists( $picture ) )
	{
		$picture = 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
	}
	/*
  $thumbnail = sportsmanagementHelper::getPictureThumb($picture, $imgTitle,
		$this->config['staff_picture_width'],
		$this->config['staff_picture_height']
	);
	*/
?>
			<div class="jl_rosterperson_staffpicture_column">
				<div class="jl_roster_staffperson_pic">
<?php

      ?>


<a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" title="<?php echo $personName;?>" data-toggle="modal" data-target="#st<?php echo $this->row->person_id;?>">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" alt="<?php echo $personName;?>" width="<?php echo $this->config['staff_picture_width'];?>" />
</a>
<div class="modal fade" id="st<?php echo $this->row->person_id;?>" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $personName, array('title' => $personName,'class' => "img-rounded" ));      
?>
</div>  

    <?php
      	
    
?>
				</div><!-- /.jl_roster_staffperson_pic -->
			</div><!-- /.jl_rosterperson_staffpicture_column -->
<?php
}//if ($this->config['show_staff_icon']) ends
?>
		<div class="jl_roster_staffperson_detail_column">
			<h3>
				<span class="jl_rosterperson_name">
				<?php
		echo ($this->config['link_staff']==1) ? 
			JHtml::link(sportsmanagementHelperRoute::getStaffRoute($this->project->slug,$this->team->slug,$this->row->slug),$personName)
			: $personName;
?>
					<br />&nbsp;
				</span>	
			</h3>
			<div class="jl_roster_persondetails">
					<div>
						<span class="jl_roster_persondetails_label">
<?php 
							echo JText::_('COM_SPORTSMANAGEMENT_ROSTER_STAFF_FUNCTION');
?>
						</span><!-- /.jl_roster_persondetails_label -->
						<span class="jl_roster_persondetails_data">
<?php
						if (!empty($this->row->parentname)) {
						echo JText::sprintf('COM_SPORTSMANAGEMENT_ROSTER_MEMBER_OF',JText::_($this->row->parentname));
						}
						echo $this->row->position;
						?>
						</span><!-- /.jl_roster_persondetails_data -->
					</div>
<?php 
	if ($this->config['show_birthday_staff'] > 0 AND $this->row->birthday !="0000-00-00") 
	{
		switch ($this->config['show_birthday_staff'])
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
							<?php echo JText::_("COM_SPORTSMANAGEMENT_PERSON_AGE");?>
						</span>
						<span class="jl_roster_persondetails_data">
							<?php echo sportsmanagementHelper::getAge($this->row->birthday,$this->row->deathday);?>
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
							<?php echo JText::_("COM_SPORTSMANAGEMENT_PERSON_DEATHDAY");?>[ &dagger; ]
						</span>
						<span class="jl_roster_persondetails_data">
							<?php echo JHtml::date($this->row->deathday,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));?>
						</span>
					</div>
<?php
		}
	}// if ($this->config['show_birthday_staff'] > 0 AND $this->row->birthday !="0000-00-00") ends
	if ($this->config['show_country_flag_staff'])
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
			</div><!-- /.jl_roster_staffperson_detail_column -->
		</div><!-- /.jl_rp<?php echo $this->k;?> -->