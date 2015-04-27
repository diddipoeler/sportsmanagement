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

defined('_JEXEC') or die('Restricted access');

//echo ' matchstaffs<br><pre>'.print_r($this->matchstaffs,true).'</pre><br>';

?>
<!-- Show Match staff -->
<?php
if (!empty($this->matchstaffpositions))
{
	?>
	<table class="table">
		<?php
		foreach ($this->matchstaffpositions as $pos)
		{
			?>
			<tr><td colspan="2" class="positionid"><?php echo JText::_($pos->name); ?></td></tr>
			<tr>
				<!-- list of home-team -->
				<td class="list">
					<div style="text-align: right; ">
						<ul style="list-style-type: none;">
							<?php
							foreach ($this->matchstaffs as $player)
							{
								if ($player->pposid==$pos->pposid && $player->ptid==$this->match->projectteam1_id)
								{
									?>
									<li class="list">
										<?php
										$player_link=sportsmanagementHelperRoute::getStaffRoute($this->project->slug,$player->team_slug,$player->person_slug);
										$match_player=sportsmanagementHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										echo JHtml::link($player_link,$match_player);
										$imgTitle=JText::sprintf('Picture of %1$s',$match_player);
										$picture=$player->picture;
										if (!file_exists($picture)){$picture = sportsmanagementHelper::getDefaultPlaceholder("player");}
										echo '&nbsp;';

                                        ?>
                                        
                                        
<a href="#" title="<?php echo $imgTitle;?>" data-toggle="modal" data-target=".matchstaff<?php echo $player->person_id;?>">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" alt="<?php echo $imgTitle;?>" width="<?php echo $this->config['staff_picture_width'];?>" />
</a>

<div id="" style="display: none;" class="modal fade matchstaff<?php echo $player->person_id;?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
<h4 class="modal-title" id="myLargeModalLabel"><?php echo $imgTitle;?></h4>
</div>
<div class="modal-body">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" class="img-responsive img-rounded center-block">
</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE');?> </button>
</div>
</div>
</div>
</div> 


                                        
									</li>
									<?php
								}
							}
							?>
						</ul>
					</div>
				</td>
				<!-- list of guest-team -->
				<td class="list">
					<div style="text-align: left;">
						<ul style="list-style-type: none;">
							<?php
							foreach ($this->matchstaffs as $player)
							{
								if ($player->pposid==$pos->pposid && $player->ptid==$this->match->projectteam2_id)
								{
									?>
									<li class="list">
										<?php
										$match_player=sportsmanagementHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										$imgTitle=JText::sprintf('Picture of %1$s',$match_player);
										$picture=$player->picture;
										if (!file_exists($picture)){$picture = sportsmanagementHelper::getDefaultPlaceholder("player");}

                                        ?>
                                        
<a href="#" title="<?php echo $imgTitle;?>" data-toggle="modal" data-target=".matchstaff<?php echo $player->person_id;?>">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" alt="<?php echo $imgTitle;?>" width="<?php echo $this->config['staff_picture_width'];?>" />
</a>

<div id="" style="display: none;" class="modal fade matchstaff<?php echo $player->person_id;?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
<h4 class="modal-title" id="myLargeModalLabel"><?php echo $imgTitle;?></h4>
</div>
<div class="modal-body">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" class="img-responsive img-rounded center-block">
</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE');?> </button>
</div>
</div>
</div>
</div> 


                                        <?php
                                        echo '&nbsp;';
										$player_link=sportsmanagementHelperRoute::getStaffRoute($this->project->slug,$player->team_slug,$player->person_slug);
										echo JHtml::link($player_link,$match_player);
										?>
									</li>
									<?php
								}
							}
							?>
						</ul>
					</div>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}
?>
<!-- END of Match staff -->