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

?>
<div class="jsm">

<?PHP
if (!isset($this->team))
{
	JError::raiseWarning('ERROR_CODE', JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_ERROR'));
}
else
{
	if($this->config['show_club_info'] || $this->config['show_team_info'])
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
	//dynamic object property string
	$pic = $this->config['show_picture'];
    $picture = $this->team->$pic;
        if ( !JFile::exists(JPATH_SITE.DS.$picture) )
        {
        $picture = sportsmanagementHelper::getDefaultPlaceholder("team");
        } 
        
	/*
  echo sportsmanagementHelper::getPictureThumb($this->team->$pic,
											$this->team->name,
											$this->config['team_picture_width'],
											$this->config['team_picture_height'],
											1);
	*/
	?>
    
<a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" title="<?php echo $this->team->name;?>" data-toggle="modal" data-target="#modal">
<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $this->team->name, array('title' => $this->team->name,'class' => $this->config['team_picture_style'],'width' => $this->config['team_picture_width'] ));      
?>
</a>
<br />
<?php 
if ( $this->team->cr_projectteam_picture )
{
echo JText::sprintf('COM_SPORTSMANAGEMENT_COPYRIGHT_INFO','<i>'.$this->team->cr_projectteam_picture.'</i>');
}
?>

<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="beispielModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>

<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $this->team->name, array('title' => $this->team->name,'class' => $this->config['team_picture_style'] ));      
?>
</div>     

<?PHP  
  ?>
</div>
	<?php
	if($this->config['show_club_info'] || $this->config['show_team_info'])
	{
		?>
<div class="col-md-6">
		<?php
		if ($this->config['show_club_info'])
		{
			if ( $this->club->address || $this->club->zipcode || $this->club->location )
			{
				?>
	<div class="jl_parentContainer">
    <address>
			<strong>
		<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_ADDRESS'); ?>
        </strong>
				<?php
				$addressString = JSMCountries::convertAddressString(	$this->club->name,
															$this->club->address,
															$this->club->state,
															$this->club->zipcode,
															$this->club->location,
															$this->club->country,
															'COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_ADDRESS_FORM' );

//				$dummy = explode('<br />', $dummy);
//				for ($i = 0; $i < count($dummy); $i++)
//				{
//					if ($i > 0)
//					{
//						echo'<span class="clubinfo_listing_item">&nbsp;</span>';
//					}
//					echo'<span class="clubinfo_listing_value">'.$dummy[$i].'</span>';
//				}
//				echo '</div>';
                
                echo $addressString;
			}
            ?>
            </address>	
            <?PHP 

			if ($this->club->phone)
			{
				?>
                <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_PHONE' ); ?></strong>
			<?php echo $this->club->phone; ?>
            </address>
            
	
				<?php
			}

			if ($this->club->fax)
			{
				?>
	<div class="jl_parentContainer">
		<span class="clubinfo_listing_item"> <?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_FAX'); ?></span>
		<span class="clubinfo_listing_value"> <?php echo $this->club->fax; ?></span>
	</div>
				<?php
			}

			if ($this->club->email)
			{
				?>
	<div class="jl_parentContainer">
		<span class="clubinfo_listing_item"> <?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_EMAIL'); ?></span>
		<span class="clubinfo_listing_value"> <?php
		$user = JFactory::getUser();
		if (($user->id) or (!$this->overallconfig['nospam_email']))
		{
			echo JHtml::link('mailto:'. $this->club->email, $this->club->email);
		} else {
			echo JHtml::_('email.cloak', $this->club->email);
		}
		?>
		</span>
	</div>
				<?php
			}

			if ($this->club)
			{
				?>
                
                <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_NAME' ); ?></strong>
			<?php 
            $link = sportsmanagementHelperRoute::getClubInfoRoute($this->project->slug, $this->club->slug);
		echo JHtml::link($link, $this->club->name);
            ?>
            </address>
            
	
				<?php
			}

			if ($this->club->website)
			{
				?>
                <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_SITE' ); ?></strong>
			<?php 
            echo JHtml::link($this->club->website, $this->club->website, array( "target" => "_blank"));
            ?>
            </address>
                

			<?php
			}
			
			if ( isset($this->merge_clubs) )
			{
			?>
	<div class="jl_parentContainer">
	    <fieldset class="adminform">
			<legend>
      <?php 
      echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_MERGE_CLUBS'); 
      ?>
      </legend>
      <?PHP
      foreach ( $this->merge_clubs as $merge_clubs)
      {
      ?>
      <span class="clubinfo_listing_item"> <?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_NAME'); ?></span>
		<span class="clubinfo_listing_value"> <?php
		$link = sportsmanagementHelperRoute::getClubInfoRoute($this->project->slug, $merge_clubs->slug);
		echo JHtml::link($link, $merge_clubs->name);
		?>
		</span>
      <?PHP
      }
      ?>
      </fieldset>	
	    </div>
				<?php
			}
		}
		
		if ($this->config['show_team_info'])
		{
			?>
            
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_NAME' ); ?></strong>
			<?php 
            $link = sportsmanagementHelperRoute::getTeamInfoRoute($this->project->slug, $this->team->slug);
		echo JHtml::link($link, $this->team->tname);
            ?>
            </address>
            
	<address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_NAME_SHORT' ); ?></strong>
			<?php 
            $link = sportsmanagementHelperRoute::getTeamStatsRoute($this->project->slug, $this->team->slug);
		echo JHtml::link($link, $this->team->short_name);
            ?>
            </address>
            
	
			<?php
			if ($this->team->info)
			{
				?>
                
                <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_INFO' ); ?></strong>
			<?php 
            echo $this->team->info;
            ?>
            </address>
            

			<?php
			}

			if ($this->team->website)
			{
				?>
                <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_SITE' ); ?></strong>
			<?php 
            echo JHtml::link($this->team->team_website, $this->team->team_website, array( "target" => "_blank"));
            ?>
            </address>
            

			<?php
			}
		}
		?>
	</div>
	<br />
	<?php
	}
    ?>
    
    <?PHP
}
?>
</div>
</div>