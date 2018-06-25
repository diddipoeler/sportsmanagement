<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

defined( '_JEXEC' ) or die( 'Restricted access' ); 

//echo 'referee <pre>',print_r($this->referee,true),'</pre>';
//echo 'person <pre>',print_r($this->person,true),'</pre>';

?>
<!-- person data START -->
<?php if ($this->referee) { ?>
<h2><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA' );	?></h2>

<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">

<div class="col-md-6">


	
		<?php
		if ( $this->config['show_photo'] )
		{
			?>
			
				<?php
				$picturetext=JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PICTURE' );
				$imgTitle = JText::sprintf( $picturetext, sportsmanagementHelper::formatName(null, $this->referee->firstname, $this->referee->nickname, $this->referee->lastname, $this->config["name_format"]) );
				$picture = $this->referee->picture;
				if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")  ))
				{
					$picture = $this->person->picture;
				}
				if ( !curl_init( $picture ) )
				{
					$picture = sportsmanagementHelper::getDefaultPlaceholder("player") ;
				}
			
echo sportsmanagementHelperHtml::getBootstrapModalImage('referee'.$this->referee->id,$picture,$imgTitle,$this->config['picture_width']);                                                       
				
		}
		?>
</div>        

<div class="col-md-6">
		
				<?php
				if(!empty($this->person->country) && ($this->config["show_nationality"] == 1))
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
						$outputName = JText::sprintf( '%1$s %2$s', $this->referee->firstname, $this->referee->lastname);
						if ( $this->referee->user_id )
						{
							switch ( $this->config['show_user_profile'] )
							{
								case 1:	 // Link to Joomla Contact Page
											$link = sportsmanagementHelperRoute::getContactRoute( $this->referee->user_id );
											$outputName = JHtml::link( $link, $outputName );
											break;

								case 2:	 // Link to CBE User Page with support for JoomLeague Tab
											$link = sportsmanagementHelperRoute::getUserProfileRouteCBE(	$this->referee->user_id,
																									$this->project->id,
																									$this->referee->id );
											$outputName = JHtml::link( $link, $outputName );
											break;

								default:	break;
							}
						}
						//echo $outputName;
						?>
				
                
                <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NAME' ); ?></strong>
			<?php
						echo $outputName;
						?>
            </address>
                
				<?php
						if ( ! empty( $this->referee->nickname ) )
						{
							?>
							
                            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_NICKNAME' ); ?></strong>
			<?php
						echo $this->referee->nickname;
						?>
            </address>
                            
                            
							<?php
						}
				if (	( $this->config[ 'show_birthday' ] > 0 ) &&
						( $this->config[ 'show_birthday' ] < 5 ) &&
						( $this->referee->birthday != '0000-00-00' ) )
				{
					#$this->config['show_birthday'] = 4;
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
											$birthdateStr =	$this->referee->birthday != "0000-00-00" ?
															JHtml::date( $this->referee->birthday, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ) ) : "-";
											$birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge( $this->referee->birthday,$this->referee->deathday ) . ")";
											break;

								case 2:	 // show Only Birthday
											$birthdateStr =	$this->referee->birthday != "0000-00-00" ?
															JHtml::date( $this->referee->birthday, JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE' ) ) : "-";
											break;

								case 3:	 // show Only Age
											$birthdateStr = sportsmanagementHelper::getAge( $this->referee->birthday,$this->referee->deathday );
											break;

								case 4:	 // show Only Year of birth
											$birthdateStr =	$this->referee->birthday != "0000-00-00" ?
															JHtml::date( $this->referee->birthday, JText::_( '%Y' ) ) : "-";
											break;

								default:	$birthdateStr = "";
											break;
							}
							//echo $birthdateStr;
							?>
						
                    
                    <address>
			<strong><?php echo $outputStr; ?></strong>
			<?php echo $birthdateStr; ?>
            </address>
            
					<?php
				}

				if (( $this->referee->address != "" ) && ( $this->config[ 'show_person_address' ] ==1  ))
				{
					?>
					                    
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_ADDRESS' ); ?></strong>
			<?php echo Countries::convertAddressString(	'',
																	$this->referee->address,
																	$this->referee->state,
																	$this->referee->zipcode,
																	$this->referee->location,
																	$this->referee->address_country,
																	'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM' ); ?>
            </address>
                    
                    
					<?php
				}

				if (( $this->referee->phone != "" ) && ( $this->config[ 'show_person_phone' ] ==1  ))
				{
					?>
					                    
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_PHONE' ); ?></strong>
			<?php echo $this->referee->phone; ?>
            </address>
            
					<?php
				}

				if (( $this->referee->mobile != "" ) && ( $this->config[ 'show_person_mobile' ] ==1  ))
				{
					?>
				
                    
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_MOBILE' ); ?></strong>
			<?php echo $this->referee->mobile; ?>
            </address>
                    
					<?php
				}

			if (($this->config['show_person_email'] == 1) && ( $this->referee->email != "" ))
			{
					?>
				                    
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EMAIL' ); ?></strong>
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
								echo JHtml::_('email.cloak', $this->referee->email );
							}
							?>
            </address>
                    
                    
					<?php
			}

				if (( $this->referee->website != "" ) && ($this->config['show_person_website'] == 1))
				{
					?>
					                    
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEBSITE' ); ?></strong>
			<?php echo JHtml::_(	'link',
											$this->referee->website,
											$this->referee->website,
											array( 'target' => '_blank' ) ); ?>
            </address>
					<?php
				}

				if(( $this->referee->height > 0 ) && ($this->config['show_person_height'] == 1))
				{
					?>
					
                    
                     <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_HEIGHT' ); ?></strong>
			<?php echo str_replace( "%HEIGHT%", $this->referee->height, JText::_( 'COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM' ) ); ?>
            </address>
					<?php
				}
				if (( $this->referee->weight > 0 ) && ($this->config['show_person_weight'] == 1))
				{
					?>
					
                    
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEIGHT' ); ?></strong>
			<?php echo str_replace( "%WEIGHT%", $this->referee->weight, JText::_( 'COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM' ) ); ?>
            </address>
					<?php
				}
				if ( $this->referee->position_name != "" )
				{
					?>
					
                    
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_POSITION' ); ?></strong>
			<?php echo JText::_( $this->referee->position_name ); ?>
            </address>
            
					<?php
				}
				if (( ! empty( $this->referee->knvbnr ) ) && ($this->config['show_person_regnr'] == 1))
				{
					?>
					
                    
                    <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR' ); ?></strong>
			<?php echo $this->referee->knvbnr; ?>
            </address>
					<?php
				}
				?>
			
</div>         
	


</div>

<br />
<?php } ?>
