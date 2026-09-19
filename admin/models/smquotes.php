<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator quotes list model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotesModel;

if (!class_exists(SmquotesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmquotesModel.php';
}

if (!class_exists(SmquotesModel::class)) {
    throw new \RuntimeException('SportsManagement native administrator quotes list model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsmquotes', false)) {
    class_alias(SmquotesModel::class, 'sportsmanagementModelsmquotes');
}
