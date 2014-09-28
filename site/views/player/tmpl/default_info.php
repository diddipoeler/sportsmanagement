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

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- person data START -->
<h2><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA' );	?></h2>

<table class="plgeneralinfo">
	<tr>
	<?php
	if ( $this->config['show_player_photo'] == 1 )
	{
		?>
		<td class="picture"><?php
		$picturetext=JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PICTURE' );
		$imgTitle = JText::sprintf( $picturetext , sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]) );
		$picture = isset($this->teamPlayer) ? $this->teamPlayer->picture : null;
		if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ))
		{
			$picture = $this->person->picture;
		}
		
        /*
        if ( !file_exists( $picture ) )
		{
			$picture = sportsmanagementHelper::getDefaultPlaceholder("player") ;
		}
        */
        
		/*
    echo sportsmanagementHelper::getPictureThumb($picture, $imgTitle,
												$this->config['picture_width'],
												$this->config['picture_height']);
    */
    //echo JHtml::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['picture_width'] ));
    ?>
        <a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" title="<?php echo $imgTitle;?>" class="modal">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" alt="<?php echo $imgTitle;?>" width="<?php echo $this->config['picture_width'];?>" />
</a>
<?PHP                    
		?></td>
		<?php
	}
	?>
		<td class="info">
		<table class="plinfo">
			<?php
			if(!empty($this->person->country) && ($this->config["show_nationality"] == 1))
			{
			?>
			<tr>
				<td class="label"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NATIONALITY' ); ?>
				</td>
				<td class="data">
				<?php
					echo JSMCountries::getCountryFlag( $this->person->country ) . " " .
					JText::_( JSMCountries::getCountryName($this->person->country));
					?>
				</td>
			</tr>
			<?php
			}
			?>
			<tr>
				<td class="label">
					<?php	echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NAME' ); ?>
				</td>
				<td class="data">
				<?php 
				
				$outputName = sportsmanagementHelper::formatName(null ,$this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]);
				if ( $this->person->user_id )
				{
					switch ( $this->config['show_user_profile'] )
					{
						case 1:	 // Link to Joomla Contact Page
							$link = sportsmanagementHelperRoute::getContactRoute( $this->person->user_id );
							$outputName = JHtml::link( $link, $outputName );
							break;

						case 2:	 // Link to CBE User Page with support for JoomLeague Tab
							$link = sportsmanagementHelperRoute::getUserProfileRouteCBE(	$this->person->user_id,
							$this->project->id,
							$this->person->id );
							$outputName = JHtml::link( $link, $outputName );
							break;

						default:	break;
					}
				}
				echo $outputName; ?>
				</td>
			</tr>
			<?php if ( ! empty( $this->person->nickname ) )
			{
				?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NICKNAME' );
				?></td>
				<td class="data"><?php
				echo $this->person->nickname;
				?></td>
			</tr>
			<?php
			}

     
			if (( $this->config[ 'show_birthday' ] > 0 ) &&
			( $this->config[ 'show_birthday' ] < 5 ) &&
			( $this->person->birthday != '0000-00-00' ))
			{
				#$this->config['show_birthday'] = 4;
				?>
			<tr>
				<td class="label"><?php
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
				?></td>
				<td class="data"><?php
				#$this->assignRef( 'playerage', $model->getAge( $this->player->birthday, $this->project->start_date ) );
				switch ( $this->config['show_birthday'] )
				{
					case 1:	 // show Birthday and Age
						$birthdateStr =	$this->person->birthday != "0000-00-00" ?
						JHtml::date( $this->person->birthday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
						$birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge( $this->person->birthday,$this->person->deathday ) . ")";
						break;

					case 2:	 // show Only Birthday
						$birthdateStr =	$this->person->birthday != "0000-00-00" ?
						JHtml::date( $this->person->birthday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
						break;

					case 3:	 // show Only Age
						$birthdateStr = sportsmanagementHelper::getAge( $this->person->birthday,$this->person->deathday );
						break;

					case 4:	 // show Only Year of birth
						$birthdateStr =	$this->person->birthday != "0000-00-00" ?
						JHtml::date( $this->person->birthday, JText::_( '%Y' ) ) : "-";
						break;

					default:	$birthdateStr = "";
					break;
				}
				echo $birthdateStr;
				?></td>
			</tr>
			<?php
			}
			if 	( $this->person->deathday != '0000-00-00' )
			{
			?>
			<tr>
				<td class="label">
					<?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_DEATHDAY' ); ?>
				</td>
				<td class="data">
					<?php 
					$deathdateStr =	JHtml::date( $this->person->deathday, JText::_('COM_SPORTSMANAGEMENT_GLOBAL_DEATHDATE')) ;
					echo '&dagger; '.$deathdateStr;
					?>
				</td>
			</tr>		
			<?php
			}

			if (( $this->person->address != "" ) && ( $this->config[ 'show_person_address' ] ==1  ) && ($this->isContactDataVisible) )
			{
				?>
			<tr>
				<td class="label"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_ADDRESS' ); ?></td>
				<td class="data"><?php
					echo JSMCountries::convertAddressString(	'',
															$this->person->address,
															$this->person->state,
															$this->person->zipcode,
															$this->person->location,
															$this->person->address_country,
															'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM' );
				?></td>
			</tr>
			<?php
			}

			if (( $this->person->phone != "" ) && ( $this->config[ 'show_person_phone' ] ==1  ) && ($this->isContactDataVisible) )
			{
				?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PHONE' );
				?></td>
				<td class="data"><?php
				echo $this->person->phone;
				?></td>
			</tr>
			<?php
			}

			if (( $this->person->mobile != "" ) && ( $this->config[ 'show_person_mobile' ] ==1  ) && ($this->isContactDataVisible) )
			{
				?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_MOBILE' );
				?></td>
				<td class="data"><?php
				echo $this->person->mobile;
				?></td>
			</tr>
			<?php
			}

			if (( $this->person->email != "" ) && ($this->config['show_person_email'] == 1) && ($this->isContactDataVisible) )
			{
					?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EMAIL' );
				?></td>
				<td class="data"><?php
				$user = JFactory::getUser();
				if ( ( $user->id ) || ( ! $this->overallconfig['nospam_email'] ) )
				{
					?> <a href="mailto: <?php echo $this->person->email; ?>"> <?php
					echo $this->person->email;
					?> </a> <?php
				}
				else
				{
					echo JHtml::_('email.cloak', $this->person->email );
				}
				?></td>
			</tr>
			<?php
			}

			if (( $this->person->website != "" ) && ($this->config['show_person_website'] == 1))
			{
				?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEBSITE' );
				?></td>
				<td class="data"><?php
				echo JHtml::_(	'link',
				$this->person->website,
				$this->person->website,
				array( 'target' => '_blank' ) );
				?></td>
			</tr>
			<?php
			}

			if (( $this->person->height > 0 ) && ($this->config['show_person_height'] == 1))
			{
				?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_HEIGHT' );
				?></td>
				<td class="data"><?php
				echo str_replace( "%HEIGHT%", $this->person->height, JText::_( 'COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM' ) );
				?></td>
			</tr>
			<?php
			}
			if (( $this->person->weight > 0 ) && ($this->config['show_person_weight'] == 1))
			{
				?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEIGHT' );
				?></td>
				<td class="data"><?php
				echo str_replace( "%WEIGHT%", $this->person->weight, JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM' ) );
				?></td>
			</tr>
			<?php
			}
			if ( ( $this->config['show_player_number'] ) &&
			isset($this->teamPlayer->jerseynumber) &&
			( $this->teamPlayer->jerseynumber > 0 ) )
			{
				?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NUMBER' );
				?></td>
				<td class="data"><?php
				if ( $this->config['player_number_picture'] )
				{
					$posnumber = $this->teamPlayer->jerseynumber;
					echo JHtml::image( JURI::root().'images/com_sportsmanagement/database/events/shirt.php?text=' . $posnumber,
					$posnumber,
					array( 'title' => $posnumber ) );
				}
				else
				{
					echo $this->teamPlayer->jerseynumber;
				}
				?></td>
			</tr>
			<?php
			}
			if ( isset($this->teamPlayer->position_id) && $this->teamPlayer->position_id != "" )
			{
				?>
			<tr>
				<td class="label"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_ROSTERPOSITION' );
				?></td>
				<td class="data"><?php
				echo JText::_( $this->teamPlayer->position_name );
				?></td>
			</tr>
			<?php
			}
			if (( ! empty( $this->person->knvbnr ) ) && ($this->config['show_person_regnr'] == 1))
			{
				?>
			<tr>
				<td class="label"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR' ); ?>
				</td>
				<td class="data"><?php echo $this->person->knvbnr; ?></td>
			</tr>
			<?php
			}
			?>
		</table>
		</td>
	</tr>
</table>
