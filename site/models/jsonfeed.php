<?php
/**
 * SportsManagement legacy compatibility bridge for the native Jsonfeed model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel;

if (!class_exists(JsonfeedModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Service/GoogleCalendarReadService.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/JsonfeedModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(JsonfeedModel::class)) {
    throw new \RuntimeException('SportsManagement native Jsonfeed model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelJSONFeed', false)) {
    class_alias(JsonfeedModel::class, 'sportsmanagementModelJSONFeed');
}
