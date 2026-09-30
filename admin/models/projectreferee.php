<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 project referee form model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectrefereeModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
    ProjectrefereeModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectrefereeModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(ProjectrefereeModel::class)) {
    throw new \RuntimeException('SportsManagement native Projectreferee model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelprojectreferee', false)) {
    class_alias(ProjectrefereeModel::class, 'sportsmanagementModelprojectreferee');
}
