<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
if ( $this->config['show_sectionheader'] == 1 )
{
	?>
	<table width="100%" class="contentpaneopen">
		<tr>
			<td class="contentheading">
				<?php
				if ( $this->roundid > 0 )
				{
					$title = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS' );
					if ( isset( $this->division ) )
					{
						$title = JText::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', '<i>' . $this->division->name . '</i>' );
					}

					sportsmanagementHelperHtml::showMatchdaysTitle(	$title, $this->roundid, $this->config );

					if ( $this->showediticon )
						{
							$link = sportsmanagementHelperRoute::getResultsRoute( $this->project->id, $this->roundid);
							$imgTitle = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS' );
							$desc = JHTML::image( 'media/com_sportsmanagement/jl_images/edit.png', $imgTitle, array( ' title' => $imgTitle ) );
							echo ' ';
							echo JHTML::link( $link, $desc );
						}

				}
				else
				{
					//1 request for current round
					// seems to be this shall show a plan of matches of a team???
					sportsmanagementHelperHtml::showMatchdaysTitle( JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_PLAN' ) . " - " . $team->name, 0, $this->config );
				}
				?>
			</td>
		</tr>
	</table>
	<?php
}
?>