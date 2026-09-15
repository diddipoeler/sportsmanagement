<?php
/**
 * Legacy compatibility bridge for SportsManagement pagination.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Pagination\JSMSportsmanagementPagination as NativePagination;

if (!class_exists(NativePagination::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Pagination/JSMSportsmanagementPagination.php';
}

if (!class_exists('sportsmanagement\\Site\\Model\\JSMSportsmanagementPagination', false)) {
    class_alias(NativePagination::class, 'sportsmanagement\\Site\\Model\\JSMSportsmanagementPagination');
}
