<?php
/**
 * SportsManagement legacy compatibility bridge.
 * The active Joomla 5/6 implementation lives in site/src/Model/AllprojectroundsModel.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllprojectroundsModel;

if (!class_exists(AllprojectroundsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllprojectroundsModel.php';
}

if (!class_exists('sportsmanagementModelallprojectrounds', false)) {
    class_alias(AllprojectroundsModel::class, 'sportsmanagementModelallprojectrounds');
}
