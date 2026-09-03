<?php
/**
 * SportsManagement legacy compatibility bridge for the native Jsonfeed model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel;

if (!class_exists(JsonfeedModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/JsonfeedModel.php';
}

if (!class_exists('sportsmanagementModelJSONFeed', false)) {
    class_alias(JsonfeedModel::class, 'sportsmanagementModelJSONFeed');
}
