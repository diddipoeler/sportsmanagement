<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 matches module helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementMatches\Site\Helper\MatchesHelper;

if (!class_exists(MatchesHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/MatchesHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(MatchesHelper::class)) {
    throw new \RuntimeException('SportsManagement native Matches module helper could not be loaded.', 500);
}

if (!class_exists('modMatchesSportsmanagementHelper', false)) {
    class_alias(MatchesHelper::class, 'modMatchesSportsmanagementHelper');
}
