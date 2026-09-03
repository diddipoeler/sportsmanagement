<?php
/**
 * Legacy compatibility bridge for matchday page navigation.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\RoundPaginationHelper;

if (!class_exists(RoundPaginationHelper::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Helper/RoundPaginationHelper.php';
}

if (!class_exists('sportsmanagementModelPagination', false)) {
    class_alias(RoundPaginationHelper::class, 'sportsmanagementModelPagination');
}
