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

defined( '_JEXEC' ) or die( 'Restricted access' );

//echo '<pre>'.print_r($this->config,true).'</pre>';

if ( !isset ( $this->club ) )
{
	JError::raiseWarning( 'ERROR_CODE', JText::_( 'Error: ClubID was not submitted in URL or Club was not found in database' ) );
}
else
{
    if( $this->config['show_club_info'] == 1)
    {
        ?>
<!--        <div class="container-fluid"> -->
        <div class="row">
        <div class="col-md-6">
        <?PHP
    }
    else
    {
        ?>
<!--        <div class="container-fluid"> -->
        <div class="row">
        <div style="text-align:center; width:100%;">
        <?PHP
    }
	?>
		<!-- SHOW LOGO - START -->
		<?php
		if (( $this->config['show_club_logo']) && ( $this->club->logo_big != '' ))
		{
			$club_emblem_title = str_replace( "%CLUBNAME%", $this->club->name, JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_EMBLEM_TITLE' ) );
			$picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER.$this->club->logo_big;
      /*
			echo sportsmanagementHelper::getPictureThumb($picture, 
								$club_emblem_title,
								$this->config['team_picture_width'],
								$this->config['team_picture_height'],
								1);
                */
     //echo JHtml::image($picture, $club_emblem_title, array('title' => $club_emblem_title,'width' => $this->config['team_picture_width'] ));           			
		}
		?>
        
<a href="<?php echo $picture;?>" title="<?php echo $club_emblem_title;?>" data-toggle="modal" data-target="#modal">
<img src="<?php echo $picture;?>" alt="<?php echo $club_emblem_title;?>" width="<?php echo $this->config['team_picture_width'];?>" />
</a>        
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="beispielModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image($picture, $club_emblem_title, array('title' => $club_emblem_title,'class' => "img-rounded" ));      
?>
</div> 
        
		<!-- SHOW LOGO - END -->
		<!-- SHOW SMALL LOGO - START -->
		<?php
		if (( $this->config['show_club_shirt']) && ( $this->club->logo_small != '' ))
		{
			$club_trikot_title = str_replace( "%CLUBNAME%", $this->club->name, JText::_( "COM_SPORTSMANAGEMENT_CLUBINFO_TRIKOT_TITLE" ) );
			$picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER.$this->club->logo_small;
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
        if( $this->config['show_club_info'] == 1 )
        {
        ?>
	<div class="col-md-6">
		<?php
		if (  $this->club->address  ||  $this->club->zipcode ||  $this->club->location )
		{

			$addressString = JSMCountries::convertAddressString(	$this->club->name,
																$this->club->address,
																$this->club->state,
																$this->club->zipcode,
																$this->club->location,
																$this->club->country,
																'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM' );
			?>
            <address>
			<strong><?php
				echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS' );
				//$dummyStr = explode('<br />', $addressString);
				//for ($i = 0; $i < count($dummyStr); $i++) { echo '<br />'; }
				//echo '<br />';
				?></strong>
			<?php echo $addressString; ?>
			
			<span class="clubinfo_listing_value">
			<?php 
            if ( isset($this->clubassoc->name) )
            {
            echo JHtml::image(JURI::root().$this->clubassoc->assocflag, $this->clubassoc->name, array('title' => $this->clubassoc->name ) ).substr($this->clubassoc->name,0,30); 
            }
            ?>
      <br />
      </span>
		</address>	
			
			<?php
		}

		if ( $this->club->phone )
		{
			?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PHONE' ); ?></strong>
			<?php echo $this->club->phone; ?>
            </address>
			<?php
		}

		if ( $this->club->fax)
		{
			?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_FAX' ); ?></strong>
			<?php echo $this->club->fax; ?>
            </address>
			<?php
		}

		if ($this->club->email)
		{
			?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_EMAIL' ); ?></strong>
			
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
					//echo JHtml::_('email.cloak', $this->club->email );
                    echo $this->club->email;
				}
				?>
			</address>
			<?php
		}

		if ( $this->club->website )
		{
			?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_WWW' ); ?></strong>
			
				<?php echo JHtml::_( 'link', $this->club->website, $this->club->website, array( "target" => "_blank" ) ); ?>
			
            <address>
			<?php
      
      
		}

		if ( $this->club->president )
		{
			?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PRESIDENT' ); ?></strong>
			<?php echo $this->club->president; ?>
            </address>
			<?php
		}

		if ( $this->club->manager )
		{
			?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_MANAGER' ); ?></strong>
			<?php echo $this->club->manager; ?>
            </address>
			<?php
		}

		if ( $this->club->founded && $this->club->founded != '0000-00-00' )
		{
			?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_FOUNDED' ); ?></strong>
			<?php echo sportsmanagementHelper::convertDate($this->club->founded,1) ; ?>
            </address>
			<?php
		}
    if ( $this->club->founded_year )
		{    
      ?>
      <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_FOUNDED_YEAR' ); ?></strong>
			<?php echo $this->club->founded_year; ?>
            </address>
			<?php
    }
    if ( $this->club->dissolved && $this->club->dissolved != '0000-00-00' )
		{  
      ?>
		<address>	
      <strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_DISSOLVED' ); ?></strong>
			<?php echo sportsmanagementHelper::convertDate($this->club->dissolved,1) ?>   
            </address>   
			<?php
    }
    if ( $this->club->dissolved_year )
		{  
      ?>
      <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_DISSOLVED_YEAR' ); ?></strong>
			<?php echo $this->club->dissolved_year; ?>
            </address>
			<?php
    }
    if ( $this->club->unique_id )
		{  
      ?>
      <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_UNIQUE_ID' ); ?></strong>
			<?php echo $this->club->unique_id; ?>
            </address>
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
					$link = sportsmanagementHelperRoute::getPlaygroundRoute( $this->project->slug, $playground->slug,JRequest::getInt('cfg_which_database',0) );
					$pl_dummy = JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PLAYGROUND' );
					?>
                    <address>
					<strong><?php echo str_replace( "%NUMBER%", $playground_number, $pl_dummy ); ?></strong>
					<?php echo JHtml::link( $link, $playground->name ); ?>
                    </address>
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
$desc = JHtml::image('media/COM_SPORTSMANAGEMENT/jl_images/kunena.logo.png', $imgTitle, array('title' => $imgTitle,'width' => '100' ));
		?>
<span class="clubinfo_listing_value">
<?PHP
echo JHtml::link($link, $desc);
		?>
</span>
<?PHP
		}

if ( $this->clubhistorysorttree )
{
?>
<fieldset>
<legend>
<strong>
<br />
<?php echo JText::_('Fusionen'); ?>
</strong>
</legend>

<div class="span4">
<div class="dtree">
      <a href="javascript: d<?PHP echo $this->modid; ?>.openAll();">
        <?php echo JText::_('&ouml;ffnen'); ?>&nbsp;&nbsp;</a>
      <a href="javascript: d<?PHP echo $this->modid; ?>.closeAll();">
        <?php echo JText::_('schliessen'); ?></a>
    </div>
<script type="text/javascript" language="javascript">
    <!--
<?php echo $this->clubhistorysorttree; ?>
    // -->
    </script>
</div>

</fieldset>

<?PHP        
}
        
		?>
	</div>
    </div> 
<!--    </div> -->
    
	<?php
	}
}
?>