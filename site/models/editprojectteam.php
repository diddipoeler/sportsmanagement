<?php
/**
 * Legacy compatibility bridge for the native frontend project-team editor.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditprojectteamModel;

if (!class_exists(EditprojectteamModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditprojectteamModel.php';
}

if (!class_exists('sportsmanagementModelEditprojectteam', false)) {
    class_alias(EditprojectteamModel::class, 'sportsmanagementModelEditprojectteam');
}
