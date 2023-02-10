<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage layouts
 * @file       newissue.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.core');
?>

<button data-toggle="modal" onclick="jQuery( '#newissue' ).modal('show');" class="btn btn-small">
    <span class="icon-checkbox-partial" aria-hidden="true"></span>
	<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE'); ?>
</button>
