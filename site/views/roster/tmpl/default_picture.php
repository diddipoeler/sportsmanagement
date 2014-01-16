<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php
	// Show team-picture if defined.
	if ( ( $this->config['show_team_logo'] == 1 ) )
	{
		?>
		<table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td align="center">
					<?php

					$picture = $this->projectteam->picture;
					if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("team") ))
					{
						$picture = $this->team->picture;
					}
					if ( !file_exists( $picture ) )
					{
						$picture = sportsmanagementHelper::getDefaultPlaceholder("team");
					}					
					$imgTitle = JText::sprintf( 'COM_SPORTSMANAGEMENT_ROSTER_PICTURE_TEAM', $this->team->name );
                    if ( !$this->config['show_highslide'] || !$this->config['show_team_logo_highslide'] )
		{
					/*
          echo sportsmanagementHelper::getPictureThumb($picture, $imgTitle,
															$this->config['team_picture_width'],
															$this->config['team_picture_height']);
					*/
          echo JHTML::image($picture, $imgTitle, array('title' => $imgTitle,'width' => $this->config['team_picture_width'] ));
          }
          else
			{
      ?>
<a href="<?php echo $picture;?>" alt="<?php echo $this->team->name;?>" title="<?php echo $this->team->name;?>" class="highslide" onclick="return hs.expand(this)">
<img src="<?php echo $picture;?>" alt="<?php echo $this->team->name;?>" title="zum Zoomen anklicken" width="<?php echo $this->config['team_picture_width'];?>" /></a>
    <?php
      }	
                    ?>
				</td>
			</tr>
		</table>
	<?php
	}
	?>