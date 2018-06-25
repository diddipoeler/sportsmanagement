<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      pressebericht.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage match
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');JHtml::_('behavior.modal');
?>
<div id="editcell">
	<form enctype='multipart/form-data' action='<?php echo $this->request_url; ?>' method='post' id='adminForm' name='adminForm'>
		<table class='adminlist'>
			<thead><tr><th><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PRESSEBERICHT_IMPORT_TABLE_TITLE_1', $this->config->get('upload_maxsize') ); ?></th></tr></thead>
			<tfoot><tr><td><?php
				echo '<p>';
				echo '<b>'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_PRESSEBERICHT_IMPORT_EXTENSION_INFO').'</b>';
				echo '</p>';
				echo '<p>';
				echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PRESSEBERICHT_IMPORT_HINT1').'<br>';
				echo '</p>';
				?></td></tr></tfoot>
			<tbody><tr><td><fieldset style='text-align: center; '>
				<input class='input_box' id='import_package' name='import_package' type='file' size='57' />
				<input class='button' type='submit' value='<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PRESSEBERICHT_IMPORT_UPLOAD_BUTTON'); ?>' />
				</fieldset></td></tr></tbody>
		</table>
		<input type='hidden' name='sent' value='1' />
		<input type='hidden' name='MAX_FILE_SIZE' value='<?php echo $this->config->get('upload_maxsize'); ?>' />
        <input type='hidden' name='match_id' value='<?php echo $this->match_id; ?>' />
		<input type='hidden' name='task' value='match.savepressebericht' />
		<?php echo JHtml::_('form.token')."\n"; ?>
	</form>
</div>