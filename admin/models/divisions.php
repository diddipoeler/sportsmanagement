<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Divisions model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\DivisionsModel;

if (!class_exists(DivisionsModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/DivisionsModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(DivisionsModel::class)) {
    throw new \RuntimeException('SportsManagement native Divisions model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelDivisions', false)) {
    class_alias(DivisionsModel::class, 'sportsmanagementModelDivisions');
}
