<div class="mini-team clear">
	<table width="100%" class="contentpaneopen">
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;';
				if ( $this->config['show_team_shortform'] == 1 )
				{
					echo JText::sprintf( 'COM_JOOMLEAGUE_ROSTER_STAFF_OF2', $this->team->name, $this->team->short_name );
				}
				else
				{
					echo JText::sprintf( 'COM_JOOMLEAGUE_ROSTER_STAFF_OF', $this->team->name );
				}
				?>
			</td>
		</tr>
	</table>
<?php
			$k = 0;
			for ( $i = 0, $n = count( $this->stafflist ); $i < $n; $i++ )
			{
				$row =& $this->stafflist[$i];
				?>
				<tr class="<?php echo ($k == 0)? '' : 'sectiontableentry2'; ?>"></td><div class="mini-team-toggler">
			<div class="short-team clear">
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
				  <tbody><tr>
				    <td style="padding: 5px 10px; color: rgb(173, 173, 173); font-weight: bold; width: 200px; text-transform: uppercase;">
				      <?php
				echo $row->position;
				?>
				    </td>
				    <td>
					  <div class="player-name">
					  <?php 
					  	$playerName = JoomleagueHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, $this->config["name_format"]);
						if ($this->config['link_player']==1)
						{
							$link=JoomleagueHelperRoute::getPlayerRoute($this->project->slug,$this->team->slug,$row->slug);
							echo JHTML::link($link,'<i>'.$playerName.'</i>');
						}
						else
						{
							echo '<i>'.$playerName.'</i>';
						}
						?>
					</td>
				  </tr>
				</tbody></table>
			</div>
			<div onclick="window.location.href=''" style="cursor: pointer;" class="quick-team clear">
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
				  <tbody><tr>
				    <td style="padding: 5px 10px; color: rgb(173, 173, 173); font-weight: bold; width: 200px; text-transform: uppercase;">
				      <?php
				echo $row->position;
				?>
				    </td>
				    <td style="width: 55px;">
					  
						  <?php
		
		$imgTitle = JText::sprintf( $playerName );
		$picture = $row->picture;
		if ((empty($picture)) || ($picture == JoomleagueHelper::getDefaultPlaceholder("player") ))
		{
		$picture = $row->ppic;
		}
		if ( !file_exists( $picture ) )
		{
			$picture = JoomleagueHelper::getDefaultPlaceholder("player");
		}
		//echo JHTML::image( $picture, $imgTitle, array( ' title' => $imgTitle ) );
        if ( !$this->config['show_highslide'] )
		{
		/*
    echo JoomleagueHelper::getPictureThumb($picture, $imgTitle,
										$this->config['staff_picture_width'],
										$this->config['staff_picture_height']);
		*/
    echo JHTML::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['staff_picture_width'] ));								
		}
        else
			{
      ?>
<a href="<?php echo $picture;?>" alt="<?php echo $playerName;?>" title="<?php echo $playerName;?>" class="highslide" onclick="return hs.expand(this)">
<img src="<?php echo $picture;?>" alt="<?php echo $playerName;?>" title="zum Zoomen anklicken" width="<?php echo $this->config['staff_picture_width'];?>" /></a>
    <?php
      }	
		?>
					  
					</td>
				    <td style="padding-left: 10px;">
				      <div class="player-position"><?php
				echo  $row->position;
				?></div>
					  <div class="player-name"><?php $projectid = $this->project->id;
							$link = JoomleagueHelperRoute::getStaffRoute( $this->project->slug, $this->team->slug, $row->slug );
							echo JHTML::link($link,'<i>'.$playerName.'</i>'); ?></div>	  	  
					</td>
				  </tr>
				</tbody></table>
			</div>
		</div></td></tr><?php
				$k = 1 - $k;
			}
			?>
		</div>