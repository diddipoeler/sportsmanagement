<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- person data START -->
<?php if ($this->referee) { ?>
<h2><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA' );	?></h2>
<table class="plgeneralinfo">
	<tr>
		<?php
		if ( $this->config['show_photo'] == 1 )
		{
			?>
			<td class="picture">
				<?php
				$picturetext=JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PICTURE' );
				$imgTitle = JText::sprintf( $picturetext, sportsmanagementHelper::formatName(null, $this->referee->firstname, $this->referee->nickname, $this->referee->lastname, $this->config["name_format"]) );
				$picture = $this->referee->picture;
				if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")  ))
				{
					$picture = $this->person->picture;
				}
				if ( !file_exists( $picture ) )
				{
					$picture = sportsmanagementHelper::getDefaultPlaceholder("player") ;
				}
				
/**
 *                 echo sportsmanagementHelper::getPictureThumb($picture, 
 * 														$imgTitle, 
 * 														$this->config['picture_width'],
 * 														$this->config['picture_height']);
 */
                                                        
				?>
<a href="<?php echo JURI::root().$picture;?>" title="<?php echo $imgTitle;?>" class="modal">
<img src="<?php echo JURI::root().$picture;?>" alt="<?php echo $imgTitle;?>" width="<?php echo $this->config['picture_width'];?>" />
</a>			
            </td>
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
						echo Countries::getCountryFlag( $this->person->country ) . " " .
						JText::_( Countries::getCountryName($this->person->country));
						?>
					</td>
				</tr>
				<?php
				}
				?>
				<tr>
					<td class="label">

							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NAME' );
							?>

					</td>
					<td class="data">
						<?php
						$outputName = JText::sprintf( '%1$s %2$s', $this->referee->firstname, $this->referee->lastname);
						if ( $this->referee->user_id )
						{
							switch ( $this->config['show_user_profile'] )
							{
								case 1:	 // Link to Joomla Contact Page
											$link = sportsmanagementHelperRoute::getContactRoute( $this->referee->user_id );
											$outputName = JHTML::link( $link, $outputName );
											break;

								case 2:	 // Link to CBE User Page with support for JoomLeague Tab
											$link = sportsmanagementHelperRoute::getUserProfileRouteCBE(	$this->referee->user_id,
																									$this->project->id,
																									$this->referee->id );
											$outputName = JHTML::link( $link, $outputName );
											break;

								default:	break;
							}
						}
						echo $outputName;
						?>
					</td>
				</tr>
				<?php
						if ( ! empty( $this->referee->nickname ) )
						{
							?>
							<tr>
								<td class="label">

										<?php
										echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NICKNAME' );
										?>

								</td>
								<td class="data">
									<?php
									echo $this->referee->nickname;
									?>
								</td>
							</tr>
							<?php
						}
				if (	( $this->config[ 'show_birthday' ] > 0 ) &&
						( $this->config[ 'show_birthday' ] < 5 ) &&
						( $this->referee->birthday != '0000-00-00' ) )
				{
					#$this->config['show_birthday'] = 4;
					?>
					<tr>
						<td class="label">

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

						</td>
						<td class="data">
							<?php
							#$this->assignRef( 'playerage', $model->getAge( $this->player->birthday, $this->project->start_date ) );
							switch ( $this->config['show_birthday'] )
							{
								case 1:	 // show Birthday and Age
											$birthdateStr =	$this->referee->birthday != "0000-00-00" ?
															JHTML::date( $this->referee->birthday, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ) ) : "-";
											$birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge( $this->referee->birthday,$this->referee->deathday ) . ")";
											break;

								case 2:	 // show Only Birthday
											$birthdateStr =	$this->referee->birthday != "0000-00-00" ?
															JHTML::date( $this->referee->birthday, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ) ) : "-";
											break;

								case 3:	 // show Only Age
											$birthdateStr = sportsmanagementHelper::getAge( $this->referee->birthday,$this->referee->deathday );
											break;

								case 4:	 // show Only Year of birth
											$birthdateStr =	$this->referee->birthday != "0000-00-00" ?
															JHTML::date( $this->referee->birthday, JText::_( '%Y' ) ) : "-";
											break;

								default:	$birthdateStr = "";
											break;
							}
							echo $birthdateStr;
							?>
						</td>
					</tr>
					<?php
				}

				if (( $this->referee->address != "" ) && ( $this->config[ 'show_person_address' ] ==1  ))
				{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_ADDRESS' );
								?>

						</td>
						<td class="data">
							<?php
							echo Countries::convertAddressString(	'',
																	$this->referee->address,
																	$this->referee->state,
																	$this->referee->zipcode,
																	$this->referee->location,
																	$this->referee->address_country,
																	'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM' );

							?>
						</td>
					</tr>
					<?php
				}

				if (( $this->referee->phone != "" ) && ( $this->config[ 'show_person_phone' ] ==1  ))
				{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PHONE' );
								?>

						</td>
						<td class="data">
							<?php
							echo $this->referee->phone;
							?>
						</td>
					</tr>
					<?php
				}

				if (( $this->referee->mobile != "" ) && ( $this->config[ 'show_person_mobile' ] ==1  ))
				{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_MOBILE' );
								?>

						</td>
						<td class="data">
							<?php
							echo $this->referee->mobile;
							?>
						</td>
					</tr>
					<?php
				}

			if (($this->config['show_person_email'] == 1) && ( $this->referee->email != "" ))
			{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EMAIL' );
								?>

						</td>
						<td class="data">
							<?php
							$user = JFactory::getUser();
							if ( ( $user->id ) || ( ! $this->overallconfig['nospam_email'] ) )
							{
								?>
								<a href="mailto: <?php echo $this->referee->email; ?>">
									<?php
									echo $this->club->email;
									?>
								</a>
								<?php
							}
							else
							{
								echo JHTML::_('email.cloak', $this->referee->email );
							}
							?>
						</td>
					</tr>
					<?php
			}

				if (( $this->referee->website != "" ) && ($this->config['show_person_website'] == 1))
				{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEBSITE' );
								?>

						</td>
						<td class="data">
							<?php
							echo JHTML::_(	'link',
											$this->referee->website,
											$this->referee->website,
											array( 'target' => '_blank' ) );
							?>
						</td>
					</tr>
					<?php
				}

				if(( $this->referee->height > 0 ) && ($this->config['show_person_height'] == 1))
				{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_HEIGHT' );
								?>

						</td>
						<td class="data">
							<?php
							echo str_replace( "%HEIGHT%", $this->referee->height, JText::_( 'COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM' ) );
							?>
						</td>
					</tr>
					<?php
				}
				if (( $this->referee->weight > 0 ) && ($this->config['show_person_weight'] == 1))
				{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEIGHT' );
								?>

						</td>
						<td class="data">
							<?php
							echo str_replace( "%WEIGHT%", $this->referee->weight, JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM' ) );
							?>
						</td>
					</tr>
					<?php
				}
				if ( $this->referee->position_name != "" )
				{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_POSITION' );
								?>

						</td>
						<td class="data">
							<?php
							echo JText::_( $this->referee->position_name );
							?>
							</td>
					</tr>
					<?php
				}
				if (( ! empty( $this->referee->knvbnr ) ) && ($this->config['show_person_regnr'] == 1))
				{
					?>
					<tr>
						<td class="label">

								<?php
								echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR' );
								?>

						</td>
						<td class="data">
							<?php
							echo $this->referee->knvbnr;
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</td>
	</tr>
</table>
<br />
<?php } ?>
