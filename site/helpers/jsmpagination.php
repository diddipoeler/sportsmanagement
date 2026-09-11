<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 SportsManagement pagination adapter.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @deprecated 5.6 Use Diddipoeler\Component\SportsManagement\Site\Pagination\JSMSportsmanagementPagination
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Pagination\JSMSportsmanagementPagination;

if (!class_exists(JSMSportsmanagementPagination::class)) {
    $nativeFile = JPATH_SITE . '/components/com_sportsmanagement/src/Pagination/JSMSportsmanagementPagination.php';

    if (is_file($nativeFile)) {
        require_once $nativeFile;
    }
}

if (!class_exists(JSMSportsmanagementPagination::class)) {
    throw new \RuntimeException('SportsManagement native pagination adapter could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelJSMPagination', false)) {
    class_alias(JSMSportsmanagementPagination::class, 'sportsmanagementModelJSMPagination');
}
