<?php defined('_JEXEC') or die('Restricted access');

	?>
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_JOOMLEAGUE_USER_EXTRA_FIELDS'); ?></legend>
	<table>
    <?php
	for($p=0;$p<count($this->lists['ext_fields']);$p++)
            {
			?>
			<tr>
				<td width="100">
					<?php echo $this->lists['ext_fields'][$p]->name;?>
				</td>
				<td>
					<input type="text" maxlength="100" size="100" name="extraf[]" value="<?php echo isset($this->lists['ext_fields'][$p]->fvalue)?htmlspecialchars($this->lists['ext_fields'][$p]->fvalue):""?>" />
					<input type="hidden" name="extra_id[]" value="<?php echo $this->lists['ext_fields'][$p]->id?>" />
                    <input type="hidden" name="extra_value_id[]" value="<?php echo $this->lists['ext_fields'][$p]->value_id?>" />
				</td>
			</tr>
			<?php	
			}
	?>
    </table>
	</fieldset>
	<?php

?>