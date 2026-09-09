<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AboutModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AboutModel;

if (!class_exists(AboutModel::class)) {
    $nativeModels = [
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/AboutModel.php',
    ];

    foreach ($nativeModels as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(AboutModel::class)) {
    throw new \RuntimeException('SportsManagement native About model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelAbout', false)) {
    class_alias(AboutModel::class, 'sportsmanagementModelAbout');
}
