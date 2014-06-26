<?php defined('_JEXEC') or die('Restricted access');
?>

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_CLUB_DETAILS' );?>
			</legend>
			<table class="admintable" border='0'>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('name'); ?></td>
					<td><?php echo $this->form->getInput('name'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('alias'); ?></td>
					<td><?php echo $this->form->getInput('alias'); ?></td>
				</tr>
				<?php if (!$this->edit): ?>
					<tr>
						<td class="key"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_CLUB_CREATE_TEAM')?></td>
						<td><input type="checkbox" name="createTeam" /></td>
					</tr>
				<?php endif; ?>				
				<tr>
					<td class="key"><?php echo $this->form->getLabel('admin'); ?></td>
					<td><?php echo $this->form->getInput('admin'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('address'); ?></td>
					<td><?php echo $this->form->getInput('address'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('zipcode'); ?></td>
					<td><?php echo $this->form->getInput('zipcode'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('location'); ?></td>
					<td><?php echo $this->form->getInput('location'); ?></td>
				</tr>
        <tr>
			<td class="key"><?php echo $this->form->getLabel('associations'); ?></td>
			<td><?php echo $this->form->getInput('associations'); ?></td>
		</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('state'); ?></td>
					<td><?php echo $this->form->getInput('state'); ?></td>
				</tr>		
				<tr>
					<td class="key"><?php echo $this->form->getLabel('country'); ?></td>
					<td><?php echo $this->form->getInput('country'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('phone'); ?></td>
					<td><?php echo $this->form->getInput('phone'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('fax'); ?></td>
					<td><?php echo $this->form->getInput('fax'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('email'); ?></td>
					<td><?php echo $this->form->getInput('email'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('website'); ?></td>
					<td><?php echo $this->form->getInput('website'); ?></td>
				</tr>	
				<tr>
					<td class="key"><?php echo $this->form->getLabel('manager'); ?></td>
					<td><?php echo $this->form->getInput('manager'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('president'); ?></td>
					<td><?php echo $this->form->getInput('president'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('founded'); ?></td>
					<td><?php echo $this->form->getInput('founded'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('dissolved'); ?></td>
					<td><?php echo $this->form->getInput('dissolved'); ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('standard_playground'); ?></td>
					<td><?php echo $this->form->getInput('standard_playground'); ?></td>
				</tr>
				
                <?PHP
                if ( $this->cfg_be_show_merge_teams )
                {
                ?>
				<tr>
					<td class="key"><?php echo $this->form->getLabel('merge_teams'); ?></td>
					<td><?php echo $this->form->getInput('merge_teams'); ?></td>
				</tr>
                <?PHP
                }
                ?>
                
                <tr>
					<td class="key"><?php echo $this->form->getLabel('enable_sb'); ?></td>
					<td><?php echo $this->form->getInput('enable_sb'); ?></td>
				</tr>
                <tr>
					<td class="key"><?php echo $this->form->getLabel('sb_catid'); ?></td>
					<td><?php echo $this->form->getInput('sb_catid'); ?></td>
				</tr>
        
        <tr>
					<td class="key"><?php echo $this->form->getLabel('dissolved_year'); ?></td>
					<td><?php echo $this->form->getInput('dissolved_year'); ?></td>
				</tr>	
        <tr>
					<td class="key"><?php echo $this->form->getLabel('founded_year'); ?></td>
					<td><?php echo $this->form->getInput('founded_year'); ?></td>
				</tr>	
        <tr>
					<td class="key"><?php echo $this->form->getLabel('unique_id'); ?></td>
					<td><?php echo $this->form->getInput('unique_id'); ?></td>
				</tr>	
        <tr>
					<td class="key"><?php echo $this->form->getLabel('new_club_id'); ?></td>
					<td><?php echo $this->form->getInput('new_club_id'); ?></td>
				</tr>	
  					
			</table>
		</fieldset>
       