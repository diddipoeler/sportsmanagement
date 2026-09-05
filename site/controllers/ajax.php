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

require_once __DIR__ . '/ajax.json.php';
