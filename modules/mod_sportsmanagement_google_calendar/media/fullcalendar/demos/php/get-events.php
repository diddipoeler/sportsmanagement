<?php
/**
 * FullCalendar event feed retained for the Joomla 5/6 Google Calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;

require __DIR__ . '/utils.php';

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    throw new \RuntimeException('SportsManagement site application resolver could not be loaded.', 500);
}

$input = SportsManagementSiteApplicationResolver::resolve()->getInput();
$start = $input->getString('start');
$end = $input->getString('end');

if ($start === '' || $end === '') {
    throw new \InvalidArgumentException('Please provide a date range.');
}

$rangeStart = parseDateTime($start);
$rangeEnd = parseDateTime($end);
$timezone = null;
$timezoneName = $input->getString('timezone');

if ($timezoneName !== '') {
    $timezone = new \DateTimeZone($timezoneName);
}

$json = file_get_contents(__DIR__ . '/../json/events.json');
$inputArrays = json_decode((string) $json, true);

if (!is_array($inputArrays)) {
    $inputArrays = [];
}

$outputArrays = [];

foreach ($inputArrays as $array) {
    $event = new Event($array, $timezone);

    if ($event->isWithinDayRange($rangeStart, $rangeEnd)) {
        $outputArrays[] = $event->toArray();
    }
}

echo json_encode($outputArrays, JSON_UNESCAPED_SLASHES);
