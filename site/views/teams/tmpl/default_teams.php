<?php defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<table style='width:96%; border: 0; text-align:center;'>
	<thead>
	<tr class="sectiontableheader">
		<?php if ($this->config['show_small_logo']) { ?>
		<th class="team_logo"><?php echo JText::_( 'COM_JOOMLEAGUE_TEAMS_LOGO_TEAM' ); ?></th>
		<?php } ?>
		<th class="team_name"><?php echo JText::_( 'COM_JOOMLEAGUE_TEAMS_NAME_TEAM' ); ?></th>
		<th class="club_name"><?php echo JText::_( 'COM_JOOMLEAGUE_TEAMS_NAME_CLUB' ); ?></th>
		<?php if ($this->config['show_medium_logo']) { ?>
		<th class="club_logo"><?php echo JText::_( 'COM_JOOMLEAGUE_TEAMS_LOGO_CLUB' ); ?></th>
		<?php } ?>
    
    <?php if ($this->config['show_club_internetadress_picture']) { ?>
		<th class="club_logo"><?php echo JText::_( 'COM_JOOMLEAGUE_TEAMS_HOMEPAGE_PICTURE' ); ?></th>
		<?php } ?>
    <?php if ($this->config['show_club_number']) { ?>
		<th class="club_logo"><?php echo JText::_( 'COM_JOOMLEAGUE_TEAMS_CLUB_NUMBER' ); ?></th>
		<?php } ?>
		<th class="club_address"><?php echo JText::_( 'COM_JOOMLEAGUE_TEAMS_NAME_CLUBADDRESS' ); ?></th>
	</tr>
	</thead>
	<?php
	$k=0;
	foreach ($this->teams as $team)
	{
		$teaminfo_link	= JoomleagueHelperRoute::getTeamInfoRoute( $this->project->slug, $team->team_slug );
		$clubinfo_link	= JoomleagueHelperRoute::getClubInfoRoute( $this->project->slug, $team->club_slug );
		$teamTitle		= JText::sprintf( 'COM_JOOMLEAGUE_TEAMS_TEAM_PROJECT_INFO', $team->team_name );
		$clubTitle		= JText::sprintf( 'COM_JOOMLEAGUE_TEAMS_CLUB_PROJECT_INFO', $team->club_name );

		if ($this->config['show_small_logo']) {
			$teampic = $this->config['team_picture'];
			$picture = $team->$teampic;
			if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
			{
				$picture = JoomleagueHelper::getDefaultPlaceholder("clublogosmall");
				$image = JHTML::image( $picture, $teamTitle, array( 'title' => $teamTitle, ' border' => 0) );
			} else {
				$image = JoomleagueHelper::getPictureThumb($picture,
					$team->team_name,
					$this->config['team_picture_width'],
					$this->config['team_picture_height'],
					1);
			}
			$smallTeamLogoLink = JHTML::link( $teaminfo_link, $image );
		}

		if ($this->config['show_medium_logo']) {
			$picture = $team->logo_middle;
			if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
			{
				$picture = JoomleagueHelper::getDefaultPlaceholder("clublogomedium");
			}
			$image = JHTML::image( $picture, $clubTitle, array( 'title' => $clubTitle, ' border' => 0  ) );
			$mediumClubLogoLink = JHTML::link( $clubinfo_link, $image );
		}
		?>
		<tr class="<?php echo ($k==0)? $this->config['style_class1'] : $this->config['style_class2']; ?>">
			<?php if ($this->config['show_small_logo']) { ?>
			<td class="team_logo"><?php echo $smallTeamLogoLink; ?></td>
			<?php } ?>
			<td class="team_name">
				<?php
				if ($this->config['which_link1']==0)
				{
					if ( !empty( $team->team_www ) )
					{
						echo JHTML::link( $team->team_www, $team->team_name, array( "target" => "_blank") );
					}
					else
					{
						echo $team->team_name;
					}
				}
				if ($this->config['which_link1']==1)
				{
					echo JHTML::link( $teaminfo_link, $team->team_name );
				}
				?>
			</td>
			<td class="club_name">
				<?php
				if ($this->config['which_link2']==0)
				{
					if (!empty($team->club_www))
					{
						echo JHTML::link(	$team->club_www, $team->club_name, array( "target" => "_blank") );
					}
					else
					{
						echo $team->club_name;
					}
				}
				if ($this->config['which_link2']==1)
				{
					echo JHTML::link( $clubinfo_link, $team->club_name );
				}
				?>
			</td>
			<?php if ($this->config['show_medium_logo']) { ?>
			<td class="club_logo"><?php echo $mediumClubLogoLink; ?></td>
			<?php } ?>
      
      <?php 
      if ($this->config['show_club_internetadress_picture'] && !empty($team->club_www) ) 
      { 
      ?>
			<td class="club_logo"><?php echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$team->club_www.'">'; ?></td>
			<?php 
      }
      else
      {
      ?>
      <td class="club_logo"></td>
      <?php 
      }
      
      if ($this->config['show_club_number'] ) 
      { 
      ?>
        <td class="club_logo"><?php echo $team->unique_id; ?></td>
		<?php 
      }
      else
      {
      ?>
      <td class="club_logo"></td>
      <?php 
      }
      
       
      ?>
      
			<td class="club_address">
				<?php
				echo Countries::convertAddressString(	$team->club_name,
														$team->club_address,
														$team->club_state,
														$team->club_zipcode,
														$team->club_location,
														$team->club_country,
														'COM_JOOMLEAGUE_TEAMS_ADDRESS_FORM' );
				?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
</table>
