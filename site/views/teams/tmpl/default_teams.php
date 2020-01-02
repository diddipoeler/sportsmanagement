<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_teams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage teams
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
usort($this->teams, function($a, $b) { return $b->club_name < $a->club_name; });

?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="teams">
<table class="<?php echo $this->config['table_class']; ?> ">
	<thead>
	<tr >
		<?php if ($this->config['show_small_logo']) { ?>
		<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_TEAMS_LOGO_TEAM' ); ?></th>
		<?php } ?>
		<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_TEAM' ); ?></th>
		<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_CLUB' ); ?></th>
		<?php if ($this->config['show_medium_logo']) { ?>
		<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_TEAMS_LOGO_CLUB' ); ?></th>
		<?php } ?>
    
    <?php if ($this->config['show_club_internetadress_picture']) { ?>
		<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_TEAMS_HOMEPAGE_PICTURE' ); ?></th>
		<?php } ?>
    <?php if ($this->config['show_club_number']) { ?>
		<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_TEAMS_CLUB_NUMBER' ); ?></th>
		<?php } ?>
		<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_CLUBADDRESS' ); ?></th>
<?php if ($this->config['show_club_playground']) { ?>
<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_CLUBPLAN_PLAYGROUND' ); ?></th>		
<?php } ?>		
	
	</tr>
	</thead>
	<?php
	$k=0;
	foreach ($this->teams as $team)
	{
	   $routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $team->team_slug;
$routeparameter['ptid'] = 0;
$teaminfo_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);

		$clubinfo_link = sportsmanagementHelperRoute::getClubInfoRoute( $this->project->slug, $team->club_slug,null,Factory::getApplication()->input->getInt('cfg_which_database',0) );
		$teamTitle = Text::sprintf( 'COM_SPORTSMANAGEMENT_TEAMS_TEAM_PROJECT_INFO', $team->team_name );
		$clubTitle = Text::sprintf( 'COM_SPORTSMANAGEMENT_TEAMS_CLUB_PROJECT_INFO', $team->club_name );

		if ($this->config['show_small_logo']) {
			$teampic = $this->config['team_picture'];
			$picture = $team->$teampic;
            
            switch($teampic)
            {
                case 'logo_small':
                case 'logo_middle':
                case 'logo_big':
                $this->config['team_picture_width'] = $this->config['team_logo_width'];
                break;
                default:
                break;
            }
            
			if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
			{
				$picture = sportsmanagementHelper::getDefaultPlaceholder("team_picture");
				$image = HTMLHelper::image( $picture, $teamTitle, array( 'title' => $teamTitle, ' border' => 0,' width' => $this->config['team_picture_width']) );
			} else {
				$image = sportsmanagementHelper::getPictureThumb($picture,
					$team->team_name,
					$this->config['team_picture_width'],
					'auto',
					1);
			}
			$smallTeamLogoLink = HTMLHelper::link( $teaminfo_link, $image );
		}

		if ($this->config['show_medium_logo']) {
			$picture = $team->logo_big;
			if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
			{
				$picture = sportsmanagementHelper::getDefaultPlaceholder("clublogomedium");
			}
			$image = HTMLHelper::image( $picture, $clubTitle, array( 'title' => $clubTitle, ' border' => 0,' width' => $this->config['team_logo_width']  ) );
			$mediumClubLogoLink = HTMLHelper::link( $clubinfo_link, $image );
		}
		?>
		<tr >
			<?php if ( $this->config['show_small_logo'] ) { ?>
			<td name="show_small_logo"><?php echo $smallTeamLogoLink; ?></td>
			<?php } ?>
			<td name="which_link1">
				<?php
				if ( $this->config['which_link1'] == 0 )
				{
					if ( !empty( $team->team_www ) )
					{
						echo HTMLHelper::link( $team->team_www, $team->team_name, array( "target" => "_blank") );
					}
					else
					{
						echo $team->team_name;
					}
				}
				if ( $this->config['which_link1'] == 1 )
				{
					echo HTMLHelper::link( $teaminfo_link, $team->team_name );
				}
				?>
			</td>
			<td name="which_link2">
				<?php
				if ( $this->config['which_link2'] == 0 )
				{
					if (!empty($team->club_www))
					{
						echo HTMLHelper::link(	$team->club_www, $team->club_name, array( "target" => "_blank") );
					}
					else
					{
						echo $team->club_name;
					}
				}
				if ( $this->config['which_link2'] == 1 )
				{
					echo HTMLHelper::link( $clubinfo_link, $team->club_name );
				}
				?>
			</td>
			<?php if ( $this->config['show_medium_logo'] ) { ?>
			<td name="show_medium_logo"><?php echo $mediumClubLogoLink; ?></td>
			<?php } ?>
      
      <?php 
      if ($this->config['show_club_internetadress_picture'] && !empty($team->club_www) ) 
      { 
      ?>
			<td >
            <?php 
            
            switch ($this->config['which_internetadress_picture_provider'])
            {
            case 'thumbshots':
            echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$team->club_www.'">';
            break;    
            case 'thumbsniper':
            echo '<img style="" src="http://api.thumbsniper.com/api_free.php?size=13&effect='.$this->config['internetadress_picture_thumbsniper_preview'].'&url='.$team->club_www.'">';
            break;
            case 'pagepeeker':
            echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size='.$this->config['pagepeeker_size'].'&url='.$team->club_www.'">';
            break;
            }
            ?>
            </td>
			<?php 
      }
      else
      {
      ?>
      <td ></td>
      <?php 
      }
      
      if ( $this->config['show_club_number'] ) 
      { 
      ?>
        <td ><?php echo $team->unique_id; ?></td>
		<?php 
      }
      else
      {
      ?>
      <td ></td>
      <?php 
      }
      ?>

