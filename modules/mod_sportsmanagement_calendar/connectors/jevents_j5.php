<?php
/**
 * Joomla 5/6 JEvents connector for the SportsManagement calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

final class JEventsConnector extends JSMCalendar
{
    public static Registry $xparams;
    private static $jevent;

    public static function getEntries(array $caldates, Registry $params, array &$matches): array
    {
        if (!self::checkJEvents()) {
            return [];
        }

        $start = substr((string) ($caldates['start'] ?? ''), 0, 10);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $start);
        if (!$date) {
            return [];
        }

        self::$xparams = $params;
        $data = self::$jevent->getCalendarData((int) $date->format('Y'), (int) $date->format('m'), 1);

        return self::formatEntries((array) ($data['dates'] ?? []), $matches);
    }

    private static function checkJEvents(): bool
    {
        $base = JPATH_SITE . '/components/com_jevents';
        $defines = $base . '/mod.defines.php';
        $model = $base . '/libraries/datamodel.php';

        if (!is_file($defines) || !is_file($model)) {
            self::raiseError('JEvents connector disabled: required JEvents files were not found.');
            return false;
        }

        require_once $defines;
        require_once $model;

        if (!class_exists('JEventsDataModel')) {
            self::raiseError('JEvents connector disabled: JEventsDataModel is unavailable.');
            return false;
        }

        self::$jevent = new \JEventsDataModel();
        if (!is_callable([self::$jevent, 'getCalendarData'])) {
            self::raiseError('JEvents connector disabled: getCalendarData() is unavailable.');
            return false;
        }

        return true;
    }

    private static function raiseError(string $message): void
    {
        Factory::getApplication()->enqueueMessage($message, 'warning');
    }

    private static function formatEntries(array $rows, array &$matches): array
    {
        $newRows = [];

        foreach ($rows as $row) {
            if (empty($row['events']) || !is_iterable($row['events'])) {
                continue;
            }

            foreach ($row['events'] as $event) {
                $formatted = [
                    'link' => self::buildLink($event, (int) ($row['year'] ?? 0), (int) ($row['month'] ?? 0)),
                    'date' => date('Y-m-d', (int) ($row['cellDate'] ?? 0)) . ' ' . date('H:i', (int) ($event->_dtstart ?? 0)),
                    'type' => 'jevents',
                    'time' => '',
                    'headingtitle' => (string) self::$xparams->get('jevents_text', 'JEvents'),
                    'name' => '',
                    'title' => (string) ($event->_title ?? ''),
                    'location' => (string) ($event->_location ?? ''),
                    'color' => (string) ($event->_color_bar ?? ''),
                    'matchcode' => 0,
                    'project_id' => 0,
                ];

                if ((int) ($event->_alldayevent ?? 0) !== 1) {
                    $start = (int) ($event->_dtstart ?? 0);
                    $end = (int) ($event->_dtend ?? 0);
                    $formatted['time'] = date('H:i', $start);
                    if ($start !== $end && (int) ($event->_noendtime ?? 0) === 0) {
                        $formatted['time'] .= '-' . date('H:i', $end);
                    }
                }

                $newRows[] = $formatted;
                $matches[] = $formatted;
            }
        }

        return $newRows;
    }

    private static function buildLink(object $event, int $year, int $month): string
    {
        $router = JPATH_SITE . '/components/com_jevents/router.php';
        if (is_file($router)) {
            require_once $router;
        }

        $link = 'index.php?option=com_jevents&task=icalrepeat.detail&evid=' . (int) ($event->_eventdetail_id ?? 0)
            . '&year=' . $year . '&month=' . $month . '&day=' . (int) ($event->_dup ?? 0)
            . '&uid=' . rawurlencode((string) ($event->_uid ?? ''));

        return Route::_($link);
    }
}
