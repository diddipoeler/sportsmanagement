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
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/SportsManagementDatabaseResolver.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/TeamTable.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditteamModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(EditteamModel::class)) {
    throw new \RuntimeException('SportsManagement native Editteam model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelEditteam', false)) {
    class_alias(EditteamModel::class, 'sportsmanagementModelEditteam');
}
