<?php
/**
 * SportsManagement Joomla 5/6 legacy plugin bridge.
 *
 * The active implementation is loaded through services/provider.php.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Plugin\System\SportsmanagementSiscron\Extension\SportsmanagementSiscron;

if (!class_exists(SportsmanagementSiscron::class)) {
    require_once __DIR__ . '/src/Extension/SportsmanagementSiscron.php';
}

if (!class_exists('PlgSystemjsm_siscron', false)) {
    class_alias(SportsmanagementSiscron::class, 'PlgSystemjsm_siscron');
}
