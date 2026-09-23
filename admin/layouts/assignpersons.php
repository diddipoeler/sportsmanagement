<?php
/**
 * Joomla 5/6 administrator layout for assigning persons.
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
    class="btn btn-sm btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#collapseModalassignPersons"
>
    <span class="icon-upload" aria-hidden="true"></span>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN'); ?>
</button>
