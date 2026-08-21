<?php
/**
 * SportsManagement Joomla 5/6 legacy plugin bridge.
 *
 * The active implementation is loaded through services/provider.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Plugin\System\SportsmanagementIshupdate\Extension\SportsmanagementIshupdate;

if (!class_exists(SportsmanagementIshupdate::class)) {
    require_once __DIR__ . '/src/Extension/SportsmanagementIshupdate.php';
}

if (!class_exists('PlgSystemjsm_ishupdate', false)) {
    class_alias(SportsmanagementIshupdate::class, 'PlgSystemjsm_ishupdate');
}
