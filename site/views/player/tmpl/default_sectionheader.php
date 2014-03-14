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

JHtml::_('behavior.mootools');
$option = JRequest::getCmd('option');
$modalheight = JComponentHelper::getParams($option)->get('modal_popup_height', 600);
$modalwidth = JComponentHelper::getParams($option)->get('modal_popup_width', 900);

?>
<table class="contentpaneopen">
	<tr>
		<td class="contentheading">
			<?php
	echo JText::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));
	
	if ( $this->showediticon )
	{
		/*
    $link = sportsmanagementHelperRoute::getPlayerRoute( $this->project->id, $this->teamPlayer->team_id, $this->person->id, 'person.edit' );
		$desc = JHtml::image(
			"media/com_sportsmanagement/jl_images/edit.png",
			JText::_( 'COM_SPORTSMANAGEMENT_PERSON_EDIT' ),
			array( "title" => JText::_( "COM_SPORTSMANAGEMENT_PERSON_EDIT" ) )
		);
	    echo " ";
	    echo JHtml::_('link', $link, $desc );
	    */
	
	?>   
	             <a	rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
									href="index.php?option=com_sportsmanagement&tmpl=component&view=editperson&cid=<?php echo $this->person->id; ?>"
									 class="modal">
									<?php
									echo JHtml::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT'),'title= "' .
													JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT').'"');
									?>
								</a>
                <?PHP
                
  }

	if ( isset($this->teamPlayer->injury) && $this->teamPlayer->injury )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_INJURED' );
		echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}

	if ( isset($this->teamPlayer->suspension) && $this->teamPlayer->suspension )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_SUSPENDED' );
		echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}


	if ( isset($this->teamPlayer->away) && $this->teamPlayer->away )
	{
		$imageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PERSON_AWAY' );
		echo "&nbsp;&nbsp;" . JHtml::image(	'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
							$imageTitle,
							array( 'title' => $imageTitle ) );
	}
			?>
		</td>
	</tr>
</table>