<?php
/**
 * SportsManagement Joomla 5/6 migration.
 *
 * @version    5.6.0 sportsmanagement
 * @author     diddipoeler <diddipoeler@gmx.de>
 * @copyright  Copyright (C) diddipoeler. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$startMenu = JPATH_ADMINISTRATOR
    . '/components/com_sportsmanagement/views/listheader/tmpl/default_5_start_menu.php';

if (!is_file($startMenu)) {
    throw new \RuntimeException('SportsManagement Joomla 5/6 cPanel start menu could not be loaded.', 500);
}

require $startMenu;
