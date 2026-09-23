<?php
/**
 * Joomla 5/6 toolbar trigger for the project-team assignment modal.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
?>
<button
    type="button"
    class="btn btn-sm btn-outline-secondary"
    data-bs-toggle="modal"
    data-bs-target="#collapseModalassignTeams"
>
    <span class="icon-users" aria-hidden="true"></span>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_BUTTON_ASSIGN'); ?>
</button>
