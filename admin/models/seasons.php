<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Seasons model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
    SeasonsModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SeasonsModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(SeasonsModel::class)) {
    throw new \RuntimeException('SportsManagement native Seasons model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelSeasons', false)) {
    class_alias(SeasonsModel::class, 'sportsmanagementModelSeasons');
}
