<?php
/**
 * Legacy administrator controller bridge for Joomla 5/6.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\DisplayController;

if (!class_exists(DisplayController::class)) {
    require_once __DIR__ . '/src/Controller/DisplayController.php';
}

if (!class_exists('SportsManagementController', false)) {
    class_alias(DisplayController::class, 'SportsManagementController');
}
