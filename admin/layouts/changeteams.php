<?php
defined('JPATH_BASE') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
HTMLHelper::_('behavior.core');
?>

<button data-toggle="modal" onclick="jQuery( '#collapseModalchangeTeams' ).modal('show');" class="btn btn-small">
	<span class="icon-checkbox-partial" aria-hidden="true"></span>
	<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'); ?>
</button>
