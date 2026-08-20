<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/EditmatchModel.php.
 */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;

if (!class_exists(EditmatchModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditmatchModel.php';
}

if (!class_exists('sportsmanagementModelEditMatch', false)) {
    class_alias(EditmatchModel::class, 'sportsmanagementModelEditMatch');
}
