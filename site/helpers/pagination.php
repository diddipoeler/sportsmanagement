<?php
/**
 * Legacy compatibility bridge for matchday page navigation.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\RoundPaginationHelper;

if (!class_exists(RoundPaginationHelper::class)) {
    $nativeHelper = JPATH_SITE . '/components/com_sportsmanagement/src/Helper/RoundPaginationHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(RoundPaginationHelper::class)) {
    throw new \RuntimeException('SportsManagement native round pagination helper could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPagination', false)) {
    class_alias(RoundPaginationHelper::class, 'sportsmanagementModelPagination');
}
