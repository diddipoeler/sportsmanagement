<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage layouts
 * @file       rosterpositions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
?>
<button
    type="button"
    class="btn btn-sm btn-outline-secondary"
    data-bs-toggle="modal"
    data-bs-target="#rosterpositions"
>
    <span class="icon-checkbox-partial" aria-hidden="true"></span>
    <?php echo Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE'); ?>
</button>
