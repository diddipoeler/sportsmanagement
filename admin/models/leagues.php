<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Leagues model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\LeaguesModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
    LeaguesModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/LeaguesModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(LeaguesModel::class)) {
    throw new \RuntimeException('SportsManagement native Leagues model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelLeagues', false)) {
    class_alias(LeaguesModel::class, 'sportsmanagementModelLeagues');
}
