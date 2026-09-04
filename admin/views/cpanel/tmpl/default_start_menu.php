<?php
/**
 * Fallback bridge for the Joomla 5/6 SportsManagement cPanel start menu.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$startMenu = __DIR__ . '/default_4_start_menu.php';

if (!is_file($startMenu)) {
    throw new \RuntimeException('SportsManagement Joomla 5/6 cPanel start menu bridge could not be loaded.', 500);
}

require $startMenu;
