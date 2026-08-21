<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * Joomla 5/6 loads the plugin through services/provider.php and the namespaced
 * extension class. This file remains for existing installations and legacy loaders.
 */

defined('_JEXEC') or die;

use Diddipoeler\Plugin\Content\SportsmanagementComments\Extension\SportsmanagementComments;

if (!class_exists(SportsmanagementComments::class)) {
    require_once __DIR__ . '/src/Extension/SportsmanagementComments.php';
}

if (!class_exists('plgContentSportsmanagement_Comments', false)) {
    class_alias(SportsmanagementComments::class, 'plgContentSportsmanagement_Comments');
}
