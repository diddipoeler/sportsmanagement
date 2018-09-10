<?php
defined('JPATH_BASE') or die;
JHtml::_('behavior.core');
?>

<button data-toggle="modal" onclick="jQuery( '#collapseModalchangeTeams' ).modal('show');" class="btn btn-small">
	<span class="icon-checkbox-partial" aria-hidden="true"></span>
	<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'); ?>
</button>
