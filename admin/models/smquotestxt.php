<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator quote text-files model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotestxtModel;

if (!class_exists(SmquotestxtModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmquotestxtModel.php';
}

if (!class_exists(SmquotestxtModel::class)) {
    throw new \RuntimeException('SportsManagement native Smquotestxt model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsmquotestxt', false)) {
    class_alias(SmquotestxtModel::class, 'sportsmanagementModelsmquotestxt');
}
