<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllteamsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllteamsModel;

if (!class_exists(AllteamsModel::class)) {
    $nativeModels = [
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllteamsModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(AllteamsModel::class)) {
    throw new \RuntimeException('SportsManagement native Allteams model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelallteams', false)) {
    class_alias(AllteamsModel::class, 'sportsmanagementModelallteams');
}
