<?php defined('_JEXEC') or die('Restricted access');
?>
<!-- Show Match staff -->
<?php
if (!empty($this->matchstaffpositions))
{
	?>
	<table class="matchreport">
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
										$player_link=JoomleagueHelperRoute::getStaffRoute($this->project->slug,$player->team_slug,$player->person_slug);
										$match_player=JoomleagueHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										echo JHTML::link($player_link,$match_player);
										$imgTitle=JText::sprintf('Picture of %1$s',$match_player);
										$picture=$player->picture;
										if (!file_exists($picture)){$picture = JoomleagueHelper::getDefaultPlaceholder("player");}
										echo '&nbsp;';
                                        /*
										echo JoomleagueHelper::getPictureThumb($picture, 
												$imgTitle,
												$this->config['staff_picture_width'],
												$this->config['staff_picture_height']);
										*/
                                        ?>
                                        <a href="<?php echo $picture;?>" alt="<?php echo $imgTitle;?>" title="<?php echo $imgTitle;?>" class="highslide" onclick="return hs.expand(this)">
                                        <?php
                                        echo JHTML::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['staff_picture_width'] ));
                                        ?>
                                        </a>
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
										$match_player=JoomleagueHelper::formatName(null,$player->firstname,$player->nickname,$player->lastname, $this->config["name_format"]);
										$imgTitle=JText::sprintf('Picture of %1$s',$match_player);
										$picture=$player->picture;
										if (!file_exists($picture)){$picture = JoomleagueHelper::getDefaultPlaceholder("player");}
										/*
                                        echo JoomleagueHelper::getPictureThumb($picture, 
												$imgTitle,
												$this->config['staff_picture_width'],
												$this->config['staff_picture_height']);
										*/
                                        ?>
                                        <a href="<?php echo $picture;?>" alt="<?php echo $imgTitle;?>" title="<?php echo $imgTitle;?>" class="highslide" onclick="return hs.expand(this)">
                                        <?PHP
                                        echo JHTML::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['staff_picture_width'] ));
                                        ?>
                                        </a>
                                        <?php
                                        echo '&nbsp;';
										$player_link=JoomleagueHelperRoute::getStaffRoute($this->project->slug,$player->team_slug,$player->person_slug);
										echo JHTML::link($player_link,$match_player);
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