<?php 

defined( '_JEXEC' ) or die( 'Restricted access' );

?>


	

			<legend>
				<?php
				echo JText::sprintf( 'COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_TITLE', '<i>' . $this->projectws->name . '</i>');
				?>
			</legend>
			<table class="table" border="0">
				<tr>
					<td>
						<b>
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_AVAIL_MATCHES' );
							?>
						</b><br />
						<?php
						echo $this->lists['matches'];
						?>
					</td>
					<td style="">
						
<input id="moveright" type="button" value="<?php echo JText::_('&gt;&gt;'); ?>" onclick="move_list_items('matcheslist','node_matcheslist');" />                        
<input id="moveleft" type="button" value="<?php echo JText::_('&lt;&lt;'); ?>" onclick="move_list_items('node_matcheslist','matcheslist');" />                        
                        <!--
                        &nbsp;&nbsp;
						<input	type="button" class="inputbox"
								onclick="handleLeftToRight();"
								value="&gt;&gt;" />
						&nbsp;&nbsp;<br />&nbsp;&nbsp;
					 	<input	type="button" class="inputbox"
					 			onclick="handleRightToLeft();"
								value="&lt;&lt;" />
						&nbsp;&nbsp;
                        -->
					</td>
					<td>
						<b>
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_NODE_MATCHES' );
							?>
						</b><br />
						<?php
						echo $this->lists['node_matches'];
						?>
					</td>
			   </tr>
			</table>

		