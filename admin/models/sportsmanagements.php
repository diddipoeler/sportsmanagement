<?php
/**
 * Legacy compatibility bridge for the native administrator Sportsmanagements list model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsmanagementsModel;

if (!class_exists(SportsmanagementsModel::class)) {
    foreach ([
        __DIR__ . '/../src/Model/SportsManagementListModel.php',
        __DIR__ . '/../src/Model/SportsmanagementsModel.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(SportsmanagementsModel::class)) {
    throw new \RuntimeException('SportsManagement native administrator list model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsportsmanagements', false)) {
    class_alias(SportsmanagementsModel::class, 'sportsmanagementModelsportsmanagements');
}
