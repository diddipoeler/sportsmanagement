<?php
/**
 * Reuse Joomla 5/6 native resultsmatrix fixtures in resultsranking.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

// This is a fixed path inside the installed site's adjacent native templates.
// No user-controlled view or template name is interpreted as a PHP path.
require dirname(__DIR__) . '/resultsmatrix/default_results.php';
