<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php
	// Show team-picture if defined.
	if ( ( $this->config['show_team_logo'] == 1 ) )
	{
		?>
		<table align="center" border="0" cellpadding="0" cellspacing="0">
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
           
      
      ?>


<a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" title="<?php echo $this->team->name;?>" data-toggle="modal" data-target="#modal">
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" alt="<?php echo $this->team->name;?>" width="<?php echo $this->config['team_picture_width'];?>" />
</a>

<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="beispielModalLabel" aria-hidden="true">
<div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>

      </div>
<img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" alt="<?php echo $this->team->name;?>"  />

</div>      

    <?php
      	
                    ?>
				</td>
			</tr>
		</table>
	<?php
	}
	?>