<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Ajax controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

// Keep the JSON and regular legacy endpoints on the same native controller bridge.
// This also ensures both entry points receive identical dependency checks.
$legacyAjaxController = __DIR__ . '/ajax.json.php';

if (!is_file($legacyAjaxController) || !is_readable($legacyAjaxController)) {
    throw new \RuntimeException('SportsManagement administrator Ajax compatibility controller is missing or unreadable.', 500);
}

require_once $legacyAjaxController;

if (!class_exists('sportsmanagementControllerAjax', false)) {
    throw new \RuntimeException('SportsManagement administrator Ajax controller alias was not registered.', 500);
}
