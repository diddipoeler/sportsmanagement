<?php
/**
 * Legacy compatibility bridge for matchday page navigation.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\RoundPaginationHelper;

if (!class_exists(RoundPaginationHelper::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Helper/RoundPaginationHelper.php';
}

if (!class_exists('sportsmanagementModelPagination', false)) {
    class_alias(RoundPaginationHelper::class, 'sportsmanagementModelPagination');
}
