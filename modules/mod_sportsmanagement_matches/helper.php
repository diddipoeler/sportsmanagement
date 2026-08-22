<?php
/** Legacy helper bridge for the Joomla 5/6 SportsManagement matches module. */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementMatches\Site\Helper\MatchesHelper;

if (!class_exists(MatchesHelper::class)) {
    require_once __DIR__ . '/src/Helper/MatchesHelper.php';
}

if (!class_exists('modMatchesSportsmanagementHelper', false)) {
    class_alias(MatchesHelper::class, 'modMatchesSportsmanagementHelper');
}
