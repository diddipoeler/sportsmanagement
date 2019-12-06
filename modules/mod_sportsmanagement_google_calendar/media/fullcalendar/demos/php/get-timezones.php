<?php

//--------------------------------------------------------------------------------------------------
// This script outputs a JSON array of all timezones (like "America/Chicago") that PHP supports.
//
// Requires PHP 5.2.0 or higher.
//--------------------------------------------------------------------------------------------------
defined('_JEXEC') or die('Restricted access');
echo json_encode(DateTimeZone::listIdentifiers());
