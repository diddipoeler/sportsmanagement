<?php
/**
 * Legacy compatibility bridge for the native administrator Projectpositions list model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectpositionsModel;

if (!class_exists(ProjectpositionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectpositionsModel.php';
}

if (!class_exists('sportsmanagementModelProjectpositions', false)) {
    class_alias(ProjectpositionsModel::class, 'sportsmanagementModelProjectpositions');
}
