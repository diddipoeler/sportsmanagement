<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Editteam model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel;

if (!class_exists(EditteamModel::class)) {
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditteamModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(EditteamModel::class)) {
    throw new \RuntimeException('SportsManagement native Editteam model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelEditteam', false)) {
    class_alias(EditteamModel::class, 'sportsmanagementModelEditteam');
}
