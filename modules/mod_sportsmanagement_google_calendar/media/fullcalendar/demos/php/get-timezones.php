<?php
/**
 * FullCalendar timezone endpoint retained for Joomla 5/6 compatibility.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

echo json_encode(\DateTimeZone::listIdentifiers(), JSON_UNESCAPED_SLASHES);
