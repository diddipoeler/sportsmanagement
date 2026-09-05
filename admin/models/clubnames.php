<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Clubnames model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubnamesModel;

if (!class_exists(ClubnamesModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ClubnamesModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(ClubnamesModel::class)) {
    throw new \RuntimeException('SportsManagement native Clubnames model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelclubnames', false)) {
    class_alias(ClubnamesModel::class, 'sportsmanagementModelclubnames');
}
