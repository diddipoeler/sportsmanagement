<?php
/** Legacy compatibility bridge for the Joomla 5/6 SportsManagement user profile plugin. */
\defined('_JEXEC') or die;

use Diddipoeler\Plugin\User\SportsmanagementProfile\Extension\SportsmanagementProfile;

if (!class_exists(SportsmanagementProfile::class)) {
    require_once __DIR__ . '/src/Extension/SportsmanagementProfile.php';
}

if (!class_exists('plgUserjsmprofile', false)) {
    class_alias(SportsmanagementProfile::class, 'plgUserjsmprofile');
}
