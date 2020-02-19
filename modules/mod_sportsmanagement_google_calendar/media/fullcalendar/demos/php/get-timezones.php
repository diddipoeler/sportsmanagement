<?php
//--------------------------------------------------------------------------------------------------
// This script outputs a JSON array of all timezones (like "America/Chicago") that PHP supports.
//
// Requires PHP 5.2.0 or higher.
//--------------------------------------------------------------------------------------------------
/** 
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
echo json_encode(DateTimeZone::listIdentifiers());
