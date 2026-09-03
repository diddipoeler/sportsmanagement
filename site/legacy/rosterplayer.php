<?php
/**
 * Legacy roster player model bridge for Joomla 5/6.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

// Keep the historical roster import path, but expose the same complete
// Joomla 5/6 Player facade used everywhere else.
require_once JPATH_SITE . '/components/com_sportsmanagement/models/player.php';
