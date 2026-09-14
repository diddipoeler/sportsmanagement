<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 roster-position model.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/RosterpositionModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\RosterpositionModel;

if (!class_exists(RosterpositionModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RosterpositionModel.php';
}

if (!class_exists('sportsmanagementModelrosterposition', false)) {
    class_alias(RosterpositionModel::class, 'sportsmanagementModelrosterposition');
}
