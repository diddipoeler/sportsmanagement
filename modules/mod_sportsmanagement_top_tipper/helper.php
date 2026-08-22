<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 Top Tipper module.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementTopTipper\Site\Helper\TopTipperHelper;

if (!class_exists(TopTipperHelper::class)) {
    require_once __DIR__ . '/src/Helper/TopTipperHelper.php';
}

if (!class_exists('modJSMTopTipper', false)) {
    class_alias(TopTipperHelper::class, 'modJSMTopTipper');
}
