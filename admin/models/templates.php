<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 templates list model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TemplatesModel;

if (!class_exists(TemplatesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TemplatesModel.php';
}

if (!class_exists(TemplatesModel::class)) {
    throw new \RuntimeException('SportsManagement native Templates model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTemplates', false)) {
    class_alias(TemplatesModel::class, 'sportsmanagementModelTemplates');
}
