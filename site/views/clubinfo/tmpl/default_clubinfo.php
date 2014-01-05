<?php defined( '_JEXEC' ) or die( 'Restricted access' );

if ( !isset ( $this->club ) )
{
	JError::raiseWarning( 'ERROR_CODE', JText::_( 'Error: ClubID was not submitted in URL or Club was not found in database' ) );
}
else
{
    if(!$this->config['show_club_info'] == 0){
        echo '<div class="left-column">';
    }
    else
    {
        echo'<div style="text-align:center; width:100%;">';
    }
	?>
		<!-- SHOW LOGO - START -->
		<?php
		if (( $this->config['show_club_logo']) && ( $this->club->logo_big != '' ))
		{
			$club_emblem_title = str_replace( "%CLUBNAME%", $this->club->name, JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_EMBLEM_TITLE' ) );
			$picture = $this->club->logo_big;
      /*
			echo sportsmanagementHelper::getPictureThumb($picture, 
								$club_emblem_title,
								$this->config['team_picture_width'],
								$this->config['team_picture_height'],
								1);
                */
     echo JHTML::image($picture, $club_emblem_title, array('title' => $club_emblem_title,'width' => $this->config['team_picture_width'] ));           			
		}
		?>
		<!-- SHOW LOGO - END -->
		<!-- SHOW SMALL LOGO - START -->
		<?php
		if (( $this->config['show_club_shirt']) && ( $this->club->logo_small != '' ))
		{
			$club_trikot_title = str_replace( "%CLUBNAME%", $this->club->name, JText::_( "COM_SPORTSMANAGEMENT_CLUBINFO_TRIKOT_TITLE" ) );
			$picture = $this->club->logo_small;
			echo sportsmanagementHelper::getPictureThumb($picture, 
								$club_emblem_title,
								20,
								20,
								3);				
		}
    if ( $this->club->website )
		{
      if( $this->config['show_club_internetadress_picture'] )
      {
      echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$this->club->website.'">';
      }
      
		}
    
    
		?>
		<!-- SHOW SMALL LOGO - END -->
	</div>
	<?php
        if(!$this->config['show_club_info'] == 0){
        ?>
	<div class="right-column">
		<?php
		if ( ( $this->club->address ) || ( $this->club->zipcode ) )
		{

			$addressString = Countries::convertAddressString(	$this->club->name,
																$this->club->address,
																$this->club->state,
																$this->club->zipcode,
																$this->club->location,
																$this->club->country,
																'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM' );
			?>
			<span class="clubinfo_listing_item"><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS' );
				$dummyStr = explode('<br />', $addressString);
				for ($i = 0; $i < count($dummyStr); $i++) { echo '<br />'; }
				echo '<br />';
				?></span>
			<span class="clubinfo_listing_value"><?php echo $addressString; ?>
			</span>
			<span class="clubinfo_listing_item">
			</span>
			<span class="clubinfo_listing_value">
			<?php echo JHTML::image(JURI::root().$this->clubassoc->assocflag, $this->clubassoc->name, array('title' => $this->clubassoc->name ) ).substr($this->clubassoc->name,0,30); ?>
      <br />
      </span>
			
			
			<?php
		}

		if ( $this->club->phone )
		{
			?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PHONE' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->phone; ?></span>
			<?php
		}

		if ( $this->club->fax)
		{
			?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_FAX' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->fax; ?></span>
			<?php
		}

		if ($this->club->email)
		{
			?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_EMAIL' ); ?></span>
			<span class="clubinfo_listing_value">
				<?php
				// to prevent spam, crypt email display if nospam_email is selected
				//or user is a guest
				$user = JFactory::getUser();
				if ( ( $user->id ) or ( ! $this->overallconfig['nospam_email'] ) )
				{
					?><a href="mailto: <?php echo $this->club->email; ?>"><?php echo $this->club->email; ?></a><?php
				}
				else
				{
					echo JHTML::_('email.cloak', $this->club->email );
				}
				?>
			</span>
			<?php
		}

		if ( $this->club->website )
		{
			?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_WWW' ); ?></span>
			<span class="clubinfo_listing_value">
				<?php echo JHTML::_( 'link', $this->club->website, $this->club->website, array( "target" => "_blank" ) ); ?>
			</span>
			<?php
      
      
		}

		if ( $this->club->president )
		{
			?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PRESIDENT' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->president; ?></span>
			<?php
		}

		if ( $this->club->manager )
		{
			?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_MANAGER' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->manager; ?></span>
			<?php
		}

		if ( $this->club->founded && $this->club->founded != '0000-00-00' )
		{
			?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_FOUNDED' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->founded; ?></span>
			<?php
		}
    if ( $this->club->founded_year )
		{    
      ?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_FOUNDED_YEAR' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->founded_year; ?></span>
			<?php
    }
    if ( $this->club->dissolved && $this->club->dissolved != '0000-00-00' )
		{  
      ?>
			
      <span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_DISSOLVED' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->dissolved; ?></span>      
			<?php
    }
    if ( $this->club->dissolved_year )
		{  
      ?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_DISSOLVED_YEAR' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->dissolved_year; ?></span>
			<?php
    }
    if ( $this->club->unique_id )
		{  
      ?>
			<span class="clubinfo_listing_item"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_UNIQUE_ID' ); ?></span>
			<span class="clubinfo_listing_value"><?php echo $this->club->unique_id; ?></span>
			<?php
    }        

		if ( ( $this->config['show_playgrounds_of_club'] == 1 ) && ( isset( $this->stadiums ) ) && ( count( $this->stadiums ) > 0 ) )
		{
			?>
			<!-- SHOW PLAYGROUNDS - START -->
			<?php
				$playground_number = 1;
				foreach ( $this->playgrounds AS $playground )
				{
					$link = sportsmanagementHelperRoute::getPlaygroundRoute( $this->project->slug, $playground->slug );
					$pl_dummy = JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PLAYGROUND' );
					?>
					<span class="clubinfo_listing_item"><?php echo str_replace( "%NUMBER%", $playground_number, $pl_dummy ); ?></span>
					<span class="clubinfo_listing_value"><?php echo JHTML::link( $link, $playground->name ); ?></span>
					<?php
					$playground_number++;
				}
			?>
			<!-- SHOW PLAYGROUNDS - END -->
			<?php
		}
        
        if ( $this->config['show_club_kunena_link'] == 1 && $this->club->sb_catid )
		{
		  ?>
<span class="clubinfo_listing_item">
</span>
<?PHP
$link = sportsmanagementHelperRoute::getKunenaRoute( $this->club->sb_catid );
$imgTitle = JText::_($this->club->name.' Forum');
$desc = JHTML::image('media/COM_SPORTSMANAGEMENT/jl_images/kunena.logo.png', $imgTitle, array('title' => $imgTitle,'width' => '100' ));
		?>
<span class="clubinfo_listing_value">
<?PHP
echo JHTML::link($link, $desc);
		?>
</span>
<?PHP
		}
        
        
		?>
	</div>
	<?php
	}
}
?>