<?php defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_JOOMLEAGUEADMIN_ASSOCIATION_LEGEND'); ?>
	</legend>
	<table class="admintable">
		<tr>
			<td width="100" align="right" class="key"><label for="name"><?php echo JText::_('COM_SPORTSMANGEMENT_ADMIN_ASSOCIATION_NAME'); ?></label></td>
			<td>
				<input class="text_area" type="text" name="name" id="title" size="32" maxlength="250" value="<?php echo $this->object->name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key"><label for="middle_name"><?php echo JText::_('COM_SPORTSMANGEMENT_ADMIN_ASSOCIATION_MIDDLE_NAME'); ?></label></td>
			<td>
				<input class="text_area" type="text" name="middle_name" id="title" size="32" maxlength="25" value="<?php echo $this->object->middle_name; ?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key"><label for="short_name"><?php echo JText::_('COM_SPORTSMANGEMENT_ADMIN_ASSOCIATION_SHORT_NAME'); ?></label></td>
			<td>
				<input class="text_area" type="text" name="short_name" id="title" size="32" maxlength="15" value="<?php echo $this->object->short_name; ?>" />
			</td>
		</tr>		
		<tr>
			<td width="100" align="right" class="key"><label for="alias"><?php echo JText::_('COM_SPORTSMANGEMENT_ADMIN_ASSOCIATION_ALIAS'); ?></label></td>
			<td>
				<input class="text_area" type="text" name="alias" id="alias" size="32" maxlength="75" value="<?php echo $this->object->alias; ?>" />
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key"><label for="ordering"><?php echo JText::_('COM_SPORTSMANGEMENT_ADMIN_ASSOCIATION_COUNTRY'); ?></label></td>
			<td><?php echo $this->lists['countries']; ?>&nbsp;<?php echo Countries::getCountryFlag($this->object->country); ?>&nbsp;(<?php echo $this->object->country; ?>)</td>
		</tr>
		<tr>
					<td class="key"><?php echo $this->form->getLabel('website'); ?></td>
					<td><?php echo $this->form->getInput('website'); ?></td>
				</tr>
    <tr>
					<td class="key"><?php echo $this->form->getLabel('parent_id'); ?></td>
					<td><?php echo $this->form->getInput('parent_id'); ?></td>
				</tr>
    
    
		<tr>
			<td valign="top" align="right" class="key"><label for="ordering"><?php echo JText::_('COM_SPORTSMANGEMENT_ADMIN_ASSOCIATION_ORDERING'); ?></label></td>
			<td><?php echo $this->lists['ordering']; ?></td>
		</tr>
	</table>
</fieldset>