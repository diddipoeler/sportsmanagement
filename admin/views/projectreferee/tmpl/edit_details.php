<?php defined('_JEXEC') or die('Restricted access');
?>		
		
		<fieldset class="adminform">
			
			<table class="admintable">
				<tr>
					<td class="key">
						<label for="position">
							<?php
								echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_REF_DETAILS_STAND_REF_POS');
							?>
						</label>
					</td>
					<td colspan="9">
						<?php
						echo $this->lists['refereepositions'];
						?>
					</td>
				</tr>
			</table>
		</fieldset>