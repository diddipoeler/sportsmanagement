<?php	foreach ( $this->rows as $position_id => $players ): ?>
<div style="margin:auto; width:720px;">
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
				$playerName = JoomleagueHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, $this->config["name_format"]);
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
										$this->config['player_picture_width'],
										$this->config['player_picture_height']);
		*/
    echo JHTML::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['player_picture_width'] ));
    }								
		else
			{
      ?>
<a href="<?php echo $picture;?>" alt="<?php echo $playerName;?>" title="<?php echo $playerName;?>" class="highslide" onclick="return hs.expand(this)">
<img src="<?php echo $picture;?>" alt="<?php echo $playerName;?>" title="zum Zoomen anklicken" width="<?php echo $this->config['player_picture_width'];?>" /></a>
    <?php
      }	

				?>			  
				</td>
			    <td style="padding-left: 9px;">
			      <div class="player-position"><?php	echo JText::_( $row->position );	?></div>
				  <div class="player-name">
				  <?php 
				  	if ($this->config['link_player']==1)
					{
						$link=JoomleagueHelperRoute::getPlayerRoute($this->project->slug,$this->team->slug,$row->slug);
						echo JHTML::link($link,'<i>'.$playerName.'</i>');
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
