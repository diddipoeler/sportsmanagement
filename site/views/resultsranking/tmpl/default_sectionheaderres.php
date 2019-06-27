<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_sectionheaderres.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage resultsranking
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if ( $this->config['show_sectionheader'] )
{
	?>
	<table class="table" id="resultsranking_sectionheaderresults">
		<tr>
			<td class="contentheading">
				<?php
				if ( $this->roundid > 0 )
				{
					$title = Text::_( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS' );
					if ( isset( $this->division ) )
					{
						$title = Text::sprintf( 'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', '<i>' . $this->division->name . '</i>' );
					}

					sportsmanagementHelperHtml::showMatchdaysTitle(	$title, $this->roundid, $this->config );

					if ( $this->showediticon )
						{
							$link = sportsmanagementHelperRoute::getResultsRoute( $this->project->id, $this->roundid );
							$imgTitle = Text::_( 'COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS' );
							$desc = HTMLHelper::image( 'media/com_sportsmanagement/jl_images/edit.png', $imgTitle, array( ' title' => $imgTitle ) );
							echo ' ';
							echo HTMLHelper::link( $link, $desc );
						}
				}
				else
				{
					//1 request for current round
					// seems to be this shall show a plan of matches of a team???
					sportsmanagementHelperHtml::showMatchdaysTitle( Text::_( 'COM_SPORTSMANAGEMENT_RESULTS_PLAN' ) . " - " . $team->name, 0, $this->config );
				}
				?>
			</td>
		</tr>
	</table>
	<?php
}
?>