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
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
    ProjectrefereesModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectrefereesModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(ProjectrefereesModel::class)) {
    throw new \RuntimeException('SportsManagement native Projectreferees model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProjectReferees', false)) {
    class_alias(ProjectrefereesModel::class, 'sportsmanagementModelProjectReferees');
}
