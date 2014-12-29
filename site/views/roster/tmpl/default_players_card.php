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

foreach ( $this->rows as $position_id => $players ): ?>
<div style="margin:auto; width:100%">
	<!-- position header -->
	<?php 
		$row = current($players);
		$position	= $row->position;
		$k			= 0;
		$colspan	= ( ( $this->config['show_birthday'] > 0 ) ? '6' : '5' );	?>
<h2><?php	echo '&nbsp;' . JText::_( $row->position );	?></h2>
<?php foreach ($players as $row): ?>
<tr	class="<?php echo ($k == 0)?'sectiontableentry1' : 'sectiontableentry2'; ?>">
<div class="mini-player_links">
			<table>
			  <tbody><tr>
			    <td>	       
			         <div class="player-trikot">		
					<?php
					if ( ! empty( $row->position_number ) )
					{  
						echo $row->position_number;
					} 
					?>	 
					</div>		       
			     </td>
			    <td style="width: 55px;padding:0px;">		      
				<?php
				$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, $this->config["name_format"]);
				$imgTitle = JText::sprintf( $playerName );
				$picture = $row->picture;
				if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") ))
				{
					$picture = $row->ppic;
				}
				if ( !file_exists( $picture ) )
				{
					$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
				}
				//echo JHtml::image( $picture, $imgTitle, array( ' title' => $imgTitle ) );
				
?>                                    
<a href="<?php echo JURI::root().$picture;?>" title="<?php echo $playerName;?>" class="modal">
<img src="<?php echo JURI::root().$picture;?>" alt="<?php echo $playerName;?>" width="<?php echo $this->config['player_picture_width'];?>" />
</a>
<?PHP

				?>			  
				</td>
			    <td style="padding-left: 9px;">
			      <div class="player-position"><?php	echo JText::_( $row->position );	?></div>
				  <div class="player-name">
				  <?php 
				  	if ($this->config['link_player']==1)
					{
						$link=sportsmanagementHelperRoute::getPlayerRoute($this->project->slug,$this->team->slug,$row->slug);
						echo JHtml::link($link,'<i>'.$playerName.'</i>');
					}
					else
					{
						echo '<i>'.$playerName.'</i>';
					}		  
				  ?></b></a></div>
				  
				</td>
			  </tr>
			</tbody></table>
			</div>
			</tr>
			
			<?php	$k = 1 - $k; ?>
	<?php endforeach; ?>
	<div class="clear"></div></div>
	<?php endforeach;	?>
