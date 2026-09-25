<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 project referees list model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectrefereesModel;

if (!class_exists(ProjectrefereesModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectrefereesModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(ProjectrefereesModel::class)) {
    throw new \RuntimeException('SportsManagement native Projectreferees model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProjectReferees', false)) {
    class_alias(ProjectrefereesModel::class, 'sportsmanagementModelProjectReferees');
}
