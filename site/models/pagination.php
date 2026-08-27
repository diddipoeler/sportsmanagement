<?php
/**
 * Legacy compatibility bridge for SportsManagement pagination.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Pagination\JSMSportsmanagementPagination as NativePagination;

if (!class_exists(NativePagination::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Pagination/JSMSportsmanagementPagination.php';
}

if (!class_exists('sportsmanagement\\Site\\Model\\JSMSportsmanagementPagination', false)) {
    class_alias(NativePagination::class, 'sportsmanagement\\Site\\Model\\JSMSportsmanagementPagination');
}
