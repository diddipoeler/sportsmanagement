<?php
/** Legacy compatibility bridge for the native frontend project-team editor. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditprojectteamModel;

if (!class_exists(EditprojectteamModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditprojectteamModel.php';
}

if (!class_exists('sportsmanagementModelEditprojectteam', false)) {
    class_alias(EditprojectteamModel::class, 'sportsmanagementModelEditprojectteam');
}
