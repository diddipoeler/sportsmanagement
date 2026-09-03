<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Cpanel model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\CpanelModel;

if (!class_exists(CpanelModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/CpanelModel.php';
}

if (!class_exists('sportsmanagementModelcpanel', false)) {
    class_alias(CpanelModel::class, 'sportsmanagementModelcpanel');
}
