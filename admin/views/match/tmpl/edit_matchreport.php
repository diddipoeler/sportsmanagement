<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );
?>		
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MR'); ?>
	</legend>
	<table class="admintable">
		<?php 
                    
                    foreach ($this->form->getFieldset('matchreport') as $field):
                    ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
	</table>
</fieldset>		