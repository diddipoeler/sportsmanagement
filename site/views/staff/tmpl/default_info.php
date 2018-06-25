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

defined( '_JEXEC' ) or die( 'Restricted access' ); 

//echo ' config<pre>'.print_r($this->config,true).'</pre>';
//echo ' person<pre>'.print_r($this->person,true).'</pre>';
//echo ' inprojectinfo<pre>'.print_r($this->inprojectinfo,true).'</pre>';

?>
<!-- person data START -->
<h4><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA' );	?></h4>

<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
<div class="col-md-6">


		<?php
		if ( $this->config['show_photo'] )
		{
			?>

				<?php
				$picturetext = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PICTURE' );
				$imgTitle = JText::sprintf( $picturetext, sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]) );
				$picture = $this->inprojectinfo->season_picture;
				if ((empty($picture))|| ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")  ))
				{
				$picture = $this->person->picture;
				}
				
                
        echo sportsmanagementHelperHtml::getBootstrapModalImage('staffinfo'.$this->person->id,$picture,$imgTitle,$this->config['picture_width']);        
				
        
        
        ?>

			<?php
		}
		?>
</div>        
<div class="col-md-6">  

	<?php
			if(!empty($this->person->country) && $this->config["show_nationality"] )
			{
			?>
			
            
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NATIONALITY' ); ?></strong>
			<?php
					echo JSMCountries::getCountryFlag( $this->person->country ) . " " .
					JText::_( JSMCountries::getCountryName($this->person->country));
					?>
            </address>
			<?php
			}
			?>
                    
	
				
						<?php
						$outputName = JText::sprintf( '%1$s %2$s', $this->person->firstname, $this->person->lastname);
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
						
						?>
					
                
                 <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NAME' ); ?></strong>
			<?php echo $outputName; ?>
            </address>
                
                
                
				<?php if ( ! empty( $this->person->nickname ) )
				{
				    
					?>
				<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NICKNAME' ); ?></strong>
			<?php echo $this->person->nickname; ?>
            </address>
				<?php
				}
                
                
				if (	( $this->config[ 'show_birthday' ] > 0 ) &&
						( $this->config[ 'show_birthday' ] < 5 ) &&
						( $this->person->birthday != '0000-00-00' ) )
				{

					?>
					

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
								//echo JText::_( $outputStr );
								?>

						
							<?php
							switch ( $this->config['show_birthday'] )
							{
								case 1:	 // show Birthday and Age
											$birthdateStr =	$this->person->birthday != "0000-00-00" ?
															JHtml::date( $this->person->birthday, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ) ) : "-";
											$birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge( $this->person->birthday,$this->person->deathday ) . ")";
											break;

								case 2:	 // show Only Birthday
											$birthdateStr =	$this->person->birthday != "0000-00-00" ?
															JHtml::date( $this->person->birthday, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ) ) : "-";
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
							
							?>
						
                    
                    <address>
			<strong><?php echo JText::_( $outputStr ); ?></strong>
			<?php echo $birthdateStr; ?>
            </address>
                    
                    
					<?php if( $this->person->deathday != '0000-00-00' ) {?>
					
					<?php
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_DEATHDAY';
						//echo JText::_( $outputStr );
						?>
						
						<?php
						$deathdateStr =	$this->person->deathday != "0000-00-00" ?
							JHtml::date( $this->person->deathday, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ) ) : "-";
							echo '&dagger; '.$deathdateStr;
						?>
						<address>
			<strong><?php echo JText::_( $outputStr ); ?></strong>
			<?php echo '&dagger; '.$deathdateStr; ?>
            </address>
					<?php
					}
                    
                    
                    
				}
                
			if (( $this->person->address != "" ) && ( $this->config[ 'show_person_address' ] ==1  ))
				{
					?>
				<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_ADDRESS' ); ?></strong>
            <?php
						echo JSMCountries::convertAddressString(	'',
																$this->person->address,
																$this->person->state,
																$this->person->zipcode,
																$this->person->location,
																$this->person->address_country,
																'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM' );
					?>
                    </address>
				<?php
				}
                
                
			if (( $this->person->phone != "" ) && $this->config[ 'show_person_phone' ] )
				{
					?>
					<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PHONE' ); ?></strong>
			<?php echo $this->person->phone; ?>
            </address>
					<?php
				}

			if (( $this->person->mobile != "" ) && $this->config[ 'show_person_mobile' ] )
				{
					?>
					<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_MOBILE' ); ?></strong>
			<?php echo $this->person->mobile; ?>
            </address>
					<?php
				}

			if (( $this->person->email != "" ) && $this->config['show_person_email'] )
			{
					?>
					<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EMAIL' ); ?></strong>
			<?php 
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
            ?>
            </address>
					<?php
			}

			if (( $this->person->website != "" ) && $this->config['show_person_website'] )
				{
					?>
					<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEBSITE' ); ?></strong>
			<?php echo JHtml::_(	'link',
				$this->person->website,
				$this->person->website,
				array( 'target' => '_blank' ) );
                 ?>
            </address>
					<?php
				}

			if (( $this->person->height > 0 ) && $this->config['show_person_height'] )
				{
					?>
					<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_HEIGHT' ); ?></strong>
			<?php echo str_replace( "%HEIGHT%", $this->person->height, JText::_( 'COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM' ) ); ?>
            </address>
					<?php
				}
                
			if (( $this->person->weight > 0 ) && $this->config['show_person_weight'] )
				{
					?>
					<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEIGHT' ); ?></strong>
			<?php echo str_replace( "%WEIGHT%", $this->person->weight, JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM' ) );; ?>
            </address>
					<?php
					}
                    
				if ( isset($this->inprojectinfo->position_id) && $this->inprojectinfo->position_id > 0 )
				{
					?>
				
                <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_POSITION' ); ?></strong>
			<?php echo JText::_( $this->inprojectinfo->position_name ); ?>
            </address>
                
                
                
				<?php
				}

			if (( ! empty( $this->person->knvbnr ) ) && $this->config['show_person_regnr'] )
				{
					?>
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR' ); ?></strong>
			<?php echo $this->person->knvbnr; ?>
            </address>
					
					<?php
				}
				?>
			</table>
		</td>
	</tr>
</table>
<br />

</div>  
</div>  