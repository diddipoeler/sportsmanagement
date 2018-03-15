<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_staff.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');

?>
<!-- Show Match staff -->
<?php
if (!empty($this->matchstaffpositions))
{
	?>
	<table class="table table-responsive">
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
								if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam1_id)
								{
									?>
									<li class="list">
										<?php
                                        $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $player->team_slug;
       $routeparameter['pid'] = $player->person_slug;
                                        
										$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff',$routeparameter);
										$match_player = sportsmanagementHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										echo JHtml::link($player_link,$match_player);
										$imgTitle = JText::sprintf('Picture of %1$s',$match_player);
										$picture = $player->picture;
										if (!file_exists($picture)){$picture = sportsmanagementHelper::getDefaultPlaceholder("player");}
										echo '&nbsp;';
echo sportsmanagementHelperHtml::getBootstrapModalImage('matchstaff'.$player->person_id,$picture,$imgTitle,$this->config['staff_picture_width']);
                                        ?>

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
								if ($player->position_id == $pos->position_id && $player->ptid == $this->match->projectteam2_id)
								{
									?>
									<li class="list">
										<?php
										$match_player = sportsmanagementHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										$imgTitle = JText::sprintf('Picture of %1$s',$match_player);
										$picture = $player->picture;
										if (!file_exists($picture)){$picture = sportsmanagementHelper::getDefaultPlaceholder("player");}

echo sportsmanagementHelperHtml::getBootstrapModalImage('matchstaff'.$player->person_id,$picture,$imgTitle,$this->config['staff_picture_width']);

                                        ?>
                                        
 


                                        <?php
                                        echo '&nbsp;';
                                        $routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $this->project->slug;
       $routeparameter['tid'] = $player->team_slug;
       $routeparameter['pid'] = $player->person_slug;
										$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('staff',$routeparameter);

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
