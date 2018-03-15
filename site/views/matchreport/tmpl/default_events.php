<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_events.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<!-- START of match events -->

<h2>
<?php 
echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'); 
?>
</h2>		

<?php
if ( $this->config['show_timeline'] && !$this->config['show_timeline_under_results'] )
{
echo $this->loadTemplate('timeline');
}
?>
<table class="table table-responsive" >
			<?php
			foreach ( $this->eventtypes as $event )
			{
				?>
				<tr>
					<td colspan="2" class="eventid">
						<?php echo JHtml::_( 'image', $event->icon, JText::_($event->icon ), NULL ) . JText::_($event->name); ?>
					</td>
				</tr>
				<tr>
					<td class="list">
						<dl>
							<?php echo $this->showEvents( $event->id, $this->match->projectteam1_id ); ?>
						</dl>
					</td>
					<td class="list">
						<dl>
							<?php echo $this->showEvents( $event->id, $this->match->projectteam2_id ); ?>
						</dl>
					</td>
				</tr>
				<?php
			}
			?>
</table>
<!-- END of match events -->
<br />
