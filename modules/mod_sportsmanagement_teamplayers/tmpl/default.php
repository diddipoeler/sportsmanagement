<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.1.0
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_teamplayers
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @modded	   llambion (2020)
 *             Added players carrousel, mins played and position fields
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

// check if any players returned
$items = count($list['roster']);

if (!$items)
{
	echo '<p class="modteamplayers">' . Text::_('NO ITEMS') . '</p>';

	return;
} 

$mode = $params->get('template');

// mins per match
$time_for_match = $list['project']->game_regular_time;

//var_dump($list['project']);

			?>

			<div class="container-fluid">

			<?	


switch ($mode)
{
	/**
	 *
	 * HTML Template
	 */
	case 'L':
	
?>
			<?php if ($params->get('show_project_name', 0)) : ?>
			<div class="projectname"><?php echo $list['project']->name; ?></div>
			<?php endif; ?>
	

			<?php if ($params->get('show_team_name', 0)) : ?>
			<div class="projectname"><?php echo $list['project']->team_name; ?></div>
			<?php endif; ?>

			<?
			/* Llambion, añado la posicion de los jugadores */
			$pos = "";
			$oldpos = "";
			?> 

			<table class="table">
			<tbody>
			<tr class="sectiontableheader">
				<td class="eventtype">Jugador</td>
				<?
					if ($params->get('show_mins_played') == 1)
					{				
					?>		
						<td class="eventtype">Mins</td>
					<?	
					}
				?>	
			</tr>

 
           <? 
			
			$players = array(); 
			$plink ="";
			$mins="";
			
			?>
			
			<?php foreach (array_slice($list['roster'], 0, $params->get('limit', 24)) as $items) : ?>

				<?php foreach (array_slice($items, 0, $params->get('limit', 24)) as $item) : ?>
				
                    <?				
	                 $plink = modSportsmanagementTeamPlayersHelper::getPlayerLink($item, $params, $list['project'], $module);			
					 $mins  = modSportsmanagementTeamPlayersHelper::getPlayerMinsPlayed($item, $params, $list['project'], $module, $time_for_match);		
					 
	                 array_push ($players, array('posicion'=>$item->position, 'jugador'=>$plink, 'mins_played'=>$mins) );			  
					 
					 ?>
			
				<?php endforeach; ?>

			<?php endforeach; ?>
			
<?

			if ($params->get('show_positions') == 0)
				{	
							// Sort criteria
				$sortCriteria =  array('mins_played' => array(SORT_DESC, SORT_NUMERIC)
										);
				}
			else
				{	
							// Sort criteria
				$sortCriteria =  array('posicion' => array(SORT_DESC, SORT_STRING),
									   'mins_played' => array(SORT_DESC, SORT_NUMERIC)
										);
				}

			//Sort Array
			$sortedData = MultiSort($players, $sortCriteria, true);
							
			$max=sizeof($sortedData);
			$pos = $sortedData[0]['posicion'];
			$odd = false;
				
			for($i=0; $i<$max; $i++) {
					
				$pos = $sortedData[$i]['posicion'];
					
				if ($params->get('show_positions') == 1)
				{							
					if($pos <> $oldpos )
						{
						?>
						<td><div class="player_position"><?php echo Text::_($sortedData[$i]['posicion']); ?></div></td>
		
						<?php
						}
				}
				$oldpos = $pos; 
				if ($odd)
					{
					  ?>						
					  <tr class="sectiontableentry1">
					  <?
					  $odd = !$odd;
					}  
				else
					{
					  ?>						
					  <tr class="sectiontableentry2">
					  <?
					  $odd = !$odd;
					}
				?>
				<td>	
				<?php echo $sortedData[$i]['jugador']; ?>					
				</td>
				</div></div>
				<?
				if ($params->get('show_mins_played') == 1)
					{
					?>	
					<td>
					<?php echo $sortedData[$i]['mins_played']; 	?>					
					</td>
					<?
					}
				?>
				</tr>
				<?
			} // end for			
			?>			
						
			
			</tr>
			</tbody>
			</table>

		</div>
	</div>	
	<?php
	break;

	/**
	 *
	 * Carrousel mode template
	 */

	case 'C':	
?>	
			<?php
            
			if ($params->get('show_project_name', 0)) : ?>
			<div class="projectname"><?php echo $list['project']->name; ?></div>
			<?php endif; ?>
	

			<?php if ($params->get('show_team_name', 0)) : ?>
			<div class="projectname"><?php echo $list['project']->team_name; ?></div>
			<?php endif; ?>

			<?
			
			$players = array(); 
			$plink ="";
			$mins="";
			
			?>
			
			<?php foreach (array_slice($list['roster'], 0, $params->get('limit', 24)) as $items) : ?>

				<?php foreach (array_slice($items, 0, $params->get('limit', 24)) as $item) : ?>
				
                    <?				
	                 $plink = modSportsmanagementTeamPlayersHelper::getPlayerLinkAndFlag($item, $params, $list['project'], $module);			
					 $mins  = modSportsmanagementTeamPlayersHelper::getPlayerMinsPlayed($item, $params, $list['project'], $module, $time_for_match);
					 $image = $item->picture;
					 
	                 array_push ($players, array('posicion'=>$item->position, 'jugador'=>$plink['name'], 'flag'=>$plink['flag'], 'mins_played'=>$mins,'player_image'=>$image) );			  
					 
					 ?>
			
				<?php endforeach; ?>

			<?php endforeach; ?>

			<?
			if ($params->get('show_positions') == 0)
				{	
							// Sort criteria
				$sortCriteria =  array('mins_played' => array(SORT_DESC, SORT_NUMERIC)
										);
				}
			else
				{	
							// Sort criteria
				$sortCriteria =  array('posicion' => array(SORT_DESC, SORT_STRING),
									   'mins_played' => array(SORT_DESC, SORT_NUMERIC)
										);
				}

			//Sort Array
			$sortedData = MultiSort($players, $sortCriteria, true);
							
			$max=sizeof($sortedData);
			$pos = $sortedData[0]['posicion'];
			$odd = false;
					
			?>	
			
			<ul class="tukuslider">
			
			<?
			for($i=0; $i<$max; $i++) {
					
				$pos = $sortedData[$i]['posicion'];	
				$image = "";

				if (file_exists(JPATH_BASE . '/' . $sortedData[$i]['player_image']) && $sortedData[$i]['player_image'] != '')
						{
							$image = Uri::base() . '/' . $sortedData[$i]['player_image'];
						}
                        elseif (file_exists(JPATH_BASE . '/' . $sortedData[$i]['player_image']) && $sortedData[$i]['player_image'] != '')
						{
							$image = Uri::base() . '/' . $sortedData[$i]['player_image'];
						}	
				
				?>
				
				<div class="slide">
				
					<div style="width: 150px;
								height:210px;
								padding: 10px 10px 20px 10px;
								border: 1px solid #BFBFBF;
								background-color: white;
								box-shadow: 10px 10px 5px #aaaaaa;">
				
				     <? echo '				
						<div style="position: absolute; 
									top: 50px;
									width: 140px;
									height:140px;
									background-size: contain;
									background-repeat: no-repeat;
									background-position: 50% 50%;
									background-image: url(' . $image . ');
									"> ';
						?>	
									
						</div>


						<? // Player's flag
							if ($params->get('show_player_flag') == 1)
								{
						?>						
									<div style="position: absolute; top: 10px; left: 10px;">	
									<?	echo $sortedData[$i]['flag'] .'<br>'; ?>
									</div>
						<?
								}
						?>										

						<div style="position: absolute; top: 5px; left: 30px;">	
							<?	echo $sortedData[$i]['jugador'] .'<br>'; ?>
						</div>

						
						<? // Mins played
							if ($params->get('show_positions') == 1)
								{
						?>					
									<div style="position: absolute; bottom: 25px; left: 10px; font-size: 15px;">	
									<?	echo $sortedData[$i]['posicion'] .'<br>'; ?>
									</div>
						<?
								}
						?>	
					
						<?
							if ($params->get('show_mins_played') == 1)
								{
						?>					
									<div style="position: absolute; bottom: 5px; left: 10px;">	
									<?	echo Text::_('MOD_SPORTSMANAGEMENT_TEAMPLAYERS_MINS_PLAYED') .', ' . $sortedData[$i]['mins_played'] .'<br>'; ?>
									</div>				
						<?
								}
						?>									

					</div>
				</div>


			<?	
	
			} // end for

			// Calc horizontal width
			if ($params->get('slider_mode') == 'H')
				{	
				  $slider_width = $params->get('max_slides') * 65;
				  $slider_mode = 'horizontal';
				}
			else
				{
				  $slider_mode = 'vertical';
				  $slider_width = 180;
				}			
					
			// responsive
			if ($params->get('slider_responsive') == 1)
				{				
				  $responsive = 'true';
				}
			else
				{				
				  $responsive = 'false';
				}
			// auto slider	
			if ($params->get('slider_auto') == 1)
				{				
				  $auto_slider = 'true';
				}
			else
				{				
				  $auto_slider = 'false';
				}				
			// navigation controls
			if ($params->get('slider_navigation') == 1)
				{				
				  $navigation = 'true';
				}
			else
				{				
				  $navigation = 'false';
				}				
			// pagigation controls
			if ($params->get('slider_pagination') == 1)
				{				
				  $pagination = 'true';
				}
			else
				{				
				  $pagination = 'false';
				}				
			
		
			
			?>			
							  
            </ul>
			
			</div>
			<script type="text/javascript">
			var $jQ = jQuery.noConflict();			

			$jQ(document).ready(function(){
				$jQ('.tukuslider').bxSlider({
				slideWidth: <? echo $slider_width; ?>,
				mode: '<? echo $slider_mode; ?>',
				minSlides: <? echo $params->get('max_slides'); ?>,
				maxSlides: <? echo $params->get('max_slides'); ?>,
				moveSlides: <? echo $params->get('max_slides'); ?>,
				slideMargin: 10,
				preloadImages: 'visible',
				speed: <? echo $params->get('slider_speed'); ?>,
				pager: <? echo $pagination; ?>,
				controls: <? echo $navigation; ?>,
				responsive: <? echo $responsive; ?>,
				auto: <? echo $auto_slider; ?>
				});
			});

			</script>	
		
		<?PHP
		

	break;
}		



function MultiSort($data, $sortCriteria, $caseInSensitive = true)
{
  if( !is_array($data) || !is_array($sortCriteria))
    return false;      
  $args = array();
  $i = 0;
  foreach($sortCriteria as $sortColumn => $sortAttributes) 
  {
    $colList = array();
    foreach ($data as $key => $row)
    {
      $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $sortAttributes) || in_array(SORT_REGULAR, $sortAttributes));
      $rowData = $convertToLower ? strtolower($row[$sortColumn]) : $row[$sortColumn];
      $colLists[$sortColumn][$key] = $rowData;
    }
    $args[] = &$colLists[$sortColumn];
     
    foreach($sortAttributes as $sortAttribute)
    {     
      $tmp[$i] = $sortAttribute;
      $args[] = &$tmp[$i];
      $i++;     
     }
  }
  $args[] = &$data;
  call_user_func_array('array_multisort', $args);
  return end($args);
} 

?>



					
					