<?php
/**
 * Legacy site controller bridge for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\DisplayController;

if (!class_exists(DisplayController::class)) {
    require_once __DIR__ . '/src/Controller/DisplayController.php';
}

if (!class_exists('sportsmanagementController', false)) {
    class_alias(DisplayController::class, 'sportsmanagementController');
}
