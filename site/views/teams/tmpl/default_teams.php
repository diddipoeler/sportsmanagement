<?php 
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/views/ranking/tmpl/default.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
<div class="table-responsive">
<table class="<?php echo $this->config['table_class']; ?>">
	<thead>
	<tr >
		<?php if ($this->config['show_small_logo']) { ?>
		<th ><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_LOGO_TEAM' ); ?></th>
		<?php } ?>
		<th ><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_TEAM' ); ?></th>
		<th ><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_CLUB' ); ?></th>
		<?php if ($this->config['show_medium_logo']) { ?>
		<th ><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_LOGO_CLUB' ); ?></th>
		<?php } ?>
    
    <?php if ($this->config['show_club_internetadress_picture']) { ?>
		<th ><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_HOMEPAGE_PICTURE' ); ?></th>
		<?php } ?>
    <?php if ($this->config['show_club_number']) { ?>
		<th ><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_CLUB_NUMBER' ); ?></th>
		<?php } ?>
		<th ><?php echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMS_NAME_CLUBADDRESS' ); ?></th>
	</tr>
	</thead>
	<?php
	$k=0;
	foreach ($this->teams as $team)
	{
	   $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['tid'] = $team->team_slug;
$routeparameter['ptid'] = 0;
$teaminfo_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);

		//$teaminfo_link	= sportsmanagementHelperRoute::getTeamInfoRoute( $this->project->slug, $team->team_slug,0,JFactory::getApplication()->input->getInt('cfg_which_database',0) );
		$clubinfo_link	= sportsmanagementHelperRoute::getClubInfoRoute( $this->project->slug, $team->club_slug,null,JFactory::getApplication()->input->getInt('cfg_which_database',0) );
		$teamTitle		= JText::sprintf( 'COM_SPORTSMANAGEMENT_TEAMS_TEAM_PROJECT_INFO', $team->team_name );
		$clubTitle		= JText::sprintf( 'COM_SPORTSMANAGEMENT_TEAMS_CLUB_PROJECT_INFO', $team->club_name );

		if ($this->config['show_small_logo']) {
			$teampic = $this->config['team_picture'];
			$picture = $team->$teampic;
            
            switch($teampic)
            {
                case 'logo_small':
                case 'logo_middle':
                case 'logo_big':
                $this->config['team_picture_width'] = 20;
                break;
                default:
                break;
            }
            
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
		<tr >
			<?php if ($this->config['show_small_logo']) { ?>
			<td ><?php echo $smallTeamLogoLink; ?></td>
			<?php } ?>
			<td >
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
			<td >
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
			<td ><?php echo $mediumClubLogoLink; ?></td>
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
            //echo '<img style="" src="http://www.thumbshots.de/cgi-bin/show.cgi?url='.$team->club_www.'">';
            echo '<img style="" src="http://api.thumbsniper.com/api_free.php?size=13&effect='.$this->config['internetadress_picture_thumbsniper_preview'].'&url='.$team->club_www.'">';
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
      
      if ($this->config['show_club_number'] ) 
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
</div>