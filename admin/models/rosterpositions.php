<?php
/**
 * SportsManagement legacy compatibility bridge for the native Joomla 5/6 roster-positions list model.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/RosterpositionsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\RosterpositionsModel;

if (!class_exists(RosterpositionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RosterpositionsModel.php';
}

if (!class_exists('sportsmanagementModelrosterpositions', false)) {
    class_alias(RosterpositionsModel::class, 'sportsmanagementModelrosterpositions');
}
