<?php defined('_JEXEC') or die('Restricted access');
?>
		<fieldset class="adminform">
			<legend>
				<?php
				echo JText::_( 'COM_JOOMLEAGUE_ADMIN_STAT_STAT' );
				?>
			</legend>
			<table class="admintable">
				<tr>
					<td class="key"><?php echo $this->form->getField('name')->label; ?></td>
					<td><?php echo $this->form->getField('name')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('sports_type_id')->label; ?></td>
					<td><?php echo $this->form->getField('sports_type_id')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('short')->label; ?></td>
					<td><?php echo $this->form->getField('short')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('alias')->label; ?></td>
					<td><?php echo $this->form->getField('alias')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('class')->label; ?></td>
					<td><?php echo $this->form->getField('class')->input; ?></td>
				</tr>
				<tr>
					<td class="key"><?php echo $this->form->getField('published')->label; ?></td>
					<td><?php echo $this->form->getField('published')->input; ?></td>
				</tr>
				<tr>
		  			<td class="key">
		   				<label for="note">
			  				<?php
			  				echo JText::_( 'COM_JOOMLEAGUE_ADMIN_STAT_NOTE' );
			  				?>
						</label>
		  			</td>
		  			<td>
							<input type="text" id="note" name="note" value="<?php echo $this->item->note; ?>" size="100"/>
		  			</td>
				</tr>
	    			
			</table>
		</fieldset>