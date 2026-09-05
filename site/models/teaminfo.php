<?php
/**
 * SportsManagement legacy compatibility bridge for the native Teaminfo model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeaminfoModel;

if (!class_exists(TeaminfoModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/TeaminfoModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(TeaminfoModel::class)) {
    throw new \RuntimeException('SportsManagement native Teaminfo model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTeamInfo', false)) {
    class_alias(TeaminfoModel::class, 'sportsmanagementModelTeamInfo');
}
