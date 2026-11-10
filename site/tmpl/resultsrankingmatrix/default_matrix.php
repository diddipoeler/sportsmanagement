<?php
/**
 * Reuse the native Joomla 5/6 responsive match cross-table.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

// The HTML-escaping matrix view uses this view's shared project-owned teams,
// results and divisions. The constant include cannot escape the component.
require dirname(__DIR__) . '/matrix/default.php';
