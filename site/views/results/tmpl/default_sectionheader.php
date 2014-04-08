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
?>

<!-- section header e.g. ranking, results etc. -->
<a id="jl_top"></a>

<table class="contentpaneopen">
	<tr>
		<td class="contentheading">
		<?php
		if ( $this->roundid)
		{
			$title = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS' );
			if ( isset( $this->division ) )
			{
				$title = JText::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', '<i>' . $this->division->name . '</i>' );
			}
			sportsmanagementHelperHtml::showMatchdaysTitle(	$title, $this->roundid, $this->config );

			if ( $this->showediticon )
			{
				$link = sportsmanagementHelperRoute::getResultsRoute( $this->project->id, $this->roundid, $this->model->divisionid, $this->model->mode, $this->model->order, $this->config['result_style_edit'] );
				$imgTitle = JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS' );
				$desc = JHtml::image( 'media/com_sportsmanagement/jl_images/edit.png', $imgTitle, array( ' title' => $imgTitle ) );
				echo ' ';
				echo JHtml::link( $link, $desc );
			}
		}
		else
		{
			//1 request for current round
			// seems to be this shall show a plan of matches of a team???
			sportsmanagementHelperHtml::showMatchdaysTitle( JText::_( 'COM_SPORTSMANAGEMENT_RESULTS_PLAN' ) , 0, $this->config );
		}
		?>
		</td>
			<?php if ( $this->config['show_matchday_dropdown'] == 1 ) 
            { 
                ?>
            <form name='resultsRoundSelector' method='post'>
		<input type='hidden' name='option' value='com_sportsmanagement' />
        <td>
        <?php
		//echo JHtml::image(	'images/com_sportsmanagement/database/jl_images/arrow_left_small.png',$imgtitle, 'title= "' . $imgtitle . '"' );
        $imgtitle = JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_PREV' );
        echo JHtml::link(sportsmanagementModelPagination::$prevlink,JHtml::image(	'images/com_sportsmanagement/database/jl_images/arrow_left_small.png',$imgtitle, 'title= "' . $imgtitle . '"' ));
        //echo sportsmanagementModelPagination::$prevlink;
		?>
        </td>
	            <td class="contentheading" style="text-align:right; font-size: 100%;">
			<?php echo sportsmanagementHelperHtml::getRoundSelectNavigation(FALSE); ?>
				</td>
                <td>
        <?php
		//echo JHtml::image(	'images/com_sportsmanagement/database/jl_images/arrow_right_small.png',$imgtitle, 'title= "' . $imgtitle . '"' );
        $imgtitle = JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_NEXT' );
        echo JHtml::link(sportsmanagementModelPagination::$nextlink,JHtml::image(	'images/com_sportsmanagement/database/jl_images/arrow_right_small.png',$imgtitle, 'title= "' . $imgtitle . '"' ));
        //echo sportsmanagementModelPagination::$nextlink;
		?>
        </td>
                </form>
    	    <?php 
            } 
            ?>
		</tr>
</table>