<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_google_calendar
 * @file       get-events.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

//--------------------------------------------------------------------------------------------------
// This script reads event data from a JSON file and outputs those events which are within the range
// supplied by the "start" and "end" GET parameters.
//
// An optional "timezone" GET parameter will force all ISO8601 date stings to a given timezone.
//
// Requires PHP 5.2.0 or higher.
//--------------------------------------------------------------------------------------------------

// Require our Event class and datetime utilities
require dirname(__FILE__) . '/utils.php';

$input = Factory::getApplication()->getInput();

// Short-circuit if the client did not give us a date range.
$start = $input->getString('start');
$end = $input->getString('end');

if ($start === '' || $end === '')
{
	die("Please provide a date range.");
}

// Parse the start/end parameters.
// These are assumed to be ISO8601 strings with no time nor timezone, like "2013-12-29".
// Since no timezone will be present, they will parsed as UTC.
$range_start = parseDateTime($start);
$range_end   = parseDateTime($end);

// Parse the timezone parameter if it is present.
$timezone = null;
$timezoneName = $input->getString('timezone');

if ($timezoneName !== '')
{
	$timezone = new DateTimeZone($timezoneName);
}

// Read and parse our events JSON file into an array of event data arrays.
$json         = file_get_contents(dirname(__FILE__) . '/../json/events.json');
$input_arrays = json_decode($json, true);

// Accumulate an output array of event data arrays.
$output_arrays = array();
foreach ($input_arrays as $array)
{

	// Convert the input array into a useful Event object
	$event = new Event($array, $timezone);

	// If the event is in-bounds, add it to the output
	if ($event->isWithinDayRange($range_start, $range_end))
	{
		$output_arrays[] = $event->toArray();
	}
}

// Send JSON to the client.
echo json_encode($output_arrays);
