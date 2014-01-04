<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- START of match events -->

<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'); ?></h2>		

<table class="matchreport" border="0">
			<?php
			foreach ( $this->eventtypes as $event )
			{
				?>
				<tr>
					<td colspan="2" class="eventid">
						<?php echo JHTML::_( 'image', $event->icon, JText::_($event->icon ), NULL ) . JText::_($event->name); ?>
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
