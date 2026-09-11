<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 SportsManagement quickicon helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementQuickIcon\Administrator\Helper\QuickIconHelper;

if (!class_exists(QuickIconHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/QuickIconHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(QuickIconHelper::class)) {
    throw new \RuntimeException('SportsManagement native Quickicon module helper could not be loaded.', 500);
}

if (!class_exists('ModSportsmanagementQuickIconHelper', false)) {
    class_alias(QuickIconHelper::class, 'ModSportsmanagementQuickIconHelper');
}