<td >
<?php
echo JSMCountries::convertAddressString($team->club_name,
$team->club_address,
$team->club_state,
$team->club_zipcode,
$team->club_location,
$team->club_country,
'COM_SPORTSMANAGEMENT_TEAMS_ADDRESS_FORM' );
if ( $this->config['show_club_phone'] ) 
{ 
?>
<br />
<?php
echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/phone_14402.png', '', 'width="16"');	
echo $team->club_phone;
?>
<br />
<?php	
}
if ( $this->config['show_club_fax'] ) 
{ 
?>
<br />
<?php
echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/fax_icon-icons_com_52496.png', '', 'width="16"');	
echo $team->club_fax;
}
if ( $this->config['show_club_email'] ) 
{ 
?>
<br />
<?php
echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/mail.png', '', '');
echo $team->club_email;
}    

if ( $this->config['show_club_facebook'] ) 
{ 
?>
<br />
<?php
$googlelink = $team->facebook;	
echo HTMLHelper::link($googlelink,
HTMLHelper::image('administrator/components/com_sportsmanagement/assets/images/facebook.png',$team->facebook), array('target' => '_blank' ,'title' => $team->club_name ) );	
}    
		
		
if ( $this->config['show_googlemap_link'] ) 
{
?>
<br />
<?php	
$googlelink = 'http://maps.google.com/maps?f=q&hl=de&geocode=&q='.$team->club_address.', '.$team->club_zipcode.' '.$team->club_location;	
echo HTMLHelper::link($googlelink,
HTMLHelper::image('images/com_sportsmanagement/database/jl_images/map.gif',$team->club_name), array('target' => '_blank' ,'title' => $team->club_name ) );	
?>
	
<?php
} 
?> 
</td>
			
<?php if ( $this->config['show_club_playground'] ) { ?>
<td >
<?php
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['pgid'] = $team->playground_slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground', $routeparameter);	
echo HTMLHelper::link($link, $team->playground_name);	
					
if ( $this->config['show_playground_picture'] ) 
{					
?>
<br>	
<?php	
if ( ( is_null( $team->playground_picture ) ) || ( !file_exists( $team->playground_picture ) ) )
{
$team->playground_picture = sportsmanagementHelper::getDefaultPlaceholder("stadium");
}	
echo sportsmanagementHelperHtml::getBootstrapModalImage('playgroundclubinfo' . $team->team_name,
            $team->playground_picture,
            $team->playground_name,
            $this->config['playground_picture_width'],
            '',
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']);  	
}					
?>
</td>		
<?php } ?>			
			
			
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
</table>
</div>
