<?php
defined('JPATH_BASE') or die;
use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_('behavior.core');
?>

<button data-toggle="modal" onclick="jQuery( '#collapseModal' ).modal('show');" class="btn btn-small">
	<span class="icon-checkbox-partial" aria-hidden="true"></span>
	<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE'); ?>
</button>
