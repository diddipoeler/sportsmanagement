<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Playground model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundModel;

if (!class_exists(PlaygroundModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlaygroundModel.php';
}

if (!class_exists('sportsmanagementModelPlayground', false)) {
    class_alias(PlaygroundModel::class, 'sportsmanagementModelPlayground');
}
