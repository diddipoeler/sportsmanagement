<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_sectionheaderres.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage resultsranking
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<?php
if ( $this->config['show_sectionheader'] == 1 )
{
	?>
	<table width="" class="table" id="">
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
							$link = sportsmanagementHelperRoute::getResultsRoute( $this->project->id, $this->roundid );
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