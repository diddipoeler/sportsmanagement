<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/EditclubModel.php.
 */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditclubModel;

if (!class_exists(EditclubModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditclubModel.php';
}

if (!class_exists('sportsmanagementModelEditClub', false)) {
    class_alias(EditclubModel::class, 'sportsmanagementModelEditClub');
}
