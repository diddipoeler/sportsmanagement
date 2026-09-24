<?php
/**
 * Legacy compatibility bridge for the native frontend Editteam model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel;

if (!class_exists(EditteamModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditteamModel.php';
}

if (!class_exists('sportsmanagementModelEditteam', false)) {
    class_alias(EditteamModel::class, 'sportsmanagementModelEditteam');
}
