<?php defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div>
	<form action="index.php" method="post" id="adminForm">
		<div id="editcell">
			<fieldset class="adminform">
				<legend>
					<?php
					echo JText::_( 'COM_JOOMLEAGUE_ADMIN_PERSON_ASSIGN_DESCR2' );
					?>
				</legend>
				<table class="adminform">
					<tr>
						<td>
							<?php
							echo $this->lists['projects'];
							?>
						</td>
					</tr>
						<?php
						if ( $this->project_id )
						{
							?>
							<tr>
								<td>
								<?php
								echo $this->lists['projectteams'];
								?>
								</td>
							</tr>
							<tr>
								<td>
									<div class="button" style="text-align:left">
										<input	type="button" class="inputbox"
												onclick="window.top.document.forms.adminForm.elements.project_id.value = document.getElementById('prjid').value; window.top.document.forms.adminForm.elements.team_id.value = document.getElementById('xtid').value; window.top.document.forms.adminForm.elements.assignperson.value ='1'; window.parent.SqueezeBox.close();"
												value="<?php echo JText::_( 'COM_JOOMLEAGUE_ADMIN_PERSON_ASSIGN' ); ?>" />
									</div>
								</td>
							</tr>
							<?php
						}
						?>
				</table>
			</fieldset>
		</div>
		<div style="clear"></div>
		<input type="hidden" name="option" value="com_joomleague" />
		<input type="hidden" name="view" value="person" />
		<input type="hidden" name="task" value="person.personassign" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>