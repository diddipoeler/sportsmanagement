<?php
/**
 * Joomla 5/6 toolbar trigger for creating a GitHub issue.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage layouts
 * @file       newissue.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
?>

<button
    type="button"
    class="btn btn-sm btn-outline-secondary"
    data-bs-toggle="modal"
    data-bs-target="#newissue"
>
    <span class="icon-checkbox-partial" aria-hidden="true"></span>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE'); ?>
</button>
