<?php
/**
 * Legacy roster player model bridge for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

// Keep the historical roster import path, but expose the same complete
// Joomla 5/6 Player facade used everywhere else. Fail explicitly when the
// compatibility facade is unavailable instead of emitting a PHP include warning.
$legacyPlayerModel = JPATH_SITE . '/components/com_sportsmanagement/models/player.php';

if (is_file($legacyPlayerModel)) {
    require_once $legacyPlayerModel;
}

if (!class_exists('sportsmanagementModelPlayer')) {
    throw new \RuntimeException('SportsManagement legacy Player model facade could not be loaded.', 500);
}
