<?php
/**
 * Joomla 5/6 compatibility entry for the SportsManagement Ajax controller.
 *
 * The native MVC factory falls back to controllers/ajax.php when the namespaced
 * controller has not been autoloaded yet. Keep the historic ajax.json.php bridge
 * as the single compatibility implementation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$legacyAjaxController = __DIR__ . '/ajax.json.php';

if (!is_file($legacyAjaxController) || !is_readable($legacyAjaxController)) {
    throw new \RuntimeException('SportsManagement Ajax compatibility controller could not be loaded.', 500);
}

require_once $legacyAjaxController;

// A stale namespace cache must never leave the legacy controller name unresolved.
if (!class_exists('sportsmanagementControllerAjax', false)) {
    throw new \RuntimeException('SportsManagement Ajax compatibility controller alias was not registered.', 500);
}
