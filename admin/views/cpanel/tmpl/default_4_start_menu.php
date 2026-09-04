<?php
/**
 * Joomla 5/6 cPanel bridge for the versioned SportsManagement start menu.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$startMenu = JPATH_ADMINISTRATOR
    . '/components/com_sportsmanagement/views/listheader/tmpl/default_4_start_menu.php';

if (!is_file($startMenu)) {
    throw new \RuntimeException('SportsManagement Joomla 5/6 cPanel start menu could not be loaded.', 500);
}

require $startMenu;
