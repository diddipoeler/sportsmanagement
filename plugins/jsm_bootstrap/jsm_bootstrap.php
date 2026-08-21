<?php
/**
 * SportsManagement Joomla 5/6 legacy plugin bridge.
 *
 * The active implementation is loaded through services/provider.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Plugin\System\SportsmanagementBootstrap\Extension\SportsmanagementBootstrap;

if (!class_exists(SportsmanagementBootstrap::class)) {
    require_once __DIR__ . '/src/Extension/SportsmanagementBootstrap.php';
}

if (!class_exists('PlgSystemjsm_bootstrap', false)) {
    class_alias(SportsmanagementBootstrap::class, 'PlgSystemjsm_bootstrap');
}
