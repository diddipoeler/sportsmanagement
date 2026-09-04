<?php
/**
 * Legacy template bridge for the native Joomla 5/6 SportsManagement cPanel.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$nativeTemplate = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tmpl/cpanel/native.php';

if (!is_file($nativeTemplate)) {
    throw new \RuntimeException('SportsManagement native Joomla 5/6 cPanel template could not be loaded.', 500);
}

require $nativeTemplate;
