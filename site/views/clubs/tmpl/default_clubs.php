<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubs
 * @file       default_clubs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="clubs">
<table class="<?php echo $this->config['table_class']; ?>">
	<thead>
	<tr>
		<?php if ($this->config['show_small_logo'])		{ ?><th class="club_logo"><?php echo Text::_( 'COM_SPORTSMANAGEMENT_CLUBS_LOGO' ); ?></th><?php } ?>
		<?php if ($this->config['show_medium_logo'])	{ ?><th class="club_logo"><?php echo Text::_( 'COM_SPORTSMANAGEMENT_CLUBS_LOGO' ); ?></th><?php } ?>
		<?php if ($this->config['show_big_logo'])		{ ?><th class="club_logo"><?php echo Text::_( 'COM_SPORTSMANAGEMENT_CLUBS_LOGO' ); ?></th><?php } ?>
		<th class="club_name"><?php echo Text::_( 'COM_SPORTSMANAGEMENT_CLUBS_CLUBNAME' ); ?></th>
		<?php if ($this->config['show_club_teams'])		{ ?><th class="club_teams"><?php echo Text::_( 'COM_SPORTSMANAGEMENT_CLUBS_TEAMS' ); ?></th><?php } ?>
		
         <?php if ($this->config['show_club_internetadress_picture']) { ?>
		<th ><?php echo Text::_( 'COM_SPORTSMANAGEMENT_TEAMS_HOMEPAGE_PICTURE' ); ?></th>
		<?php } ?>
      
        <?php if ($this->config['show_address'])		{ ?><th class="club_address"><?php echo Text::_( 'COM_SPORTSMANAGEMENT_CLUBS_ADDRESS' ); ?></th><?php } ?>
	</tr>
	</thead>
	<?php
	$k = 0;
	foreach ($this->clubs as $club)
	{
		$clubinfo_link = sportsmanagementHelperRoute::getClubInfoRoute( $this->project->slug, $club->club_slug );
		$title			= Text::sprintf( 'COM_SPORTSMANAGEMENT_CLUBS_TITLE2', $club->name );

		$picture = $club->logo_small;
		if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
		{
			$picture = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall");
		}
		$image = HTMLHelper::image( $picture, $title, array( 'height'=>21, 'title' => $title, ' border' => 0  ) );
		$smallClubLogoLink = HTMLHelper::link( $clubinfo_link, $image );

		$picture = $club->logo_middle;
		if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
		{
			$picture = sportsmanagementHelper::getDefaultPlaceholder("clublogomedium");
		}
		$image = HTMLHelper::image( $picture, $title, array('height'=>50, 'title' => $title, ' border' => 0  ) );
		$mediumClubLogoLink = HTMLHelper::link( $clubinfo_link, $image );

		$picture = $club->logo_big;
		if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
		{
			$picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
		}
		$image = HTMLHelper::image( $picture, $title, array( 'height'=>150, 'title' => $title, ' border' => 0  ) );
		$bigClubLogoLink = HTMLHelper::link( $clubinfo_link, $image );
		?>
		<tr class="">
			<?php if ($this->config['show_small_logo'])		{ ?><td><?php echo $smallClubLogoLink;	?></td><?php } ?>
			<?php if ($this->config['show_medium_logo'])	{ ?><td><?php echo $mediumClubLogoLink;	?></td><?php } ?>
			<?php if ($this->config['show_big_logo'])		{ ?><td><?php echo $bigClubLogoLink;	?></td><?php } ?>
			<td>
				<?php
					if ( !empty( $club->website ) )
					{
						echo HTMLHelper::link	(	$club->website,
												$club->name,
												array( "target" => "_blank")
											);
					}
					else
					{
						echo $club->name;
					}
				?>
			</td>
			<?php
			if ($this->config['show_club_teams'])
			{
				?>
				<td>
					<?php
						foreach ($club->teams as $team)
						{
							//dynamic object property string
							$pic = $this->config['show_picture'];
                                          
echo sportsmanagementHelperHtml::getBootstrapModalImage('teaminfo'.$team->id,$team->$pic,$team->name,$this->config['team_picture_width']);
echo '<br />';
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $team->team_slug;
$routeparameter['ptid'] = 0;
$teaminfo_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);                                          

							echo HTMLHelper::link( $teaminfo_link, $team->name );
							echo '<br />';
						}
					?>
				</td>
				<?php
			}
          
            if ($this->config['show_club_internetadress_picture']  )
      {
      ?>
			<td >
            <?php
          
            switch ($this->config['which_internetadress_picture_provider'])
            {
            case 'thumbshots':
            echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$club->website.'">';
            break;  
            case 'thumbsniper':
            //echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$team->club_www.'">';
            echo '<img style="" src="http://api.thumbsniper.com/api_free.php?size=13&effect='.$this->config['internetadress_picture_thumbsniper_preview'].'&url='.$club->website.'">';
            break;
            }
           
          
            ?>
          
          
            </td>
			<?php
      }
			?>
          
			<?php
			if ($this->config['show_address'])
			{
			?>
				<td>
					<?php
					echo JSMCountries::convertAddressString(	$club->name,
															$club->address,
															$club->state,
															$club->zipcode,
															$club->location,
															$club->country,
															'COM_SPORTSMANAGEMENT_CLUBS_ADDRESS_FORM' );
				?>
				</td>
			<?php
			}
			?>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
</table>
</div>