<?php 
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/views/ranking/tmpl/default.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<table style='width:96%; border: 0; text-align:center;'>
	<thead>
	<tr class="sectiontableheader">
		<?php if ($this->config['show_small_logo']) { ?>
		<th class="team_logo"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_LOGO_TEAM' ); ?></th>
		<?php } ?>
		<th class="team_name"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_TEAM' ); ?></th>
		<th class="club_name"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_CLUB' ); ?></th>
		<?php if ($this->config['show_medium_logo']) { ?>
		<th class="club_logo"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_LOGO_CLUB' ); ?></th>
		<?php } ?>
    
    <?php if ($this->config['show_club_internetadress_picture']) { ?>
		<th class="club_logo"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_HOMEPAGE_PICTURE' ); ?></th>
		<?php } ?>
    <?php if ($this->config['show_club_number']) { ?>
		<th class="club_logo"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_CLUB_NUMBER' ); ?></th>
		<?php } ?>
		<th class="club_address"><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_CLUBADDRESS' ); ?></th>
	</tr>
	</thead>
	<?php
	$k=0;
	foreach ($this->teams as $team)
	{
		$teaminfo_link	= sportsmanagementHelperRoute::getTeamInfoRoute( $this->project->slug, $team->team_slug );
		$clubinfo_link	= sportsmanagementHelperRoute::getClubInfoRoute( $this->project->slug, $team->club_slug );
		$teamTitle		= JText::sprintf( 'COM_SPORTSMANAGEMENT_TEAMS_TEAM_PROJECT_INFO', $team->team_name );
		$clubTitle		= JText::sprintf( 'COM_SPORTSMANAGEMENT_TEAMS_CLUB_PROJECT_INFO', $team->club_name );

		if ($this->config['show_small_logo']) {
			$teampic = $this->config['team_picture'];
			$picture = $team->$teampic;
			if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
			{
				$picture = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall");
				$image = JHTML::image( $picture, $teamTitle, array( 'title' => $teamTitle, ' border' => 0) );
			} else {
				$image = sportsmanagementHelper::getPictureThumb($picture,
					$team->team_name,
					$this->config['team_picture_width'],
					'auto',
					1);
			}
			$smallTeamLogoLink = JHTML::link( $teaminfo_link, $image );
		}

		if ($this->config['show_medium_logo']) {
			$picture = $team->logo_middle;
			if ( ( is_null( $picture ) ) || ( !file_exists( $picture ) ) )
			{
				$picture = sportsmanagementHelper::getDefaultPlaceholder("clublogomedium");
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
				echo JSMCountries::convertAddressString(	$team->club_name,
														$team->club_address,
														$team->club_state,
														$team->club_zipcode,
														$team->club_location,
														$team->club_country,
														'COM_SPORTSMANAGEMENT_TEAMS_ADDRESS_FORM' );
				?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
</table>
