<?php
/**
 * Legacy site controller bridge for Joomla 5/6.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\DisplayController;

if (!class_exists(DisplayController::class)) {
    require_once __DIR__ . '/src/Controller/DisplayController.php';
}

if (!class_exists('sportsmanagementController', false)) {
    class_alias(DisplayController::class, 'sportsmanagementController');
}
