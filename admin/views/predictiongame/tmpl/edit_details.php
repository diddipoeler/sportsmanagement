<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die('Restricted access');
?>


		<fieldset class="adminform">
			<legend><?php echo JText::sprintf('Predictiongame [%1$s]','<i>' .$this->prediction->name .'</i>'); ?></legend>
		<fieldset class="adminform">			
			<?php echo JText::_('JL_ADMIN_PGAME_HINT_1'); ?>
		</fieldset>			
			<table class="admintable">
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
						<label for="name"><?php echo JText::_('Prediction Name'); ?></label>
					</td>
					<td>
						<input class="text_area" type="text" name="name" id="title" size="80" maxlength="75" value="<?php echo $this->prediction->name; ?>" />
					</td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
						<label for="admins">
							<?php
							echo JText::_('Administrator(s)');
							echo '<br />';
							echo JText::sprintf('%1$s admin(s) assigned',count($this->pred_admins));
							?>
						</label>
					</td>
					<td><?php echo $this->lists['jl_users']; ?></td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for="league">
							<?php
							echo JText::_('Assigned project(s)');
							echo '<br />';
							echo JText::sprintf('%1$s project(s) assigned',count($this->pred_projects));
							?>
						</label>
					</td>
					<td><?php echo $this->lists['projects']; ?></td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for="auto_activate_user"><?php echo JText::_('Automatically activate user?'); ?></label>
					</td>
					<td><?php echo $this->lists['auto_activate_user']; ?></td>
				</tr>
				<tr>
					<td nowrap='nowrap' class='key' style='text-align:right;'>
		 				<label for="auto_activate_user"><?php echo JText::_('Tipp only favourite teams?'); ?></label>
					</td>
					<td><?php echo $this->lists['only_favteams']; ?></td>
				</tr>
				<tr>
					<td class='key' style='text-align:right;'>
		 				<label for="auto_activate_user"><?php echo JText::_('Allow Tipp-Admins to enter predictions for other users?'); ?></label>
					</td>
					<td><?php echo $this->lists['admin_tipp']; ?></td>
				</tr>
			</table>
		</fieldset>