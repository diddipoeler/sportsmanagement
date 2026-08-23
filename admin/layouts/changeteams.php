<?php
/** Joomla 5/6 toolbar trigger for the project-team replacement modal. */
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
?>
<button
    type="button"
    class="btn btn-sm btn-outline-secondary"
    data-bs-toggle="modal"
    data-bs-target="#collapseModalchangeTeams"
>
    <span class="icon-shuffle" aria-hidden="true"></span>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_CHANGE_TEAMS'); ?>
</button>
