<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator quote form model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmquoteModel;

if (!class_exists(SmquoteModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmquoteModel.php';
}

if (!class_exists(SmquoteModel::class)) {
    throw new \RuntimeException('SportsManagement native administrator quote form model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsmquote', false)) {
    class_alias(SmquoteModel::class, 'sportsmanagementModelsmquote');
}
