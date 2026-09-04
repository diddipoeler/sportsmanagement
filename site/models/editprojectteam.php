<?php
/**
 * Legacy compatibility bridge for the native frontend project-team editor.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditprojectteamModel;

if (!class_exists(EditprojectteamModel::class)) {
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditprojectteamModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(EditprojectteamModel::class)) {
    throw new \RuntimeException('SportsManagement native Editprojectteam model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelEditprojectteam', false)) {
    class_alias(EditprojectteamModel::class, 'sportsmanagementModelEditprojectteam');
}
