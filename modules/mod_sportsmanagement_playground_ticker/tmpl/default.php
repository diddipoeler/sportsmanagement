<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version   1.1.00
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @modded 	  llambion (2020)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
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

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

$mode = $params->get('mode');

if ($playgrounds == -1)
	
	{
	  echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_NO_PLAYGROUND');
	}
	
   else
   {   

switch ($mode)
{
	/**
	 *
	 * bootstrap mode template
	 */
	case 'B':
		?>
		
	<div class="container-fluid">
		<div class="row">

        <div class="col-md-12">
				<!-- Controls -->
				<div class="controls pull-right hidden-xs">
					<a class="left fa fa-chevron-left btn btn-primary"
					href="#carousel-<?php echo $module->module; ?>-<?php echo $module->id; ?>"
					data-slide="prev"></a><a class="right fa fa-chevron-right btn btn-primary"
                                            href="#carousel-<?php echo $module->module; ?>-<?php echo $module->id; ?>"
                                            data-slide="next"></a>
				</div>

		</div> 
    </div>

    <div id="carousel-<?php echo $module->module; ?>-<?php echo $module->id; ?>" class="carousel slide hidden-xs"
         data-ride="carousel">
        <!-- Wrapper for slides -->
        <div class="carousel-inner">

			<?PHP
			$a = 0;

			foreach ($playgrounds AS $playground)
			{
				$active = ($a == 0) ? 'active' : '';

				$playground->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');

				// If ($params->get('show_picture')==1)
				// {
				if (curl_init($module->picture_server . DIRECTORY_SEPARATOR . $playground->picture) && $playground->picture != '')
				{
					$thispic = $playground->picture;
				}
                elseif (curl_init($module->picture_server . DIRECTORY_SEPARATOR . $playground->default_picture) && $playground->default_picture != '')
				{
					$thispic = $playground->default_picture;
				}

				// }


				?>
                <div class="item <?php echo $active; ?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-item">
                                <div class="photo">
                                    <img src="<?php echo $thispic; ?>" class="img-responsive" alt="a"
                                         width="<?php echo $params->get('picture_width', 50); ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info">
                        <div class="row">
                            <div class="price col-md-6">
							<?php // Club
									If ($params->get('club')==1)
									{							
									?> <h5><?php echo $playground->name; ?></h5>
									<?
									}
							?>		
                            </div>
                            <div class="price col-md-6">
							<?php // Visitors
									If ($params->get('capacity')==1)
									{
									?>	<h5 class="price-text-color"><?php echo 'Aforo: ' . $playground->max_visitors; ?></h5>	
									<?
									}								
								?>
							<?php // Address
									If ($params->get('address')==1)
									{
									?>	<h5 class="price-text-color"><?php echo 'Dirección: ' . $playground->address . '. ' . $playground->city; ?></h5>	
									<?
									}								
							?>								

							<?php // Gps Location
									If ($params->get('gps_coor')==1)
									{
									?>	<h5 class="price-text-color"><?php echo 'Coordenadas GPS: ' . $playground->latitude . ', ' . $playground->longitude; ?></h5>	
									<?
									}								
							?>
								
							<?php // Web
									If ($params->get('web')==1)
									{
									?>	<h5 class="price-text-color"><?php echo 'Web: ' . $playground->website; ?></h5>	
									<?
									}
								
							?>	         								

                            </div>
                        </div>
                    </div>
                </div>
				<?PHP
				$a++;
			}
			?>
        </div>
    </div>
</div>
		
		
		
	<?php
	break;

	/**
	 *
	 * html mode template
	 */
	case 'L':
	
		$border = $params->get('border');
		$border_color = $params->get('border_color');
		$border_rounded = $params->get('border_rounded');
		$border_shadow = $params->get('border_shadow');
		$background_color = $params->get('background_color');
		$text_color = $params->get('text_color');
		$text_size = $params->get('text_size');
		$title_color = $params->get('title_color');
		$title_size = $params->get('title_size');
		
		$style = "";
		
		if ($border)
		 {
			$style = "border: 1px solid " . $border_color . "; " ;
			$style =  $style . 'border-radius: 20px ; ';
			if($border_rounded)
			{
				$style =  $style . 'border-radius: 20px ; ';
			}
			if($border_shadow)
			{
				$style =  $style . 'box-shadow: 10px 10px 6px 3px #474747; ';
			}			
			
		 }	 
	
	if(count($playgrounds) > 0)
		{	
			
			$a = 0;

			foreach ($playgrounds AS $playground)
			{
				
							
			?>	
				
			<div class="container-fluid" style="<? echo $style; ?>
											background-color: <? echo $background_color; ?>;
											margin: 0 0 25px;">
											
			<?PHP								
				
				$active = ($a == 0) ? 'active' : '';

				$playground->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');

				// If ($params->get('show_picture')==1)
				// {
				if (curl_init($module->picture_server . DIRECTORY_SEPARATOR . $playground->picture) && $playground->picture != '')
				{
					$thispic = $playground->picture;
				}
                elseif (curl_init($module->picture_server . DIRECTORY_SEPARATOR . $playground->default_picture) && $playground->default_picture != '')
				{
					$thispic = $playground->default_picture;
				}

				// }


				?>
                    <div class="row"style="text-align:left;
												font-size: <? echo $title_size;?>px;
												color: <? echo $title_color;?> ; 
												display:block;
												margin: 0px 0px 10px 10px;
												clear:both;">
							<?php // Nombre 
									If ($params->get('name')==1)
									{							
									?> <?php echo $playground->playground_name; ?>
									<?
									}
							?>	
					</div>

                                <div class="photo">
                                    <img src="<?php echo $thispic; ?>" class="img-responsive" alt="a"
                                         width="<?php echo $params->get('picture_width', 50); ?>"/>
                                </div>
                    


                    <div>
                        <div class="row" style="text-align:left;
												font-size: <? echo $text_size;?>px;
												color: <? echo $text_color;?> ; 
												display:block;
												margin: 0px 0px 0px 10px;
												clear:both;">
								<?php // Club
									If ($params->get('club')==1)
									{
									?>	<?php echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_CLUB') . ', ' . $playground->club_name; ?>
									<?
									}								
								?>
						</div>	
						<div class="row" style="text-align:left;
												font-size: <? echo $text_size;?>px;
												color: <? echo $text_color;?> ; 
												display:block;
												margin: 0px 0px 0px 10px;
												clear:both;">
								<?php // Visitors
									If ($params->get('capacity')==1)
									{
									?>	<?php echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_CAPACITY') . ', ' . $playground->max_visitors; ?>
									<?
									}								
								?>
						</div>	
						<div class="row" style="text-align:left;
												font-size: <? echo $text_size;?>px;
												color: <? echo $text_color;?> ; 
												display:block;
												margin: 0px 0px 0px 10px;
												clear:both;">
								<?php // Address
									If ($params->get('address')==1)
									{
									?>	<?php echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_ADDRESS') . ', ' . $playground->address . '. ' . $playground->city; ?>
									<?
									}								
								?>								
						</div>
						<div class="row" style="text-align:left;
												font-size: <? echo $text_size;?>px;
												color: <? echo $text_color;?> ; 
												display:block;
												margin: 0px 0px 0px 10px;
												clear:both;">
								<?php // Gps Location
									If ($params->get('gps_coor')==1)
									{
									?>	<?php echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_GPS') . ', ' . $playground->latitude . ', ' . $playground->longitude; ?>	
									<?
									}								
								?>
						</div>
						<div class="row" style="text-align:left;
												font-size: <? echo $text_size;?>px;
												color: <? echo $text_color;?> ; 
												display:block;
												margin: 0px 0px 0px 10px;
												clear:both;">
								<?php // Web
									If ($params->get('web')==1)
									{
									?>	<?php echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_WEB') . ', ' . $playground->website; ?>	
									<?
									}
								
								?>	                          
						</div>
						<div class="row" style="text-align:left;
												font-size: <? echo $text_size;?>px;
												color: <? echo $text_color;?> ; 
												display:block;
												margin: 0px 0px 20px 10px;
												clear:both;">
								<?php // Game Field type
									If ($params->get('field_type')==1)
									{
									?>	<?php Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_SURFACE') . ', '  . PlayGround_Surface($playground->extended); ?>
									<?
									}
								
								?>	 								
						
                         </div>
                        </div>
                </div>
				<?PHP
				$a++;
			}
			
			} // if playgrounds
			else
				
				{
				  echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_NO_PLAYGROUND');	
				}
			
			?>

	<?PHP
	break;
	}

 }
   
Function PlayGround_Surface($extended)
{

  $surface = '';
  $pos = strpos($extended,'"COM_SPORTSMANAGEMENT_EXT_PLAYGROUND_GROUND":');
  $pos = $pos + strlen('"COM_SPORTSMANAGEMENT_EXT_PLAYGROUND_GROUND":') + 1;
  $end = strpos($extended,'"',$pos) ;
  
  $text = substr($extended, $pos, $end-$pos);
  
  switch ($text)
  {
	case 'Naturrasen':
		  $surface = Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_NATURAL_GRASS');
		  break;
	case 'Kunstrasen':
		  $surface = Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_SYNTHETIC_GRASS');	  
		  break;
	case 'Hyprid-Rasen':
		  $surface = Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_HYBRID_GRASS');
		  break;
	case 'TennenHartplatz':	  
		  $surface = Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_GRAND');
		  break;
	case 'Grand':	  
		  $surface = Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_GRAND');		  
		  break;
	case 'Grand':	  
		  $surface = Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_ASH');			  
		  break;
	case 'Gummiplatz':	  
		  $surface = Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_RUBBERIZED_COURT');				  
		  break;
  }  
	
return $surface;	
}






